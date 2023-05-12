<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;

class KCProSettingController extends KCBase
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
    public function editAllProSettingData()
    {

        if (!current_user_can('administrator')) {
            echo json_encode([
                'status'      => false,
                'status_code' => 403,
                'message'     => $this->permission_message,
                'data'        => []
            ]);
            wp_die();
        }
        $wordpressLogoResponse = apply_filters('kcpro_get_wordpress_logo', []);
        if (!empty($wordpressLogoResponse) && is_array($wordpressLogoResponse)) {
            $wordpressLogoData =  $wordpressLogoResponse['data'];
        } else {
            $wordpressLogoData = [
                'status' =>  0,
                'logo' => KIVI_CARE_DIR_URI . 'assets/images/wp-logo.png'
            ];
        }

        echo json_encode([
            'sms' => apply_filters('kcpro_edit_sms_config', [
                'current_user' => get_current_user_id(),
            ]),
            'whatsapp' => apply_filters('kcpro_edit_whatsapp_config', [
                'current_user' => get_current_user_id(),
            ]),
            'google_calendar' => apply_filters('kcpro_edit_google_cal', []),
            'patient_calendar' => apply_filters('kcpro_patient_edit_google_cal', []),
            'encounter_clinical_detail' => apply_filters('kcpro_get_clinical_detail_in_prescription', []),
            'wordpress_logo_data' => $wordpressLogoData
        ]);
    }

    public function uploadLogo()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_upload_logo', [
            'site_logo' => $request_data['site_logo'],
        ]);
        echo json_encode($response);
    }

    public function uploadLoader()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_upload_loader', [
            'site_loader' => $request_data['site_loader'],
        ]);
        echo json_encode($response);
    }

    public function updateThemeColor()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_change_themecolor', [
            'color' => $request_data['color'],
        ]);
        echo json_encode($response);
    }

    public function updateRTLMode()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_change_mode', [
            'mode' => $request_data['rtl'],
        ]);
        echo json_encode($response);
    }

    public function wordpresLogo()
    {
        $requestData = $this->request->getInputs();
        $response = apply_filters('kcpro_save_wordpress_logo', $requestData);
        if (!empty($response) && is_array($response)) {
            echo json_encode($response);
        } else {
            echo json_encode([
                'status' => false,
                'message' => esc_html__('Filter Not Found', 'kc-lang')
            ]);
        }
        die;
    }

    public function saveSmsConfig()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_save_sms_config', [
            'config_data' => $request_data,
        ]);
        echo json_encode($response);
    }

    public function saveWhatsAppConfig()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_save_whatsapp_config', [
            'config_data' => $request_data,
        ]);
        echo json_encode($response);
    }

    public function saveConfig()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_saved_google_config', ['data' => $request_data]);
        echo json_encode($response);
    }

    public function googleCalPatient()
    {
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_patient_google_cal', [
            'data' => $request_data
        ]);
        echo json_encode($response);
    }

    public function editClinicalDetailInclude()
    {

        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_edit_clinical_detail_in_prescription', [
            'status' => $request_data['status'],
        ]);
        echo json_encode($response);
    }

    public function editEncounterCustomFieldInclude()
    {

        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_edit_encounter_custom_field_in_prescription', [
            'status' => $request_data['status'],
        ]);
        echo json_encode($response);
    }

    public function editClinicalDetailHideInPatient()
    {
        $request_data = $this->request->getInputs();
        apply_filters('kcpro_save_pro_option_value', $request_data, 'hide_clinical_detail_in_patient');

        echo json_encode([
            'status' => true,
            'message' => esc_html__('Setting Saved', 'kc-lang')
        ]);
    }

    public function saveCopyRightText()
    {
        $request_data = $this->request->getInputs();
        apply_filters('kcpro_save_pro_option_value', $request_data, 'copyrightText');
        echo json_encode(['status' => true, 'message' => __('CopyRight Text Saved Successfully', 'kc-lang')]);
        die;
    }

    public function getEncounterTemplates()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_get_encounter_templates',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function insertTemplateToEncounter()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_insert_template_to_encounter',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function deleteEncounterTemp()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_delete_encounter_temp',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function addEncounterTemp()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_add_encounter_temp',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function medicalHistoryListFromTemplate()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_medical_history_list_from_template',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function saveEncounterTemplateMedicalHistory()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_save_encounter_template_medical_history',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function deleteEncounterTemplateMedicalHistory()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_delete_encounter_template_medical_history',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function patientEncounterTemplateDetails()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_patient_encounter_template_details',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function getEncounterTemplatePrescriptionList()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_get_encounter_template_prescription_list',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function saveEncounterTemplatePrescription()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_save_encounter_template_prescription',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
    public function deleteEncounterTemplatePrescription()
    {
        $request_data = $this->request->getInputs();
        do_action('kcpro_delete_encounter_template_prescription',$request_data);
        wp_send_json_error(esc_html__('This API Is Only For KiviCare Pro', 'kc-lang'), 403);
    }
}
