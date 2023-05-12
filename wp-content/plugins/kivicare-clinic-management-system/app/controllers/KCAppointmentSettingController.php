<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCAppointmentSettingController extends KCBase
{
    public $db;

    private $request;

    public function __construct() {

        global $wpdb;

        $this->db = $wpdb;

        $this->request = new KCRequest();

        parent::__construct();
    }

    public function restrictAppointmentSave(){
        $request_data = $this->request->getInputs();
        $message = esc_html__('Failed to update', 'kc-lang');
        $status =false;
        if(isset($request_data['pre_book']) && isset($request_data['post_book'])){
            if((int)$request_data['pre_book'] < 0 && (int)$request_data['post_book'] < 0 ){
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html__('Pre or Post Book Days Must Be Greater than Zero ', 'kc-lang'),
                ] );
                die;
            }
            update_option(KIVI_CARE_PREFIX .'restrict_appointment',['post' => (int)$request_data['post_book'] ,'pre' =>(int)$request_data['pre_book']]);
            $status = true;
            $message = esc_html__('Appointment restrict days saved successfully', 'kc-lang');
        }
        echo json_encode( [
            'status'  => $status,
            'message' => $message,
        ] );
        die;
    }

    public function restrictAppointmentEdit(){
        echo json_encode( [
            'status'  =>  true,
            'data' => kcAppointmentRestrictionData(),
        ] );
        die;
    }

    public function getMultifileUploadStatus(){
        $data = get_option(KIVI_CARE_PREFIX . 'multifile_appointment',true);

        if(gettype($data) != 'boolean'){
            $temp = $data;
        }else{
            $temp = 'off';
        }

        echo json_encode( [
            'status'  => true,
            'data' => $temp,
        ] );
    }

    public function saveMultifileUploadStatus(){
        $request_data = $this->request->getInputs();
        $message = esc_html__('Failed to update', 'kc-lang');
        $status =false;
        if(isset($request_data['status']) && !empty($request_data['status']) ){
            $table_name = $this->db->prefix . 'kc_appointments';
            kcUpdateFields($table_name,[ 'appointment_report' => 'longtext NULL']);
            update_option(KIVI_CARE_PREFIX . 'multifile_appointment',$request_data['status']);
            $message = esc_html__('File Upload Setting Saved.', 'kc-lang');
            $status = true;
        }
        echo json_encode( [
            'status'  => $status ,
            'message' => $message,
        ] );
        die;
    }

    public function appointmentReminderNotificatioSave(){
        $request_data = $this->request->getInputs();
        $message = esc_html__('Failed to update', 'kc-lang');
        $status =false;
        if(isset($request_data['status']) && !empty($request_data['status']) && isset($request_data['time'])){
            update_option(KIVI_CARE_PREFIX . 'email_appointment_reminder',[
                    "status" =>$request_data['status'],
                    "time" =>$request_data['time'],
                    "sms_status"=>isset($request_data['sms_status']) ? $request_data['sms_status'] : 'off' ,
                    "whatapp_status" => isset($request_data['whatapp_status']) ? $request_data['whatapp_status']: 'off' ]
            );
            $message = esc_html__('Email Appointment Reminder Setting Saved', 'kc-lang');
            $status = true;
            if($request_data['status'] == 'off' && isset($request_data['sms_status']) && $request_data['sms_status'] == 'off' && isset($request_data['whatapp_status']) && $request_data['whatapp_status'] == 'off' ){
                wp_clear_scheduled_hook("kivicare_patient_appointment_reminder");
            }
        }
        echo json_encode( [
            'status'  => $status,
            'message' => $message,
        ] );
        die;
    }

    public function getAppointmentReminderNotification(){
        $data = get_option(KIVI_CARE_PREFIX . 'email_appointment_reminder',true);

        //create table
        require KIVI_CARE_DIR . 'app/database/kc-appointment-reminder-db.php';

        if(gettype($data) != 'boolean'){
            $temp = $data;
            if( !isKiviCareProActive()){
                if(is_array($temp ) ){
                    $temp['sms_status'] = 'off';
                    $temp['whatapp_status'] = 'off';
                }
            }
        }else{
            $temp = ["status" => 'off',"sms_status"=> 'off',"time" => '24',"whatapp_status" => 'off'];
        }

        echo json_encode( [
            'status'  => true,
            'data' => $temp,
        ] );
    }

    public function appointmentTimeFormatSave(){
        $request_data = $this->request->getInputs();
        $message = esc_html__('Failed to update', 'kc-lang');
        $status =false;
        if(isset($request_data['timeFormat']) ){
            update_option(KIVI_CARE_PREFIX . 'appointment_time_format',$request_data['timeFormat']);
            $message = esc_html__('Appointment Time Format Saved', 'kc-lang');
            $status = true;
        }
        echo json_encode( [
            'status'  => $status ,
            'message' => $message,
        ] );
        die;
    }

    public function enableDisableAppointmentDescription () {
        $request_data = $this->request->getInputs();
        $update_status = update_option(KIVI_CARE_PREFIX.'appointment_description_config_data', $request_data['status']);
        echo json_encode([
            'data' => $request_data['status'],
            'status'  => true,
            'message' => esc_html__('Appointment Description status changed successfully.', 'kc-lang'),
        ]);
    }

    public function getAppointmentDescription () {
        $get_status = get_option(KIVI_CARE_PREFIX.'appointment_description_config_data');
        $enableAppointmentDescription = gettype($get_status) == 'boolean' ? 'on' : $get_status;

        echo json_encode([
            'data' => $enableAppointmentDescription,
            'status'  => true
        ]);
    }

    public function enableDisableAppointmentPatientInfo () {
        $request_data = $this->request->getInputs();
        $update_status = update_option(KIVI_CARE_PREFIX.'appointment_patient_info_config_data', $request_data['status']);
        echo json_encode([
            'data' => $request_data['status'],
            'status'  => true,
            'message' => esc_html__('Appointment Patient Info visibility status changed successfully.', 'kc-lang'),
        ]);
    }

    public function getAppointmentPatientInfo () {
        $get_status = get_option(KIVI_CARE_PREFIX.'appointment_patient_info_config_data');
        $enablePatientInfo = gettype($get_status) == 'boolean' ? 'on' : $get_status;

        echo json_encode([
            'data' => $enablePatientInfo,
            'status'  => true
        ]);
    }
}