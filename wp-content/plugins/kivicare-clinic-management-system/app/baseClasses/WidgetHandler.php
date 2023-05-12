<?php

namespace App\baseClasses;
use App\controllers\KCBookAppointmentWidgetController;
use App\controllers\KCServiceController;
use App\models\KCServiceDoctorMapping;
use function Clue\StreamFilter\fun;

class WidgetHandler extends KCBase {

    public $message = '';
	public function init() {
        add_action('init',function(){
            $this->message = $this->getLoginUserRole().(__(" can not view the widget. Please open this page in incognito mode or use another browser without an ","kc-lang")).$this->getLoginUserRole().(__(" login","kc-lang"));
        });

        add_shortcode('bookAppointment', [$this, 'bookAppointmentWidget']);
        add_shortcode('patientDashboard', [$this, 'patientDashboardWidget']);
        add_shortcode('kivicareBookAppointment', [$this, 'kivicareBookAppointmentWidget']);
        add_shortcode('kivicareBookAppointmentButton', [$this, 'kivicareBookAppointmentButtonWidget']);
        add_shortcode('kivicareRegisterLogin', [$this, 'kivicareRegisterLogin']);
    }



    //button appointment widget
    public function kivicareBookAppointmentButtonWidget($param){
        ob_start();
        if(empty($this->getLoginUserRole()) || $this->getLoginUserRole() === $this->getPatientRole()){
            wp_print_styles("kc_book_appointment");
            $this->shortcodeScript('kivicarePopUpBookAppointment');
            $this->shortcodeScript('kivicareBookAppointmentWidget');
            wp_enqueue_script('kc_bookappointment_widget');
            require KIVI_CARE_DIR . 'app/baseClasses/popupBookAppointment/bookAppointment.php';
        }else{
            echo esc_html($this->message);
        }
        return ob_get_clean();
    }

    //old appointment widget (vuejs)
    public function bookAppointmentWidget ($param) {
        ob_start();
        if(empty($this->getLoginUserRole()) || $this->getLoginUserRole() === $this->getPatientRole()){

            // sanitize parameters of shortcode
            $temp = $this->sanitizeShortCodeData($param,'bookAppointmentWidget');
            $doctor_id = $temp['doctor'];
            $clinic_id = $temp['clinic'];
            $service_id = $temp['service'];
            $user_id = get_current_user_id();

            // condition if clinic id provide in shortcode parameter or $_Get is available
            if($clinic_id != 0){
                if($this->checkIfPreSelectClinicExists($clinic_id)){
                    echo esc_html__('Selected clinic not available', 'kc-lang');
                    return ob_get_clean();
                }
            }else{
                // condition if anyy clinic is available in system and if only 1 available preselect it
                $kiviCareclinic = $this->checkIfKiviCareAnyClinicAvailable($clinic_id);
                if($kiviCareclinic['status']){
                    echo esc_html__('No clinic available', 'kc-lang');
                    return ob_get_clean();
                }else{
                    $clinic_id = $kiviCareclinic['clinic'];
                }
            }

            // condition if clinic id provide in shortcode parameter or $_Get is available
            if($doctor_id != 0){
                if($clinic_id == 0){
                    echo esc_html__('Please Provide clinic id', 'kc-lang');
                    return ob_get_clean();
                }
                if($this->checkIfPreSelectDoctorExists($doctor_id)){
                    echo esc_html__('Select Doctor Not available', 'kc-lang');
                    return ob_get_clean();
                }
            }else{
                // condition if anyy doctor is available in system and if only 1 available preselect it
                $kiviCareDoctor = $this->checkIfKiviCareAnyDoctorAvailable($doctor_id);
                if($kiviCareDoctor['status']){
                    echo esc_html__('No Doctor  available', 'kc-lang');
                    return ob_get_clean();
                }else{
                    if($clinic_id != 0){
                        $doctor_id = $kiviCareDoctor['doctor'];
                    }
                }
            }

            $this->shortcodeScript('bookAppointmentWidget');
            echo "<div id='app' class='kivi-care-appointment-booking-container kivi-widget' >
            <book-appointment-widget v-bind:user_id='$user_id' v-bind:doctor_id='$doctor_id' v-bind:clinic_id='$clinic_id' v-bind:service_id='$service_id' >
            </book-appointment-widget></div>";
        }else{
            echo esc_html($this->message);
        }
        return ob_get_clean();
    }

