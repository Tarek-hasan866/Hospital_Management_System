<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCPatientUniqueIdController extends KCBase
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

    public function savePatientSetting(){
        $setting = $this->request->getInputs();
        try{
            if(isset($setting)){
                $config = array(
                    'prefix_value' =>$setting['prefix_value'],
                    'postfix_value'=>$setting['postfix_value'],
                    'enable'=>$setting['enable'],
                    'only_number' => $setting['only_number']
                );
                update_option( KIVI_CARE_PREFIX . 'patient_id_setting',$config );
                echo json_encode( [
                    'status' => true,
                    'message' => esc_html__('Unique id setting saved successfully', 'kcp-lang')
                ] );
            }
        }catch (Exception $e) {
            echo json_encode( [
                'status' => false,
                'message' => esc_html__('Failed to save Unique id settings.', 'kcp-lang')
            ] );
        }

    }
    public function editPatientSetting(){
        $get_patient_data = get_option(KIVI_CARE_PREFIX . 'patient_id_setting',true);

        if ( gettype($get_patient_data) != 'boolean' ) {

            $get_patient_data['enable'] = in_array( (string)$get_patient_data['enable'], ['true', '1']) ? true : false;
            $get_patient_data['only_number'] = in_array( (string)$get_patient_data['only_number'], ['true','1']) ? true : false;
            
            echo json_encode( [
                'data'=> $get_patient_data,
                'status' => true,
            ] );
        } else {
            echo json_encode( [
                'data'=> [],
                'status' => false,
            ] );
        }
    }
    public function getPatientUid() {
        echo json_encode( [
            'data'=> generatePatientUniqueIdRegister(),
            'status' => true,
        ] );
    }
}