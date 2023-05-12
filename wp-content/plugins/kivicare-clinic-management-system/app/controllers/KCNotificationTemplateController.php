<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCNotificationTemplateController extends KCBase
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

    public function getEmailTemplate () {
        echo json_encode([
            'status' => true,
            'data' => kcGetNotificationTemplateLists('mail'),
            'labels' => [
                'patient' => __("Patient Templates","kc-lang"),
                'doctor' => __("Doctor Templates","kc-lang"),
                'clinic' => __("Clinic Templates","kc-lang"),
                'receptionist' => __("Receptionist Templates","kc-lang"),
                'common' => __("Common Templates","kc-lang"),
            ],
            'dynamicKey' => kcGetEmailSmsDynamicKeys()
        ]);
    }
    public function saveEmailTemplate () {

        $request_data = $this->request->getInputs();
        $request_data['data'] = collect($request_data['data'])->flatten(1);
        foreach ($request_data['data'] as $key => $value) {
            wp_update_post($value);
        }

        echo json_encode([
            'status' => true,
            'message' => esc_html__('Email template saved successfully.', 'kc-lang')
        ]);

    }
    public function getSMSTemplate () {
        echo json_encode([
            'status' => true,
            'data'=> kcGetNotificationTemplateLists('sms'),
            'labels' => [
                'patient' => __("Patient Templates","kc-lang"),
                'doctor' => __("Doctor Templates","kc-lang"),
                'clinic' => __("Clinic Templates","kc-lang"),
                'receptionist' => __("Receptionist Templates","kc-lang"),
                'common' => __("Common Templates","kc-lang"),
            ],
            'dynamicKey' => kcGetEmailSmsDynamicKeys()
        ]);
    }
    public function saveSMSTemplate () {

        $request_data = $this->request->getInputs();
        $request_data['data'] = $request_data['data'] = collect($request_data['data'])->flatten(1);
        $response = apply_filters('kcpro_save_sms_template', [
            'data'=>$request_data['data']
        ]);
    }
    public function sendTestNotification () {
        $data = $this->request->getInputs();
        $response = (object)[];
        $message = esc_html__('Failed to send test ', 'kc-lang') .$data['type'];
        $status = false;
        switch($data['type']){
            case 'email':
                $email_status = wp_mail($data['recieverDetails'], 'Kivicare test mail', $data['content']);
                $message = esc_html__('Failed to send test email. Please check your SMTP setup.', 'kc-lang');
                if($email_status) {
                    $status = true ;
                    $message = esc_html__('Test email sent successfully.', 'kc-lang');
                    update_option( KIVI_CARE_PREFIX . 'is_email_working' , $email_status);
                }
                break;
            case 'sms':
                $response = apply_filters('kcpro_send_sms_directly',$data['recieverDetails'],$data['content']);
                $message = esc_html__('Failed to send test sms. Please check your Twillo sms setup.', 'kc-lang');
                if(!empty($response->status)){
                    if(in_array($response->status,['sent','queued','delivered'])){
                        $status = true ;
                        $message = esc_html__('Test Sms sent successfully.', 'kc-lang');
                    }else if(!empty($response->error)){
                        $message =  $response->error;
                    }
                }
                break;
            case 'whatsapp':
                $response = apply_filters('kcpro_send_whatsapp_directly',$data['recieverDetails'],$data['content']);
                $message = esc_html__('Failed to send test sms. Please check your Twillo whatsapp setup.', 'kc-lang');
                if(!empty($response->status)){
                    if(in_array($response->status,['sent','queued','delivered'])){
                        $status = true ;
                        $message =  esc_html__('Test whatsapp sent successfully.', 'kc-lang');
                    }else if(!empty($response->error)){
                        $message =  $response->error;
                    }
                }
                break;
        }

        echo json_encode([
            'status' => $status,
            'message' => $message,
            'response' => $response
        ]); die;
    }
}