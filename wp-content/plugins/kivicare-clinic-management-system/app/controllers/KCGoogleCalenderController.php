<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use WP_User;

class KCGoogleCalenderController extends KCBase
{
    /**
     * @var KCRequest
     */
    private $request;

    public function __construct()
    {
        $this->request = new KCRequest();
        parent::__construct();
    }
    

    public function connectDoctor(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_connect_doctor', [
            'id'=>(int)$request_data['doctor_id'],
            'code'=>$request_data['code'],
        ]);
        echo json_encode($response);
    }
    public function disconnectDoctor(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_disconnect_doctor', [
            'id'=>(int)$request_data['doctor_id']
        ]);
        echo json_encode($response);
    }
    public function getGoogleEventTemplate () {
        if($this->getLoginUserRole() !== 'administrator'){
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $this->permission_message,
                'data' => []
            ]);
            wp_die();
        }
        $prefix = KIVI_CARE_PREFIX;
        $google_event_template = $prefix.'gcal_tmp' ;
        $args['post_type'] = strtolower($google_event_template);
        $gogle_template_result = get_posts($args);
        $gogle_template_result = collect($gogle_template_result)->unique('post_title')->sortBy('ID');
        if ($gogle_template_result) {
            $response = [
                'status' => true,
                'data'=> $gogle_template_result,
            ];
        } else {
            $response = [
                'status' => false,
                'data'=> [],
            ];
        }
        echo json_encode($response);
    }
    public function saveGoogleEventTemplate(){
        if($this->getLoginUserRole() !== 'administrator'){
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $this->permission_message,
                'data' => []
            ]);
            wp_die();
        }
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_save_google_event_template', [
            'data'=>$request_data['data']
        ]);
        echo json_encode($response);

    }
}