    //patient login/register widget (vuejs)
    public function patientDashboardWidget () {
        $theme_mode = get_option(KIVI_CARE_PREFIX . 'theme_mode');
        $rtl_attr = in_array($theme_mode,['1','true']) ? 'rtl' : '';
        ob_start();
        if(empty($this->getLoginUserRole()) || $this->getLoginUserRole() === $this->getPatientRole()){
            $this->shortcodeScript('patientDashboardWidget');
            echo "<div id='app' class='kivi-care-patient-dashboard-container kivi-widget' dir='{$rtl_attr}'><patient-dashboard-widget></patient-dashboard-widget></div>";
        }else{
            echo esc_html($this->message);
        }
        return ob_get_clean();
    }

    //new book appointment widget
    public function kivicareBookAppointmentWidget($param,$content,$tag){
        ob_start();
        if (empty($this->getLoginUserRole()) || $this->getLoginUserRole() === $this->getPatientRole()) {
            // sanitize parameters of shortcode
            $temp = $this->sanitizeShortCodeData($param,'kivicareBookAppointmentWidget');
            $shortcode_doctor_id = $temp['doctor'];
            $shortcode_clinic_id = $temp['clinic'];
            $shortcode_service_id = $temp['service'];
            $shortcode_doctor_id_single = $shortcode_service_id_single = false;

            if(kcGoogleCaptchaData('status') === 'on') {
                $siteKey = kcGoogleCaptchaData('site_key');
                if(empty($siteKey) && empty(kcGoogleCaptchaData('secret_key'))){
                    echo esc_html__('Google Recaptcha Data Not found', 'kc-lang');
                    return ob_get_clean();   
                }
                $this->googleRecaptchaload($siteKey);
            }
 
            // condition if clinic id provide in shortcode parameter or $_Get is available
            if($shortcode_clinic_id != 0){
                if($this->checkIfPreSelectClinicExists($shortcode_clinic_id)){
                    echo esc_html__('Selected clinic not available', 'kc-lang');
                    return ob_get_clean();
                }
            }else{
                // condition if anyy clinic is available in system and if only 1 available preselect it
                $kiviCareclinic = $this->checkIfKiviCareAnyClinicAvailable($shortcode_clinic_id);
                if($kiviCareclinic['status']){
                    echo esc_html__('No clinic available', 'kc-lang');
                    return ob_get_clean();
                }else{
                    $shortcode_clinic_id = $kiviCareclinic['clinic'];
                }
            }

            // condition if doctor id provide in shortcode parameter or $_Get is available
            if($shortcode_doctor_id != 0){
                if($shortcode_clinic_id == 0){
                    echo esc_html__('Please Provide clinic id', 'kc-lang');
                    return ob_get_clean();
                }
                if($this->checkIfPreSelectDoctorExists($shortcode_doctor_id)){
                    echo esc_html__('Select Doctor Not available', 'kc-lang');
                    return ob_get_clean();
                }
                $shortcode_doctor_id_single = !(strpos($shortcode_doctor_id, ',') !== false);
            }else{
                // condition if anyy doctor is available in system and if only 1 available preselect it
                $kiviCareDoctor = $this->checkIfKiviCareAnyDoctorAvailable($shortcode_doctor_id);
                if($kiviCareDoctor['status']){
                    echo esc_html__('No Doctor  available', 'kc-lang');
                    return ob_get_clean();
                }else{
                    if($shortcode_clinic_id != 0){
                        $shortcode_doctor_id_single = $kiviCareDoctor['single'];
                        $shortcode_doctor_id = $kiviCareDoctor['doctor'];
                    }
                }
            }

             //condition if service id provide in shortcode parameter or $_Get is available
            if($shortcode_service_id != 0){
                if($this->checkIfPreSelectServiceExists($shortcode_service_id)){
                    echo esc_html__('Select Service Not available', 'kc-lang');
                    return ob_get_clean();
                }
                $shortcode_service_id_single = !(strpos($shortcode_service_id, ',') !== false);
            }else{
                // condition if any service is available in system and if only 1 available preselect it
                $kiviCareService = $this->checkIfKiviCareAnyServiceAvailable($shortcode_service_id);
                if($kiviCareService['status']){
                    echo esc_html__('No service  available', 'kc-lang');
                    return ob_get_clean();
                }else{
                    $shortcode_service_id_single = $kiviCareService['single'];
                    $shortcode_service_id = $kiviCareService['service'];
                }
            }

            $popup = !empty($param['popup']) && $param['popup'] === 'on';
            wp_print_styles("kc_book_appointment");
            $this->shortcodeScript('kivicareBookAppointmentWidget');
            if(!$popup){
                wp_enqueue_script('kc_bookappointment_widget');
            }
            require KIVI_CARE_DIR . 'app/baseClasses/bookAppointment/bookAppointment.php';

        } else {

            echo esc_html($this->message);

        }

        return ob_get_clean();

    }

