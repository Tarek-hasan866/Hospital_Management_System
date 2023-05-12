<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCGooglemeetController extends KCBase
{

    /**
     * @var KCRequest
     */
    public $db;

    private $request;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;

        $this->request = new KCRequest();

        parent::__construct();
    }


    public function saveGoogleMeetConfig(){
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
        $response = apply_filters('kcgm_saved_googlemeet_config', ['data'=>$request_data]);
        echo json_encode($response);
    }

    public function getGoogleMeetEventTemplateAndConfigData () {
        if ( $this->getLoginUserRole() !== 'administrator') {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => $this->permission_message,
                'data'        => []
            ] );
            wp_die();
        }
        $prefix = KIVI_CARE_PREFIX;
        $google_event_template = $prefix.'gmeet_tmp' ;
        $args['post_type'] = strtolower($google_event_template);
        $gogle_template_result = get_posts($args);
        $gogle_template_result = collect($gogle_template_result)->unique('post_title')->sortBy('ID');
        $config = apply_filters('kcgm_edit_googlemeet', []);
        if ($gogle_template_result) {
            $response =  [
                'status' => true,
                'data'=> $gogle_template_result,
                'config' => $config
            ];
        } else {
            $response = [
                'status' => false,
                'data'=> [],
                'config' => $config
            ];
        }
        echo json_encode($response);
    }
    public function saveGoogleMeetEventTemplate(){
        if ( $this->getLoginUserRole() !== 'administrator') {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => $this->permission_message,
                'data'        => []
            ] );
            wp_die();
        }
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcgm_save_googlemeet_event_template', [
            'data'=>$request_data['data']
        ]);
        echo json_encode($response);
    }

    public function connectGoogleMeetDoctor(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcgm_connect_doctor', [
            'id'=>$request_data['doctor_id'],
            'code'=>$request_data['code'],
        ]);
        echo json_encode($response);
    }

    public function disconnectMeetDoctor(){

        $request_data = $this->request->getInputs();
        $response = apply_filters('kcgm_disconnect_doctor', [
            'id'=>$request_data['doctor_id']
        ]);

        echo json_encode($response);

    }

    public function saveDoctorGooglemeetDataSave(){
        $request_data = $this->request->getInputs();
        $rules = [
            'doctor_id' => 'required',
            'telemed_service_id' => 'required',
            'video_price'              => 'required',
        ];

        $message = [
            'telemed_service_id'     => esc_html__('Telemed service id  is required', 'kc-lang'),
            'doctor_id' => esc_html__('Doctor is required','kc-lang'),
            'video_price'  => esc_html__('Price is required','kc-lang'),
        ];

        $errors = kcValidateRequest( $rules, $request_data, $message );

        if ( count( $errors ) ) {
            echo json_encode( [
                'status'  => false,
                'message' => $errors[0]
            ] );
            die;
        }

        $response = apply_filters('kcgm_save_doctor_googlemeet_data', [
            'data'=>$request_data
        ]);
        if(!empty($response) && !empty($response['status']) ){
            echo json_encode($response);
        }else{
            echo json_encode(['status' => false,"message" => "Failed to Save"]);
        }
    }
}