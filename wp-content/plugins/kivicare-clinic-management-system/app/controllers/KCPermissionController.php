<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCPermissionController extends KCBase
{
    public $db;

    /**
     * @var KCRequest
     */
    private $request;

    public function __construct() {

        global $wpdb;

        $this->db = $wpdb;

        $this->request = new KCRequest();

        parent::__construct();
    }

    public function allPermissionList(){
        if ($this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }
        $request_data = $this->request->getInputs();
        if(isKiviCareProActive()){
            $response = apply_filters('kcpro_get_all_permission',$request_data);
            if(is_array($response) && array_key_exists('data',$response)){
                echo json_encode($response);
            }else{
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html__('Please use latest Pro plugin', 'kc-lang'),
                    'data'    => []
                ] );
            }
        }else{
            echo json_encode( [
                'status'  => false,
                'message' => esc_html__('Pro plugin not active', 'kc-lang'),
                'data'    => []
            ] );
        }
        wp_die();

    }

    public function savePermissionList(){
        if ( $this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }
        $request_data = $this->request->getInputs();
        if(isKiviCareProActive()){
            $response = apply_filters('kcpro_save_permission_list',$request_data);
            if(is_array($response) && array_key_exists('status',$response)){
                echo json_encode($response);
            }else{
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html__('Please use latest Pro plugin', 'kc-lang'),
                    'data'    => []
                ] );
            }
        }else{
            echo json_encode( [
                'status'  => false,
                'message' => esc_html__('Pro plugin not active', 'kc-lang'),
                'data'    => []
            ] );
        }
        wp_die();
    }
}