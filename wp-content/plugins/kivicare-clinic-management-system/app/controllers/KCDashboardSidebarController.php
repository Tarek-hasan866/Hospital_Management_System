<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCDashboardSidebarController extends KCBase{

    public $db;

    private $request;

    public function __construct(){

        if ( $this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }

        global $wpdb;

        $this->db = $wpdb;

        $this->request = new KCRequest();

        parent::__construct();
    }

    public function index(){
        echo json_encode([
            'status' => true,
            'data' => [
                'administrator' => kcDashboardSidebarArray(['administrator']),
                'clinic_admin' => kcDashboardSidebarArray([KIVI_CARE_PREFIX.'clinic_admin']),
                'receptionist' => kcDashboardSidebarArray([KIVI_CARE_PREFIX.'receptionist']),
                'doctor' => kcDashboardSidebarArray([KIVI_CARE_PREFIX.'doctor']),
                'patient' => kcDashboardSidebarArray([KIVI_CARE_PREFIX.'patient'])
            ]
        ]);
        die;
    }

    public function save(){
        $request_data = $this->request->getInputs();
        $rules = [
            'type' => 'required',
            'data' => 'required',
        ];

        $errors = kcValidateRequest( $rules, $request_data );

        if ( count( $errors ) ) {
            echo json_encode( [
                'status'  => false,
                'message' => $errors[0]
            ] );
            die;
        }
        if(in_array($request_data['type'],
            ['administrator','clinic_admin','receptionist','doctor','patient'])){
            update_option(KIVI_CARE_PREFIX.$request_data['type'].'_dashboard_sidebar_data',$request_data['data']);
            echo json_encode( [
                'status'  => true,
                'message' => esc_html__('Dashboard sidebar data saved successfully','kc-lang')
            ] );
        }else{
            echo json_encode( [
                'status'  => false,
                'message' => esc_html__('Data is not valid','kc-lang')
            ] );
        }
        die;

    }
}