<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCDoctorRatingController extends KCBase{
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

    public function getReview(){

        $request_data = $this->request->getInputs();
        if(isKiviCareProActive()){
            $response = apply_filters('kcpro_get_doctor_review',$request_data);
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

    public function saveReview(){
        $request_data = $this->request->getInputs();
        if(isKiviCareProActive()){
            $rules = [
                'patient_id' => 'required',
                'doctor_id' => 'required'
            ];

            $errors = kcValidateRequest($rules, $request_data);

            if (!empty(count($errors))) {
                echo json_encode([
                    'status' => false,
                    'message' => $errors[0]
                ]);
                die;
            }
            $doctor_patient_list = kcDoctorPatientList($request_data['doctor_id']);
            $doctor_patient_list = !empty($doctor_patient_list) ? $doctor_patient_list : [-1];
            if(!in_array($request_data['patient_id'],$doctor_patient_list)){
                echo json_encode([
                    'status' => false,
                    'status_code' => 403,
                    'message' => $this->permission_message,
                    'data' => []
                ]);
                wp_die();
            }
            $response = apply_filters('kcpro_save_doctor_review',$request_data);
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
        die;
    }
    public function doctorReviewDetail(){
        $request_data = $this->request->getInputs();
        if(isKiviCareProActive()){
            $response = apply_filters('kcpro_get_doctor_review_detail',$request_data);
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
        die;
    }
}