    //login/register widget
    public function kivicareRegisterLogin($param){
        ob_start();
        $this->shortcodeScript('kivicareRegisterLogin');
        if(kcGoogleCaptchaData('status') === 'on') {
            $siteKey = kcGoogleCaptchaData('site_key');
            if(empty($siteKey) && empty(kcGoogleCaptchaData('secret_key'))){
                echo esc_html__('Google Recaptcha Data Not found', 'kc-lang');
                return ob_get_clean();   
            }
            $this->googleRecaptchaload($siteKey);
        }
        $userList = [
            'kiviCare_patient' => __("Patient", "kc-lang"),
            'kiviCare_doctor'  => __('Doctor',"kc-lang"),
            'kiviCare_receptionist'  => __('Receptionist',"kc-lang"),
        ];
        $login = (bool)true;
        $register = (bool)true;
        if(isset($param) && !empty($param)){
            
            if(isset($param["login"]) || isset($param["register"])){
                $login = (bool)false;
                $register = (bool)false;
            }
            if(isset($param["login"]) && !empty($param["login"])){
                $login = (bool)$param["login"];
            }
            if(isset($param["register"]) && !empty($param["register"])){
                $register = (bool)$param["register"];
            }
            if(isset($param["userroles"]) && !empty($param["userroles"])){
                $userRolesList = explode(",",$param["userroles"]);
                $attr = [];
                if(!empty($userRolesList) && !empty($userList)){
                    foreach($userRolesList as $role){
                        if(!empty($role) && array_key_exists(trim($role),$userList)){
                            $attr[trim($role)] = $userList[trim($role)];
                        }
                    }
                }
                if(!empty($attr)){
                    $userList = $attr;
                }
            }

        }

        

        require KIVI_CARE_DIR . 'app/baseClasses/registerLogin/registerLogin.php';
        return ob_get_clean();
    }

    public function shortcodeScript($type){
        wp_enqueue_style('kc_font_awesome');
        if($type === 'kivicareBookAppointmentWidget'){
            wp_enqueue_style('kc_book_appointment');
            wp_enqueue_script('kc_axios');
            wp_enqueue_script('kc_flatpicker');
            wp_enqueue_style('kc_flatpicker');
            if(kcGetSingleWidgetSetting('widget_print')){
                wp_enqueue_script('kc_print');
            }
            if(isKiviCareProActive()){
                wp_enqueue_style('kc_calendar');
                wp_enqueue_script('kc_calendar');
            }
            do_action('kivicare_enqueue_script','shortcode_enqueue');
        }elseif($type == 'kivicarePopUpBookAppointment'){
            wp_enqueue_style('kc_popup');
            wp_enqueue_script('kc_popup');
            do_action('kivicare_enqueue_script','shortcode_enqueue');
        }elseif ($type === 'kivicareRegisterLogin'){
            wp_enqueue_style('kc_register_login');
            wp_enqueue_script('kc_axios');
            //wp_enqueue_style('kc_book_appointment');
        }else{
            $color = get_option(KIVI_CARE_PREFIX.'theme_color');
            if(!empty($color) && gettype($color) !== 'boolean' && isKiviCareProActive()){
                ?>
                <script> document.documentElement.style.setProperty("--primary-color", '<?php echo esc_js($color);?>');</script>
                <?php
            }
            kcAppendLanguageInHead();
            wp_enqueue_style('kc_front_app_min_style');
            wp_dequeue_style( 'stylesheet' );
            wp_enqueue_script('kc_custom');
            wp_enqueue_script('kc_front_js_bundle');
            do_action('kivicare_enqueue_script','shortcode_enqueue');
        }
    }

    public function sanitizeShortCodeData($param,$type){

        $shortcode_doctor_id = 0;
        $shortcode_clinic_id = 0;
        $shortcode_service_id = 0;
        if(isset($param['doctor_id']) && !empty($param['doctor_id'])){
            if($type === 'kivicareBookAppointmentWidget'){
                $shortcode_doctor_id = isset($param['doctor_id']) ? sanitize_text_field($param['doctor_id']) : 0;
            }else{
                $shortcode_doctor_id = isset($param['doctor_id']) ? '"'.sanitize_text_field($param['doctor_id']).'"' : 0;
            }
        }elseif(isset($_GET['doctor_id']) && !empty($_GET['doctor_id'])){
            $shortcode_doctor_id = sanitize_text_field(wp_unslash($_GET['doctor_id']));
        }

        if(isset($param['clinic_id']) && !empty($param['clinic_id'])){
            $shortcode_clinic_id = isset($param['clinic_id']) ? sanitize_text_field($param['clinic_id']) : 0;
        }elseif(isset($_GET['clinic_id']) && !empty($_GET['clinic_id'])){
            $shortcode_clinic_id = sanitize_text_field(wp_unslash($_GET['clinic_id']));
        }

        if(isset($param['service_id']) && !empty($param['service_id'])){
            $shortcode_service_id = isset($param['service_id']) ? sanitize_text_field($param['service_id']) : -1;
        }elseif(isset($_GET['service_id']) && !empty($_GET['service_id'])){
            $shortcode_clinic_id = sanitize_text_field(wp_unslash($_GET['clinic_id']));
        }
        $data = [
            'doctor' => $shortcode_doctor_id,
            'clinic' => $shortcode_clinic_id,
            'service' => $shortcode_service_id
        ];
        return apply_filters('kivicare_widget_shortcode_parameter',$data);
    }

