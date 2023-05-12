<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCAppointment;
use App\models\KCBill;
use App\models\KCDoctorClinicMapping;
use App\models\KCPatientEncounter;
use App\models\KCServiceDoctorMapping;
use App\models\KCReceptionistClinicMapping;
use App\models\KCPatientClinicMapping;
use App\models\KCService;
use App\models\KCCustomField;
use App\models\KCClinic;
use Exception;
use stdClass;
use WP_User;
use DateTime;

class KCHomeController extends KCBase
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

    public function logout()
    {
        wp_logout();
        echo json_encode([
            'status' => true,
            'message' => esc_html__('Logout successful.', 'kc-lang'),
        ]);
    }

    public function getUser() {

        if (!function_exists('get_plugins')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

        $currentLoginUserRole = $this->getLoginUserRole();
        $proPluginActive = isKiviCareProActive();
        $telemedZoomPluginActive = isKiviCareTelemedActive();
        $telemedGooglemeetActive = isKiviCareGoogleMeetActive();
        $razorpayPluginActive = isKiviCareRazorpayActive();
        $user = new \stdClass();
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $userObj = new WP_User($user_id);
            $get_user_language = get_user_meta($user_id, 'defualt_lang');
            $user = $userObj->data;
            unset($user->user_pass);
            $get_admin_language = get_option(KIVI_CARE_PREFIX . 'admin_lang');
            if(current_user_can('administrator')){
                $user->get_lang = isset($get_admin_language) ? $get_admin_language :'en';
            }else{
                $user->get_lang = isset($get_user_language[0]) ? $get_user_language[0] :$get_admin_language;
            }
            
            $user->permissions = $userObj->allcaps;
            $user->roles = array_values($userObj->roles);

            $image_attachment_id = '';
            $kivicare_user = false;
            switch ($this->getLoginUserRole()) {
                case $this->getReceptionistRole():
                    $image_attachment_id = get_user_meta($user_id,'receptionist_profile_image',true);
                    $kivicare_user = true;
                    break;
                case $this->getDoctorRole():
                    $image_attachment_id = get_user_meta($user_id,'doctor_profile_image',true);
                    $kivicare_user = true;
                    break;
                case $this->getPatientRole():
                    $image_attachment_id = get_user_meta($user_id,'patient_profile_image',true);
                    $kivicare_user = true;
                    break;
                case $this->getClinicAdminRole():
                    $image_attachment_id = get_user_meta($user_id,'clinic_admin_profile_image',true);
                    $kivicare_user = true;
                    break;
                case 'administrator':
                    $kivicare_user = true;
                    break;
                default:
                    # code...
                    break;
            }

            if(!$kivicare_user){
                echo json_encode( [
                    'status'      => false,
                    'status_code' => 403,
                    'message'     => $this->permission_message,
                    'data'        => []
                ] );
                wp_die();
            }
            $user->profile_photo = !empty($image_attachment_id) ? wp_get_attachment_url($image_attachment_id) : '';
            $setup_step_count = get_option($this->getSetupSteps());
            $steps = [];
            for ($i = 0; $i < $setup_step_count; $i++) {
                if (get_option('setup_step_' . ($i + 1))) {
                    $steps[$i] = json_decode(get_option('setup_step_' . ($i + 1)));
                }
            }
            $user->steps = $steps;
            $user->module = kcGetModules();
            $user->step_config = kcGetStepConfig();

            $user->default_clinic_id = kcGetDefaultClinicId();
            $user->unquie_id_status =(bool)kcPatientUniqueIdEnable('status');
            $user->unquie_id_value = generatePatientUniqueIdRegister();

            if($proPluginActive){
                $get_site_logo = get_option(KIVI_CARE_PREFIX.'site_logo');
                $enableEncounter = json_decode(get_option(KIVI_CARE_PREFIX.'enocunter_modules'));
                $enablePrescription = json_decode(get_option(KIVI_CARE_PREFIX.'prescription_module'));
                $user->encounter_enable_module = isset($enableEncounter->encounter_module_config) ? $enableEncounter->encounter_module_config : 0;
                $user->prescription_module_config = isset($enablePrescription->prescription_module_config) ? $enablePrescription->prescription_module_config : 0;
                $user->encounter_enable_count = $this->getEnableEncounterModule($enableEncounter);
                $user->theme_color = get_option(KIVI_CARE_PREFIX.'theme_color');
                $user->theme_mode = get_option(KIVI_CARE_PREFIX.'theme_mode');
                $user->site_logo  = isset($get_site_logo) && $get_site_logo!= null && $get_site_logo!= '' ? wp_get_attachment_url($get_site_logo) : -1;
                $user->pro_version = getKiviCareProVersion();
                $get_patient_cal_config= get_option( KIVI_CARE_PREFIX . 'patient_cal_setting',true);
                $user->is_patient_enable = in_array((string)$get_patient_cal_config,['1','true']) ? 'on' : 'off';
                $get_googlecal_config= get_option( KIVI_CARE_PREFIX . 'google_cal_setting',true);
                $user->is_enable_google_cal = !empty($get_googlecal_config['enableCal']) && in_array((string)$get_googlecal_config['enableCal'],['1','true']) ? 'on' : 'off';
                $user->google_client_id = !empty($get_googlecal_config['client_id']) ? trim($get_googlecal_config['client_id']) : 0;
                $user->google_app_name = !empty($get_googlecal_config['app_name']) ? trim($get_googlecal_config['app_name']) : 0;

                if($currentLoginUserRole == $this->getDoctorRole() || $currentLoginUserRole == $this->getReceptionistRole()){
                    $user->is_enable_doctor_gcal = get_user_meta($user_id, KIVI_CARE_PREFIX.'google_cal_connect',true) == 'on' ? 'on' : 'off';
                }
            }
            $user->is_enable_googleMeet = 'off';
            if($telemedGooglemeetActive) {
                if($currentLoginUserRole == $this->getDoctorRole()) {
                    $user->is_enable_doctor_gmeet = get_user_meta($user_id, KIVI_CARE_PREFIX.'google_meet_connect',true) == 'on' ? 'on' : 'off';
//                    $telemed_service_id = kcGetTelemedServiceId();
//                    $doctor_telemed_price = 0;
                    if(!empty($telemed_service_id)){
//                        $doctor_telemed_price = $this->db->get_var("SELECT charges FROM {$this->db->prefix}kc_service_doctor_mapping WHERE doctor_id={$user->ID} AND service_id={$telemed_service_id}");
                    }
                    $user->telemed_service_id = '';
                    $user->doctor_telemed_price = '' ;
                }
                $googleMeet =  get_option( KIVI_CARE_PREFIX . 'google_meet_setting',true);
                $user->is_enable_googleMeet = !empty($googleMeet['enableCal']) && in_array((string)$googleMeet['enableCal'] ,['1','true','Yes']) ? 'on' : 'off';
                $user->googlemeet_client_id = !empty($googleMeet['client_id']) ? trim($googleMeet['client_id']) : 0;
                $user->googlemeet_app_name = !empty($googleMeet['app_name']) ? trim($googleMeet['app_name']) : 0 ;
            }

            $zoomWarningStatus = $telemedStatus = true;
            if($telemedZoomPluginActive){
                if($currentLoginUserRole === $this->getDoctorRole()){
                    $zoomWarningStatus = $telemedStatus = false;
                    $zoomConfigData = apply_filters('kct_get_zoom_configuration', [
                        'user_id' => $user->ID,
                    ]);
                    if (isset($zoomConfigData['data']) && !empty($zoomConfigData['data'])) {
                        $zoomConfig = $zoomConfigData['data'];
                        $telemedStatus = !empty($zoomConfig->enableTeleMed) && strval($zoomConfig->enableTeleMed) == 'true' ;
                        $zoomWarningStatus = !empty($zoomConfig->api_key) || !empty($zoomConfig->api_secret) ;
                    }
                }
            }
            $user->enableTeleMed = !$zoomWarningStatus;
            $user->telemedConfigOn = $currentLoginUserRole === $this->getDoctorRole() ? kcDoctorTelemedServiceEnable($user->ID) : false;
            $user->teleMedStatus = $telemedStatus;
            $user->teleMedWarning = !$zoomWarningStatus;

            $user_data = get_user_meta($user->ID, 'basic_data', true);
            $user->timeSlot = '';
            $user->basicData = [];

            if (!empty($user_data)) {
                $user_data = json_decode($user_data);
                $user->timeSlot = isset($user_data->time_slot) ? $user_data->time_slot : "";
                $user->basicData = $user_data;
            }

        }

        $user->appointmentMultiFile = kcAppointmentMultiFileUploadEnable();
        $user->woocommercePayment = kcWoocommercePaymentGatewayEnable() ;
        $user->addOns = [
            'telemed' => $telemedZoomPluginActive,
            'kiviPro' => $proPluginActive,
            'googlemeet' => $telemedGooglemeetActive,
            'razorpay' => $razorpayPluginActive
        ];
        $default_clinic = get_option('setup_step_1');
        $option_data = [];
        if(!empty($default_clinic)){
            $option_data = json_decode($default_clinic, true);
        }
        $user->default_clinic = !empty($option_data['id'][0]) ? $option_data['id'][0] : '';
        $user->all_payment_method = kcAllPaymentMethodList();
        $fullCalendarSetting = get_option(KIVI_CARE_PREFIX . 'fullcalendar_setting',true);
        $user->fullcalendar_key = gettype($fullCalendarSetting) !== 'boolean' && !empty($fullCalendarSetting) ? $fullCalendarSetting : '';
        $user->doctor_rating_by_patient = $proPluginActive;
        $user->doctor_available = $user->doctor_service_available = $user->doctor_session_available = true;
        if($currentLoginUserRole === 'administrator'){
            $user->doctor_available = count(get_users(['role' => $this->getDoctorRole(),'fields' => ['ID']])) > 0;
            $user->doctor_service_available = $this->db->get_var("SELECT COUNT(*) FROM {$this->db->prefix}kc_service_doctor_mapping") > 0;
            $user->doctor_session_available = $this->db->get_var("SELECT COUNT(*) FROM {$this->db->prefix}kc_clinic_sessions") > 0;
        }
        $user->head_external_toolbar = [];
        $user->dashboard_sidebar_data = kcDashboardSidebarArray([$this->getLoginUserRole()]);
        if(has_filter('kivicare_head_external_toolbar')){
            $user->head_external_toolbar = apply_filters('kivicare_head_external_toolbar',[]);
            $user->head_external_toolbar = !empty( $user->head_external_toolbar) && is_array( $user->head_external_toolbar) ? $user->head_external_toolbar : [];
        }

        $user = apply_filters('kivicare_user_data',$user);
        
        echo json_encode([
            'status' => true,
            'message' => esc_html__('User data', 'kc-lang'),
            'data' => $user
        ]);

    }

	public function changePassword () {

		$request_data = $this->request->getInputs();

		$current_user = wp_get_current_user();

		$result = wp_check_password($request_data['currentPassword'], $current_user->user_pass, $current_user->ID);

		if ($result) {
			if(isset($current_user->ID) && !empty($current_user->ID)) {
				wp_set_password($request_data['newPassword'], $current_user->ID);
				$status = true ;
				$message = __('Password successfully changed' ,'kc-lang');
				wp_logout();
			} else {
				$status = false ;
				$message = __('Password change failed.' ,'kc-lang');
			}
		} else {
			$status = false ;
			$message = __('Current password is wrong!!','kc-lang');
		}

		echo json_encode([
			'status'  => $status,
			'data' => $result,
			'message' => $message,
		]);

	}

    public function getDashboard(){
        $clinicCurrency = kcGetClinicCurrenyPrefixAndPostfix();
        $clinic_prefix = !empty($clinicCurrency['prefix']) ? $clinicCurrency['prefix'] : '';
        $clinic_postfix = !empty($clinicCurrency['postfix']) ? $clinicCurrency['postfix'] : '';

        if (isKiviCareProActive()) {

            $response = apply_filters('kcpro_get_doctor_dashboard_detail', [
                'user_id' => get_current_user_id(),
                'clinic_prefix' => $clinic_prefix,
                'clinic_postfix' => $clinic_postfix
            ]);
            $response['data']['is_email_working'] = get_option(KIVI_CARE_PREFIX . 'is_email_working');
            echo json_encode($response);
        } else {
            if ($this->getLoginUserRole() == $this->getDoctorRole()) {
                $doctor_id =get_current_user_id();

                $todayAppointments = $appointments = [];
                if(kcCheckPermission('dashboard_total_today_appointment') || kcCheckPermission('dashboard_total_appointment')){
                    $appointments = collect(( new KCAppointment() )->get_by(['doctor_id' => $doctor_id]));
                    if(kcCheckPermission('dashboard_total_today_appointment')){
                        $today = date("Y-m-d");
                        $todayAppointments= $appointments->where('appointment_start_date', $today);
                    }
                    if(!kcCheckPermission('dashboard_total_appointment')){
                        $appointments = [];
                    }
                }

                $patient_count = 0;
                if(kcCheckPermission('dashboard_total_patient')){
                    $patient_count  = count(kcDoctorPatientList());
                }
                $service_count = 0;
                if(kcCheckPermission('dashboard_total_service')){
                    $service_table = $this->db->prefix . 'kc_service_doctor_mapping';
                    if(kcDoctorTelemedServiceEnable($doctor_id)){
                        $service = "SELECT  count(*) FROM {$service_table} WHERE `doctor_id` = {$doctor_id}";
                    }else{
                        $service_name_table = $this->db->prefix . 'kc_services';
                        $service = "SELECT  count(*) FROM {$service_table} join {$service_name_table} on {$service_name_table}.id= {$service_table}.service_id  WHERE {$service_table}.doctor_id = {$doctor_id} AND {$service_table}.telemed_service != 'yes'";
                    }
                    $service_count = $this->db->get_var( $service);
                }

                $data = [
                    'patient_count' => $patient_count,
                    'appointment_count' => count($appointments),
                    'today_count' => count($todayAppointments),
                    'service' => $service_count,
                ];

                echo json_encode([
                    'data' => $data,
                    'status' => true,
                    'message' => esc_html__('doctor dashboard', 'kcp-lang')
                ]);
                die;
            }

            $patients = [];
            if(kcCheckPermission('dashboard_total_patient')){
                $patients = get_users([
                    'role' => $this->getPatientRole(),
                    'fields' => ['ID']
                ]);
            }
            $doctors = [];
            if(kcCheckPermission('dashboard_total_doctor')) {
                $doctors = get_users([
                    'role' => $this->getDoctorRole(),
                    'fields' => ['ID']
                ]);
            }
            $appointment = 0;
            if(kcCheckPermission('dashboard_total_appointment')){
                $appointment = collect((new KCAppointment())->get_all())->count();
            }


            $bills = 0;
            if(kcCheckPermission('dashboard_total_revenue')) {
                $config = kcGetModules();
                $modules = collect($config->module_config)->where('name', 'billing')->where('status', 1)->count();
                if ($modules > 0) {
                    if(!empty(get_option(KIVI_CARE_PREFIX.'reset_revenue'))){
                        $reset_revenue_date = get_option(KIVI_CARE_PREFIX.'reset_revenue');
                        $bills = collect((new KCBill())->get_all())->where('payment_status' ,'=','paid')->where('created_at', '>', $reset_revenue_date )->sum('actual_amount');
                    }else{
                        $bills = collect((new KCBill())->get_all())->where('payment_status', '=', 'paid')->sum('actual_amount');
                    }
                }
            }


            $change_log = get_option('is_read_change_log');

            $telemed_change_log = get_option('is_telemed_read_change_log');

            $data = [
                'patient_count' => !empty($patients) ? count($patients) : 0,
                'doctor_count' => !empty($doctors) ? count($doctors) : 0,
                'appointment_count' => !empty($appointment) ? $appointment : 0,
                'revenue' => $clinic_prefix . $bills . $clinic_postfix,
                'change_log' => $change_log == 1,
                'telemed_log' => !($telemed_change_log == 1),
                'is_email_working' => get_option(KIVI_CARE_PREFIX . 'is_email_working')
            ];

            echo json_encode([
                'status' => true,
                'data' => $data,
                'message' => esc_html__('admin dashboard', 'kc-lang'),
            ]);
        }
    }

    public  function getWeeklyAppointment() {

        $appointments_table = $this->db->prefix . 'kc_' . 'appointments';
        $request_data = $this->request->getInputs();
        $clinic_condition = ' ';
        $current_user_login = $this->getLoginUserRole();
        if(!(in_array($current_user_login,['administrator',$this->getClinicAdminRole()]))){
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => $this->permission_message,
                'data'        => []
            ] );
            wp_die();
        }
        if($current_user_login == $this->getClinicAdminRole()){
            $clinic_condition = " AND clinic_id =".kcGetClinicIdOfClinicAdmin();
        }

        if(!empty($request_data['filterType']) && $request_data['filterType'] === 'monthly'){
            $monthQuery = "SELECT appointment_start_date AS `x`, COUNT(appointment_start_date) AS `y`  
                        FROM {$appointments_table} WHERE MONTH(appointment_start_date) = MONTH(CURRENT_DATE())
                        AND YEAR(appointment_start_date) = YEAR(CURRENT_DATE())  {$clinic_condition}
                          GROUP BY appointment_start_date ORDER BY appointment_start_date";

            $data = collect($this->db->get_results($monthQuery))->map(function ($v){
                $v->x = !empty($v->x) ? date(get_option('date_format'),strtotime($v->x)) : $v->x;
                return $v;
            })->toArray();

        }else{
            $sunday = strtotime("last monday");
            $sunday = date('w', $sunday) === date('w') ? $sunday+7*86400 : $sunday;
            $monday = strtotime(date("Y-m-d",$sunday)." +6 days");

            $week_start = date("Y-m-d",$sunday);
            $week_end = date("Y-m-d",$monday);

            $appointments = "SELECT DAYNAME(appointment_start_date)  AS `x`,
                         COUNT(DAYNAME(appointment_start_date)) AS `y`  
                        FROM {$appointments_table} WHERE appointment_start_date BETWEEN '{$week_start}' AND '{$week_end}' {$clinic_condition}
                          GROUP BY appointment_start_date";


            $arrday = [
                "Monday"=> esc_html__("Monday",'kc-lang'),
                "Tuesday"=> esc_html__("Tuesday",'kc-lang'),
                "Wednesday"=> esc_html__("Wednesday",'kc-lang'),
                "Thursday"=> esc_html__("Thursday",'kc-lang'),
                "Friday"=> esc_html__("Friday",'kc-lang'),
                "Saturday"=> esc_html__("Saturday",'kc-lang'),
                "Sunday"=> esc_html__("Sunday",'kc-lang')
            ];

            $data = collect($this->db->get_results($appointments))->toArray();
            $temp = [];
            $all_value_empty = empty($data);
            $temp = $this->convertWeekDaysToLang($data, $arrday);
            $data = $all_value_empty ? [] : $temp;
        }

        echo json_encode([
            'status'  => true,
            'data' => $data,
            'message' => (!empty($request_data['filterType']) ? $request_data['filterType'] : 'weekly'). esc_html__(' appointment', 'kc-lang'),
        ]);
    }

    public function convertWeekDaysToLang($data, $arrday) {
        $newData = array();
        foreach ($data as $object) {
          $newObject = new stdClass();
          $newObject->x = $arrday[$object->x];
          $newObject->y = $object->y;
          $newData[] = $newObject;
        }
        return $newData;
    }

	public function getTest () {
		echo json_encode([
			'status' => true,
			'message' => 'Test'
		]);
	}

	public function resendUserCredential() {

        $data = $this->request->getInputs();
        $data =  get_userdata($data['id']);
        if(isset($data->data)) {
            if(isset($data->roles[0]) && $data->roles[0] !==  null) {
                $password = kcGenerateString(12);
                wp_set_password($password, $data->data->ID);

                $user_email_param = [
                    'id' => $data->data->ID,
                    'username' => $data->data->user_login,
                    'user_email' => $data->data->user_email,
                    'password' => $password,
                    'patient_name' => $data->data->display_name,
                    'email_template_type' => 'resend_user_credential',
                ];

                $status = kcSendEmail($user_email_param);

                if(kcCheckSmsOptionEnable()){
                    $sms = apply_filters('kcpro_send_sms', [
                        'type' => 'resend_user_credential',
                        'user_data' => $user_email_param,
                    ]);
                }

                echo json_encode([
                    'status' => $status,
                    'data' => $data,
                    'message' => $status ? esc_html__('Password Resend Successfully', 'kc-lang') : esc_html__('Password Resend Failed', 'kc-lang')
                ]);
                die;

            }
            echo json_encode([
                'status' => false,
                'data' => $data,
                'message' => esc_html__('Password Resend Failed', 'kc-lang')
            ]);

            die;
        } else {
            echo json_encode([
                'status' => false,
                'message' => esc_html__('Requested user not found', 'kc-lang')
            ]) ;
        }
        wp_die();
    }

    public function setChangeLog () {

        $data = $this->request->getInputs();
        $change_log = '';
        if($data['log_type'] === 'version_read_change') {
            $change_log = update_option('is_read_change_log',1);
        } elseif ($data['log_type'] === 'telemed_read_load') {
            $change_log = update_option('is_telemed_read_change_log',1);
        }

        echo json_encode([
            'status'  => true,
            'data' => $change_log,
            'message' => esc_html__('Change Log', 'kc-lang'),
        ]);
    }

    public function getEnableEncounterModule($data){
        $encounter = collect($data->encounter_module_config);
        $encounter_enable = $encounter->where('status', 1)->count();
        if($encounter_enable == 1){
            $class = "12";
        }elseif ($encounter_enable == 2) {
            $class = "6";
        }else{
            $class = "4";
        }
        return $class;
    }

    public function renderShortcode(){
        $request_data = $this->request->getInputs();
        $shortcode_params = ' popup="on"';
        if(!empty($request_data['doctor_id'])){
            $shortcode_params .= " doctor_id={$request_data['doctor_id']} ";
        }
        if(!empty($request_data['clinic_id'])){
            $shortcode_params .= " clinic_id={$request_data['clinic_id']} ";
        }
        echo json_encode([
            'status' => true,
            'data'   => do_shortcode("[kivicareBookAppointment {$shortcode_params}]")
        ]);
        die;
    }

    public function changeModuleValueStatus(){
        $request_data = $this->request->getInputs();
        $rules = [
            'module_type' => 'required',
            'id' => 'required',
            'value' => 'required',
        ];

        $errors = kcValidateRequest($rules, $request_data);
        if (!empty(count($errors))) {
            echo json_encode([
                'status' => false,
                'message' => $errors[0]
            ]);
            die;
        }
        $request_data['value'] = esc_sql($request_data['value']);
        $request_data['id'] = (int)$request_data['id'];
        $current_user_role = $this->getLoginUserRole();
        switch($request_data['module_type']){
            case 'static_data':
                if (!(kcCheckPermission('static_data_edit'))) {
                    echo json_encode([
                        'status' => false,
                        'status_code' => 403,
                        'message' => $this->permission_message,
                        'data' => []
                    ]);
                    wp_die();
                }
                $this->db->update($this->db->prefix.'kc_static_data',['status' => $request_data['value']],['id' => $request_data['id']]);
                break;
            case 'custom_field':
                if (!(kcCheckPermission('custom_field_edit'))) {
                    echo json_encode([
                        'status' => false,
                        'status_code' => 403,
                        'message' => $this->permission_message,
                        'data' => []
                    ]);
                    wp_die();
                }
                $customFieldTable = $this->db->prefix.'kc_custom_fields';
                $results = $this->db->get_var("SELECT fields FROM {$customFieldTable} WHERE id={$request_data['id']}");
                if(!empty($results)){
                    $results = json_decode($results);
                    $results->status = strval($request_data['value']);
                    $this->db->update($customFieldTable,['status' => $request_data['value'],'fields' => json_encode($results)],['id' => $request_data['id']]);
                }
                break;
            case 'doctor_service':
                $permission = false;
                switch ($current_user_role) {
                    case $this->getReceptionistRole():
                    case $this->getClinicAdminRole():
                        $clinic_id = $current_user_role === $this->getReceptionistRole() ? kcGetClinicIdOfReceptionist() : kcGetClinicIdOfClinicAdmin();
                        $doctor_clinic = collect((new KCServiceDoctorMapping())->get_by(['service_id' => $request_data['id']], '=', false))->pluck('clinic_id')->toArray();
                        if (in_array($clinic_id, $doctor_clinic)) {
                            $permission = true;
                        }
                        break;
                    case $this->getDoctorRole():
                        $doctor_service = collect((new KCServiceDoctorMapping())->get_by(['id' => $request_data['id']], '=', false))->pluck('doctor_id')->toArray();
                        if (in_array(get_current_user_id(), $doctor_service)) {
                            $permission = true;
                        }
                        break;
                    case 'administrator':
                        $permission = true;
                        break;
                }
                if (!(kcCheckPermission('service_edit')) || !$permission) {
                    echo json_encode([
                        'status' => false,
                        'status_code' => 403,
                        'message' => $this->permission_message,
                        'data' => []
                    ]);
                    wp_die();
                }
                $this->db->update($this->db->prefix . 'kc_service_doctor_mapping', ['status' => $request_data['value']], ['id' => $request_data['id']]);
                break;
            case 'clinics':
                if(!kcCheckPermission( 'clinic_edit' )){
                    echo json_encode( [
                        'status'      => false,
                        'status_code' => 403,
                        'message'     => $this->permission_message,
                        'data'        => []
                    ] );
                    wp_die();
                }
                $this->db->update($this->db->prefix.'kc_clinics',['status' => $request_data['value']],['id' => $request_data['id']]);
                break;
            case 'doctors':
                $permission = false;
                if(in_array($current_user_role, [$this->getClinicAdminRole(),$this->getReceptionistRole()])){
                    $clinic_id = $current_user_role === $this->getReceptionistRole() ? kcGetClinicIdOfReceptionist() : kcGetClinicIdOfClinicAdmin();
                    $doctor_clinic = collect((new KCDoctorClinicMapping())->get_by(['doctor_id' => $request_data['id']],'=',false))->pluck('clinic_id')->toArray();
                    if(in_array($clinic_id,$doctor_clinic)){
                        $permission = true;
                    }
                }elseif($current_user_role === 'administrator'){
                    $permission = true;
                }

                if (!(kcCheckPermission( 'doctor_edit' )) || !$permission) {
                    echo json_encode([
                        'status' => false,
                        'status_code' => 403,
                        'message' => $this->permission_message,
                        'data' => []
                    ]);
                    wp_die();
                }
                $this->db->update($this->db->base_prefix.'users',['user_status' => $request_data['value']],['ID' => $request_data['id']]);
                break;
            case 'receptionists':
                $permission = false;
                if($current_user_role === $this->getClinicAdminRole()){
                    $clinic_id = kcGetClinicIdOfClinicAdmin();
                    $receptionist_clinic_id = collect((new KCReceptionistClinicMapping())->get_by(['doctor_id' => $request_data['id']],'=',false))->pluck('clinic_id')->toArray();
                    if(in_array($clinic_id,$receptionist_clinic_id)){
                        $permission = true;
                    }
                }elseif($current_user_role === 'administrator'){
                    $permission = true;
                }

                if (!(kcCheckPermission( 'receptionist_edit' )) || !$permission) {
                    echo json_encode([
                        'status' => false,
                        'status_code' => 403,
                        'message' => $this->permission_message,
                        'data' => []
                    ]);
                    wp_die();
                }
                $this->db->update($this->db->base_prefix.'users',['user_status' => $request_data['value']],['ID' => $request_data['id']]);
                break;
            case 'patients':
                $permission = false;
                switch ($current_user_role){
                    case $this->getReceptionistRole():
                    case $this->getClinicAdminRole():
                        $clinic_id = $current_user_role === $this->getReceptionistRole() ? kcGetClinicIdOfReceptionist() : kcGetClinicIdOfClinicAdmin();
                        $patient_clinic_id = collect((new KCPatientClinicMapping())->get_by(['doctor_id' => $request_data['id']],'=',false))->pluck('clinic_id')->toArray();
                        if(in_array($clinic_id,$patient_clinic_id)){
                            $permission = true;
                        }
                        break;
                    case $this->getDoctorRole():
                        $doctor_patient = kcDoctorPatientList(get_current_user_id());
                        $doctor_patient = !empty($doctor_patient) ? $doctor_patient : [-1];
                        if(in_array($request_data['id'],$doctor_patient)){
                            $permission = true;
                        }
                        break;
                    case 'administrator':
                        $permission = true;
                        break;
                }
                if (!(kcCheckPermission( 'patient_edit' )) || !$permission) {
                    echo json_encode([
                        'status' => false,
                        'status_code' => 403,
                        'message' => $this->permission_message,
                        'data' => []
                    ]);
                    wp_die();
                }
                $this->db->update($this->db->base_prefix.'users',['user_status' => $request_data['value']],['ID' => $request_data['id']]);
                break;
        }
        echo json_encode([
            'status' => true,
            'message' => __("Status Changes Successfully","kc-lang")
        ]);
        die;
    }

    public function saveTermsCondition () {

        $request_data = $this->request->getInputs();

        delete_option('terms_condition_content');
        delete_option('is_term_condition_visible');

        add_option( 'terms_condition_content', $request_data['content']);
        add_option( 'is_term_condition_visible', $request_data['isVisible']);

        echo json_encode([
            'status' => true,
            'message' => esc_html__('Terms & Condition saved successfully', 'kc-lang')
        ]);
    }

    public function getTermsCondition () {
        $term_condition = get_option( 'terms_condition_content') ;
        $term_condition_status = get_option( 'is_term_condition_visible') ;
        echo json_encode([
            'status' => true,
            'data' => array( 'isVisible' => $term_condition_status, 'content' => $term_condition)
        ]);
    }

    public function getCountryCurrencyList () {
        $country_currency_list = kcCountryCurrencyList() ;
        echo json_encode([
            'status' => true,
            'data' => $country_currency_list,
            'message' => esc_html__('country list', 'kc-lang')
        ]);
    }

    public function getVersionData() {

        $data = array(
                    'kivi_pro_version' => kcGetPluginVersion('kiviCare-clinic-&-patient-management-system-pro'),
                    'kivi_telemed_version' => kcGetPluginVersion('kiviCare-telemed-addon'),
                    'kivi_googlemeet_version' => kcGetPluginVersion('kc-googlemeet'),
                );
                
        echo json_encode([
            'status' => true,
            'data' => $data,
            'message' => esc_html__('Terms & Condition saved successfully', 'kc-lang')
        ]);
    }

	public function moduleWiseMultipleDataUpdate(){
		$request_data = $this->request->getInputs();
		$rules = [
			'module' => 'required',
			'data' => 'required',
			'action_perform' => 'required',
		];

		$errors = kcValidateRequest($rules, $request_data);
		if (!empty(count($errors))) {
			echo json_encode([
				'status' => false,
				'message' => $errors[0]
			]);
			die;
		}

		$field_pluck = in_array($request_data['module'],['patient','doctor','receptionist']) ? 'ID' : 'id';
		$ids = collect($request_data['data']['selectedRows'])->pluck($field_pluck)->map(function ($v){return (int)$v;})->toArray();
		$current_user_role = $this->getLoginUserRole();
		switch ( $request_data['module'] ) {
			case 'static_data':
				if ( ! ( kcCheckPermission( 'static_data_edit' ) ) ) {
					echo json_encode( [
						'status'      => false,
						'status_code' => 403,
						'message'     => $this->permission_message,
						'data'        => []
					] );
					wp_die();
				}
				$this->db->update( $this->db->prefix . 'kc_static_data', [ 'status' => $request_data['value'] ], [ 'id' => $request_data['id'] ] );
				break;
			case 'custom_field':
				if ( ! ( kcCheckPermission( 'custom_field_edit' ) ) ) {
					echo json_encode( [
						'status'      => false,
						'status_code' => 403,
						'message'     => $this->permission_message,
						'data'        => []
					] );
					wp_die();
				}
				$customFieldTable = $this->db->prefix . 'kc_custom_fields';
				$results          = $this->db->get_var( "SELECT fields FROM {$customFieldTable} WHERE id={$request_data['id']}" );
				if ( ! empty( $results ) ) {
					$results         = json_decode( $results );
					$results->status = strval( $request_data['value'] );
					$this->db->update( $customFieldTable, [
						'status' => $request_data['value'],
						'fields' => json_encode( $results )
					], [ 'id' => $request_data['id'] ] );
				}
				break;
			case 'doctor_service':
				$permission = false;
				switch ( $current_user_role ) {
					case $this->getReceptionistRole():
					case $this->getClinicAdminRole():
						$clinic_id     = $current_user_role === $this->getReceptionistRole() ? kcGetClinicIdOfReceptionist() : kcGetClinicIdOfClinicAdmin();
						$doctor_clinic = collect( ( new KCServiceDoctorMapping() )->get_by( [ 'service_id' => $request_data['id'] ], '=', false ) )->pluck( 'clinic_id' )->toArray();
						if ( in_array( $clinic_id, $doctor_clinic ) ) {
							$permission = true;
						}
						break;
					case $this->getDoctorRole():
						$doctor_service = collect( ( new KCServiceDoctorMapping() )->get_by( [ 'id' => $request_data['id'] ], '=', false ) )->pluck( 'doctor_id' )->toArray();
						if ( in_array( get_current_user_id(), $doctor_service ) ) {
							$permission = true;
						}
						break;
					case 'administrator':
						$permission = true;
						break;
				}
				if ( ! ( kcCheckPermission( 'service_edit' ) ) || ! $permission ) {
					echo json_encode( [
						'status'      => false,
						'status_code' => 403,
						'message'     => $this->permission_message,
						'data'        => []
					] );
					wp_die();
				}
				$this->db->update( $this->db->prefix . 'kc_service_doctor_mapping', [ 'status' => $request_data['value'] ], [ 'id' => $request_data['id'] ] );
				break;
			case 'clinics':
				if ( ! kcCheckPermission( 'clinic_edit' ) ) {
					echo json_encode( [
						'status'      => false,
						'status_code' => 403,
						'message'     => $this->permission_message,
						'data'        => []
					] );
					wp_die();
				}
				$this->db->update( $this->db->prefix . 'kc_clinics', [ 'status' => $request_data['value'] ], [ 'id' => $request_data['id'] ] );
				break;
			case 'doctors':
				$permission = false;
				if ( in_array( $current_user_role, [ $this->getClinicAdminRole(), $this->getReceptionistRole() ] ) ) {
					$clinic_id     = $current_user_role === $this->getReceptionistRole() ? kcGetClinicIdOfReceptionist() : kcGetClinicIdOfClinicAdmin();
					$doctor_clinic = collect( ( new KCDoctorClinicMapping() )->get_by( [ 'doctor_id' => $request_data['id'] ], '=', false ) )->pluck( 'clinic_id' )->toArray();
					if ( in_array( $clinic_id, $doctor_clinic ) ) {
						$permission = true;
					}
				} elseif ( $current_user_role === 'administrator' ) {
					$permission = true;
				}

				if ( ! ( kcCheckPermission( 'doctor_edit' ) ) || ! $permission ) {
					echo json_encode( [
						'status'      => false,
						'status_code' => 403,
						'message'     => $this->permission_message,
						'data'        => []
					] );
					wp_die();
				}
				$this->db->update( $this->db->base_prefix . 'users', [ 'user_status' => $request_data['value'] ], [ 'ID' => $request_data['id'] ] );
				break;
			case 'receptionists':
				$permission = false;
				if ( $current_user_role === $this->getClinicAdminRole() ) {
					$clinic_id              = kcGetClinicIdOfClinicAdmin();
					$receptionist_clinic_id = collect( ( new KCReceptionistClinicMapping() )->get_by( [ 'doctor_id' => $request_data['id'] ], '=', false ) )->pluck( 'clinic_id' )->toArray();
					if ( in_array( $clinic_id, $receptionist_clinic_id ) ) {
						$permission = true;
					}
				} elseif ( $current_user_role === 'administrator' ) {
					$permission = true;
				}

				if ( ! ( kcCheckPermission( 'receptionist_edit' ) ) || ! $permission ) {
					echo json_encode( [
						'status'      => false,
						'status_code' => 403,
						'message'     => $this->permission_message,
						'data'        => []
					] );
					wp_die();
				}
				$this->db->update( $this->db->base_prefix . 'users', [ 'user_status' => $request_data['value'] ], [ 'ID' => $request_data['id'] ] );
				break;
			case 'patient':
				$permission = true;
				switch ( $current_user_role ) {
					case $this->getReceptionistRole():
					case $this->getClinicAdminRole():
						$clinic_id = $current_user_role === $this->getReceptionistRole() ? kcGetClinicIdOfReceptionist() : kcGetClinicIdOfClinicAdmin();
					    $clinic_patent_id = collect( ( new KCPatientClinicMapping() )->get_by( [ 'clinic_id' => $clinic_id ], '=' ) )->pluck( 'patient_id' )->toArray();
						foreach ( $ids as $id ) {
							if ( ! in_array( $id, $clinic_patent_id ) ) {
								$permission = false;
								break;
							}
						}
						break;
					case $this->getDoctorRole():
						$doctor_patient = kcDoctorPatientList( get_current_user_id() );
						$doctor_patient = ! empty( $doctor_patient ) ? $doctor_patient : [ - 1 ];
						foreach ( $ids as $id ) {
							if ( !in_array( $id, $doctor_patient ) ) {
								$permission = false;
							}
						}
						break;
				}
				if ( ! ( kcCheckPermission( 'patient_edit' ) )  || !$permission ) {
					echo json_encode( [
						'status'      => false,
						'status_code' => 403,
						'message'     => $this->permission_message,
						'data'        => []
					] );
					wp_die();
				}
				$response = [
					'status' => true,
					'message' => esc_html__("Patient status successfully","kc-lang")
				];
				foreach ($ids as $id){
					switch ($request_data['action_perform'] ) {
						case 'delete':
							wp_delete_user($id);
							$response['message'] = esc_html__("Patient deleted successfully","kc-lang");
							break;
						case 'active':
							$this->db->update( $this->db->base_prefix . 'users', [ 'user_status' => 0 ], [ 'ID' => $id ] );
							break;
						case 'inactive':
							$this->db->update( $this->db->base_prefix . 'users', [ 'user_status' => 1 ], [ 'ID' => $id ] );
							break;
					}
				}
				echo json_encode($response);
				wp_die();
				break;
		}
	}
}
