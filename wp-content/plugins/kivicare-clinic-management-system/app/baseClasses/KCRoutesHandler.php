<?php

namespace App\baseClasses;

use Exception;

class KCRoutesHandler extends KCBase {

	public  $routes;

	public  $controllerPath;

	public function __construct($routes, $controllerPath) {
	    $this->routes = $routes;
	    $this->controllerPath = $controllerPath;
    }

    public function init() {

		// Action to handle routes...
		add_action( "wp_ajax_ajax_post", [ $this, 'ajaxPost' ] );
		add_action( "wp_ajax_nopriv_ajax_post", [ $this, 'ajaxPost' ] );
		add_action( "wp_ajax_ajax_get", [ $this, 'ajaxGet' ] );
		add_action( "wp_ajax_nopriv_ajax_get", [ $this, 'ajaxGet' ] );
	}

	public function ajaxPost() {

		$request = new KCRequest();

		$requestData = $request->getInputs();

		try {

            //check if request method is post method
            if ( strtolower( sanitize_textarea_field(wp_unslash($_SERVER['REQUEST_METHOD']) )) !== 'post' ) {
                $error = __('Method is not allowed','kc-lang');
                throw new Exception( $error, 405 );
            }

            //check if request route key exists in route array
			if ( array_key_exists($requestData['route_name'],$this->routes) ) {

                //get request route value from route array
                $route = $this->routes[ $requestData['route_name'] ];

                //check if request route method is same as required method
				if ( strtolower( $route['method'] ) !== 'post' ) {
					$error = __('Method is not allowed','kc-lang');
					throw new Exception( $error, 405 );
				}

                //check route value have nonce if not set nonce to 100
				if (!isset($route['nonce'])) {
					$route['nonce'] = 1;
				}

				if ($route['nonce'] === 1) {
                    //verify request nonce
					if ( ! wp_verify_nonce( $requestData['_ajax_nonce'], 'ajax_post' ) && !is_user_logged_in() ) {
						$error = __('Invalid nonce in request','kc-lang');
						throw new Exception( $error, 419 );
					}
				}

                //call function
				$this->call( $route );
			}else{
                $error = __('Route not found','kc-lang');
                throw new Exception( $error, 404 );
            }

		} catch ( Exception $e ) {

			$code    = $e->getCode();
			$message = $e->getMessage();

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => $e->getMessage()
			] );

		}
		wp_die();
	}

	public function ajaxGet() {

		$request = new KCRequest();

		$requestData = $request->getInputs();

		if ( $requestData === '' ) {
			$requestData = json_decode( file_get_contents( "php://input" ), true );
		}

        try {
            //check if request method is get method
            if ( strtolower( sanitize_textarea_field(wp_unslash($_SERVER['REQUEST_METHOD'])) ) !== 'get' ) {
                $error = 'Method is not allowed';
                throw new Exception( $error, 405 );
            }


            //check if request route exists in route array
            if (array_key_exists($requestData['route_name'],$this->routes)) {

                //get request route value from route array
                $route = $this->routes[ $requestData['route_name'] ];

                //check if request route method is same as required method
				if ( strtolower( $route['method'] ) !== 'get' ) {
					$error = __('Method is not allowed','kc-lang');
					throw new Exception( $error, 405 );
				}

                //call function
				$this->call( $route );

			}else{
                $error = __('Route not found','kc-lang');
                throw new Exception( $error, 404 );
            }

		} catch ( Exception $e ) {

			$code    = $e->getCode();
			$message = $e->getMessage();

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => $e->getMessage()
			] );
		}
		
	}

	public function call( $route ) {

		$cluster = explode( '@', $route['action'] );

		$controller = $this->controllerPath . $cluster[0];
		$function   = $cluster[1];

		( new $controller )->$function();

		die;
	}

}