    public function checkIfPreSelectClinicExists($clinic_id){
        global $wpdb;
        $status = false;
        // condition if clinic id provide in shortcode parameter or $_Get
        if($clinic_id != 0 ){
            $clinic_id = (int)$clinic_id;
            if(!isKiviCareProActive()){
                if($clinic_id != kcGetDefaultClinicId()){
                    $status = true;
                }
            }else{
                $clinic_count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}kc_clinics WHERE id = {$clinic_id} AND status = 1");
                if($clinic_count == 0){
                    $status = true;
                }
            }
        }
        return $status;
    }

    public function checkIfPreSelectDoctorExists($doctor_id){
        $args['role'] = $this->getDoctorRole();
        $args['user_status'] = '0';
        $args['fields'] = ['ID'];
        $doctor_id = array_filter(array_map('absint',explode(',',$doctor_id)));
        $doctor_id = !empty($doctor_id) ? $doctor_id : [-1];
        $args['include'] = $doctor_id;
        $allDoctor = get_users($args);
        return !(!empty($allDoctor) && count($allDoctor) > 0);
    }

    public function checkIfKiviCareAnyDoctorAvailable($doctor_id){
        $status = false;
        $single_doctor = false;
        $args['role'] = $this->getDoctorRole();
        $args['user_status'] = '0';
        $args['fields'] = ['ID'];
        $allDoctor = get_users($args);
        if(empty($allDoctor)){
            $status = true;
        }else if($doctor_id == 0 && count($allDoctor) === 1 ){
            $single_doctor = true;
            foreach ($allDoctor as $doc){
                $doctor_id = $doc->ID;
            }
        }

        return [
            'status' => $status,
            'doctor' => $doctor_id,
            'single' => $single_doctor
        ];
    }

    public function checkIfKiviCareAnyClinicAvailable($clinic_id){
        global $wpdb;
        $status = false;
        $clinic_count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->prefix}kc_clinics WHERE status = 1");

        //if no clinic data is found return
        if($clinic_count == 0){
            $status = true;
        }

        // if pro active and clinic id is not provide in shortcode or $_GET
        if(isKiviCareProActive() && $clinic_id === 0){
            // if only one clinic is available default selected
            if($clinic_count == 1){
                $clinic_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}kc_clinics WHERE status = 1");
            }
        }

        if(!isKiviCareProActive()){
            $clinic_id = kcGetDefaultClinicId();
        }
        return ['status' => $status , 'clinic' => $clinic_id];
    }

    public function googleRecaptchaload($siteKey){
        $siteKey = esc_js($siteKey);
        echo "<script src='https://www.google.com/recaptcha/api.js?render={$siteKey}'></script>";
    }

    public function checkIfPreSelectServiceExists($shortcode_service_id){
        $status = true;
        if(!empty($shortcode_service_id)){
            $shortcode_service_id = implode(",",array_map('absint',explode(',',$shortcode_service_id)));
            if(!empty($shortcode_service_id)){
                global $wpdb;
                $query = "SELECT COUNT(*) FROM {$wpdb->prefix}kc_service_doctor_mapping WHERE service_id IN ($shortcode_service_id) AND status = 1";
                $service_list = $wpdb->get_var($query);
                $status = empty($service_list);
            }
        }
        return $status;
    }

    public function checkIfKiviCareAnyServiceAvailable($shortcode_service_id){
        $service_id = $shortcode_service_id;
        $single_service = false;
        $status = false;
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}kc_service_doctor_mapping WHERE  status = 1";
        $service_list = $wpdb->get_results($query);
        if(empty($service_list)){
            $status = true;
        }else{
            if(count($service_list) === 1){
                $single_service = true;
                $service_id = $service_list[0]->service_id;
            }
        }
        return [
            'status' => $status,
            'service' => $service_id,
            'single' => $single_service
        ];
    }
}


