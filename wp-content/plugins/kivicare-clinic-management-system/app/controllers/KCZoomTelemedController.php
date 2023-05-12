<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCService;
use App\models\KCServiceDoctorMapping;

class KCZoomTelemedController extends KCBase
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

    public function saveZoomConfiguration() {

        $request_data = $this->request->getInputs();

        $request_data['enableTeleMed'] = in_array((string)$request_data['enableTeleMed'],['true','1']) ? 'true' : 'false';
        $telemed_service_status = $request_data['enableTeleMed'] === 'true' ? 1 : 0;

        $service_doctor_mapping = new KCServiceDoctorMapping ;

        $rules = [
            'api_key' => 'required',
            'api_secret' => 'required',
            'doctor_id' => 'required',
        ];

        $errors = kcValidateRequest($rules, $request_data);

        if (count($errors)) {
            echo json_encode([
                'status' => false,
                'message' => $errors[0]
            ]);
            die;
        }


        $request_data['doctor_id'] = (int)$request_data['doctor_id'];

        //save zoom configuration
        $response = apply_filters('kct_save_zoom_configuration', [
            'user_id' => $request_data['doctor_id'],
            'enableTeleMed' => $request_data['enableTeleMed'],
            'api_key' => $request_data['api_key'],
            'api_secret' => $request_data['api_secret']
        ]);

        //if zoom configuration save successfull
        if(!empty($response['status'])) {

            //get telemed service id
//            $telemed_Service =  kcGetTelemedServiceId();
            //get doctor telemed service id
//            $doctor_telemed_service  =  $service_doctor_mapping->get_by(['service_id'=> $telemed_Service, 'doctor_id'  => $request_data['doctor_id']]);

            //if doctor telemed not exists add telemed service
//            if(count($doctor_telemed_service) == 0) {
//                $service_doctor_mapping->insert([
//                    'service_id' => $telemed_Service,
//                    'clinic_id'  => kcGetDefaultClinicId(),
//                    'doctor_id'  => $request_data['doctor_id'],
//                    'charges'    => $request_data['video_price']
//                ]);
//            }else{
//                $service_doctor_mapping->update(['charges' => $request_data['video_price'], 'status' => $telemed_service_status],['id' => $doctor_telemed_service[0]->id]);
//            }

            if($request_data['enableTeleMed'] === 'true'){
                //change googlemeet value if zoom telemed enable
                update_user_meta($request_data['doctor_id'], KIVI_CARE_PREFIX.'google_meet_connect', 'off' );
            }

            echo json_encode([
                'status' => true,
                'message' => esc_html__("Telemed key successfully saved.", 'kc-lang'),
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => esc_html__("Failed to save Telemed key, please check API key and API Secret", 'kc-lang'),
            ]);
        }
        die;

    }

    public function getZoomConfiguration() {
        $request_data = $this->request->getInputs();

        $request_data['user_id'] = (int)$request_data['user_id'];
        $response = apply_filters('kct_get_zoom_configuration', [
            'user_id' => $request_data['user_id'],
        ]);

        //get telemed service id
//        $telemed_Service =  kcGetTelemedServiceId();
//        if(!empty($response['data']) && !empty($telemed_Service)){
//            $price = $this->db->get_var("SELECT charges FROM {$this->db->prefix}kc_service_doctor_mapping WHERE service_id={$telemed_Service} AND doctor_id={$request_data['user_id']}");
//            $response['data']->video_price = !empty($price) ? $price : '';
//        }
        //get doctor based zoom configuration data
        echo json_encode([
            'status' => true,
            'message' => esc_html__("Configuration data", 'kc-lang'),
            'data' => !empty($response['data']) ? $response['data'] : []
        ]);
        die;

    }

    public function resendZoomLink(){

        //resend video appointment meeting link
        $request_data = $this->request->getInputs();
        //googlemeet send google meet link
        if(isKiviCareGoogleMeetActive() && kcCheckDoctorTelemedType($request_data['id']) == 'googlemeet'){
            $res_data = apply_filters('kcgm_save_appointment_event_link_resend', $request_data);
        }else{
            //send zoom meet link
            $res_data = apply_filters('kct_send_resend_zoomlink', $request_data);
        }
        echo json_encode( [
            'status'  => true,
            'message' => esc_html__('Video Conference Link Send', 'kc-lang'),
            'data'    => $res_data,
        ] );
    }
}