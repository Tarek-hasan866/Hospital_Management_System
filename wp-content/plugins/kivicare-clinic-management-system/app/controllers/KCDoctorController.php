<?php

namespace App\Controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCAppointment;
use App\models\KCAppointmentServiceMapping;
use App\models\KCCustomField;
use App\models\KCCustomFieldData;
use App\models\KCDoctorClinicMapping;
use App\models\KCClinicSession;
use App\models\KCClinicSchedule;
use App\models\KCPatientEncounter;
use App\models\KCServiceDoctorMapping;

use DateTime;
use Exception;
use WP_User;

class KCDoctorController extends KCBase
{

    public $db;

    private $request;

    public function __construct()
    {

        global $wpdb;

        $this->db = $wpdb;

        $this->request = new KCRequest();

        parent::__construct();
    }

    public function index()
    {

        $table_name = $this->db->prefix . 'kc_doctor_clinic_mappings';

        //check current login user permission
        if (!kcCheckPermission('doctor_list')) {
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $this->permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();

        //get user args
        $args['role']           = $this->getDoctorRole();
        $args['orderby']        = 'ID';
        $args['order']          = 'DESC';
        $args['fields']          = ['ID','display_name','user_email','user_registered','user_status'];
        if((int)$request_data['perPage'] > 0){
            $args['page'] = (int)$request_data['page'];
            $args['number'] = $request_data['perPage'];
            $args['offset'] = ((int)$request_data['page'] - 1) * (int)$request_data['perPage'];
        }

        $search_condition = '';
        $total = 0;
        if(!empty($request_data['sort'])){
            $request_data['sort'] = kcRecursiveSanitizeTextField(json_decode(stripslashes($request_data['sort'][0]),true));
            if(!empty($request_data['sort']['field']) && !empty($request_data['sort']['type']) && $request_data['sort']['type'] !== 'none'){
                $args['orderby']        = esc_sql($request_data['sort']['field']);
                $args['order']          = esc_sql(strtoupper($request_data['sort']['type']));
            }
        }

        //global filter
        if(isset($request_data['searchTerm']) && $request_data['searchTerm'] !== ''){
            $args['search_columns'] = ['user_email','ID','display_name','user_status'];
            $args['search'] = '*'.esc_sql(strtolower(trim($request_data['searchTerm']))).'*' ;
        }else{
            //column wise filter
            if(!empty($request_data['columnFilters'])){
                $request_data['columnFilters'] = json_decode(stripslashes($request_data['columnFilters']),true);
                foreach ($request_data['columnFilters'] as $column => $searchValue){
                    $searchValue = esc_sql(strtolower(trim($searchValue)));
                    if(empty($searchValue) ){
                        continue;
                    }
                    $column = esc_sql($column);
                    if($column === 'specialties'){
                        $args['meta_query'] = [
                            [
                                'key' => 'basic_data',
                                'value' => $searchValue,
                                'compare' => 'LIKE'
                            ]
                        ];
                        continue;
                    }else if($column === 'clinic_name' && isKiviCareProActive()){
                        $search_condition .= " AND {$this->db->users}.id IN (SELECT doctor_id FROM {$table_name} WHERE clinic_id={$searchValue}) ";
                        continue;
                    }
                    $search_condition .= " AND {$column} LIKE '%{$searchValue}%' ";

                }
            }
        }
        $current_user_role = $this->getLoginUserRole();
        $doctors = [];
        if (current_user_can('administrator')) {
            $results = $this->getUserData($args,$search_condition);
            //total doctors count
            $total = $results['total'];

            // doctors list
            $doctors = $results['list'];
        } else {
            if(isKiviCareProActive()){
                switch ($current_user_role) {
                    case $this->getReceptionistRole():
                        $clinic_id = kcGetClinicIdOfReceptionist();
                        $query = "SELECT DISTINCT `doctor_id` FROM {$table_name} WHERE `clinic_id` ={$clinic_id}";
                        break;
                    case $this->getClinicAdminRole():
                        $clinic_id = kcGetClinicIdOfClinicAdmin();
                        $query = "SELECT DISTINCT `doctor_id` FROM {$table_name} WHERE `clinic_id` ={$clinic_id}";
                        break;
                }

                if(!empty($query)){
                    // get role wise doctor id from mapping table
                    $result = collect($this->db->get_results($query))->pluck('doctor_id')->toArray();
                    // include mapping doctor in args array
                    $args['include'] = !empty($result) ? $result : [-1];
                    $results = $this->getUserData($args,$search_condition);
                    //total doctors count
                    $total = $results['total'];

                    // doctors list
                    $doctors = $results['list'];
                }
            }else{
                if(in_array($current_user_role , [$this->getReceptionistRole(),$this->getClinicAdminRole()]) ){
                    $results = $this->getUserData($args,$search_condition);
                    //total doctors count
                    $total = $results['total'];

                    // doctors list
                    $doctors = $results['list'];
                }
            }
        }

        if (!count($doctors)) {
            echo json_encode([
                'status' => false,
                'message' => esc_html__('No doctors found', 'kc-lang'),
                'data' => []
            ]);
            wp_die();
        }

        $data = [];
        foreach ($doctors as $key => $doctor) {
            $doctor->ID = (int)$doctor->ID;
            $allUserMeta = get_user_meta( $doctor->ID);
            $data[$key]['ID'] = $doctor->ID;
            $data[ $key ]['profile_image'] =!empty($allUserMeta['doctor_profile_image'][0]) ? wp_get_attachment_url($allUserMeta['doctor_profile_image'][0]) : '';
            $data[$key]['display_name'] = $doctor->display_name;
            //doctor clinic name
            $clinics = $this->db->get_row("SELECT GROUP_CONCAT(clinic.name) AS clinic_name,GROUP_CONCAT(clinic.id) AS clinic_id FROM {$this->db->prefix}kc_clinics AS clinic LEFT JOIN {$this->db->prefix}kc_doctor_clinic_mappings AS dr_clinic ON dr_clinic.clinic_id = clinic.id WHERE dr_clinic.doctor_id ={$doctor->ID} GROUP BY dr_clinic.doctor_id ");
            $data[$key]['clinic_name'] = !empty($clinics->clinic_name) ? $clinics->clinic_name : "";
            $data[$key]['clinic_id'] = !empty($clinics->clinic_id) ? $clinics->clinic_id : "";
            $data[$key]['user_email'] = $doctor->user_email;
            $data[$key]['user_status'] = $doctor->user_status;
            $data[$key]['user_registered'] = $doctor->user_registered;
            $data[$key]['user_registered_formated']= date("Y-m-d", strtotime($doctor->user_registered));
            $user_deactivate_status = !empty($allUserMeta['kivicare_user_account_status'][0]) ? $allUserMeta['kivicare_user_account_status'][0] : '';
            // verify doctor by shortcode condition
            $data[$key]['user_deactivate'] =!empty($user_deactivate_status) ? $user_deactivate_status : 'yes';
            //doctor other basic data
            $user_meta = !empty($allUserMeta['basic_data'][0]) ? $allUserMeta['basic_data'][0] : false ;
            if(!empty($user_meta)) {
                $basic_data = json_decode($user_meta,true);
                if(!empty($basic_data)) {
                    foreach ($basic_data as $basic_data_key => $basic_data_value){
                        if($basic_data_key === 'specialties'){
                            $data[$key][$basic_data_key] =   collect($basic_data_value)->pluck('label')->implode(',') ;
                        }else if($basic_data_key === 'qualifications'){
                            $data[$key][$basic_data_key] = collect($basic_data_value)->map(function($v){
                                return $v->degree .'( '.$v->university.'-'.$v->year.' )';
                            })->implode(',');
                        }else{
                            $data[$key][$basic_data_key] = $basic_data_value;
                        }
                    }
                }
            }
            foreach (['dob','address','city','country','postal_code','gender','blood_group','mobile_number'] as $item){
                if(!array_key_exists($item,$data[ $key ])){
                    $data[ $key ][$item] = '-';
                }
            }
            $data[ $key ]['full_address'] = ('#').(!empty($data[ $key ]['address']) && $data[ $key ]['address'] !== '-' ? $data[ $key ]['address'].',' : '' ) .
                (!empty($data[ $key ]['city']) && $data[ $key ]['city'] !== '-' ? $data[ $key ]['city'].',' : '').
                (!empty($data[ $key ]['postal_code']) && $data[ $key ]['postal_code'] !== '-' ? $data[ $key ]['postal_code'].',' : '').
                (!empty($data[ $key ]['country'])  && $data[ $key ]['country'] !== '-' ? $data[ $key ]['country'] : '');
                $data[$key] = apply_filters('kivicare_doctor_lists',$data[$key],$doctor,$allUserMeta);
        }

        echo json_encode([
            'status' => true,
            'message' => esc_html__('Doctors list', 'kc-lang'),
            'data' => $data,
            'total_rows' => $total
        ]);
    }

    public function save()
    {
        global $wpdb;
        //check current login user permission
        $is_permission = kcCheckPermission('doctor_add') || kcCheckPermission('doctor_profile');
        if (!$is_permission) {
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $this->permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();
        $current_user_role = $this->getLoginUserRole();

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'user_email' => 'required|email',
            'mobile_number' => 'required',
            'gender' => 'required',
        ];

        $errors = kcValidateRequest($rules, $request_data);

        if (!empty(count($errors))) {
            echo json_encode([
                'status' => false,
                'message' => $errors[0]
            ]);
            die;
        }

        //check email condition
        $email_condition = kcCheckUserEmailAlreadyUsed($request_data);
        if(empty($email_condition['status'])){
            echo json_encode($email_condition);
            die;
        }


        $temp = [
            'mobile_number' => $request_data['mobile_number'],
            'gender' => $request_data['gender'],
            'dob' => $request_data['dob'],
            'address' => $request_data['address'],
            'city' => $request_data['city'],
            'state' => !empty($request_data['state']) ? $request_data['state'] : '',
            'country' => $request_data['country'],
            'postal_code' => $request_data['postal_code'],
            'qualifications' => !empty($request_data['qualifications']) ? $request_data['qualifications'] : [] ,
            'price_type' => $request_data['price_type'],
            'price' => $request_data['price'],
            'no_of_experience' => $request_data['no_of_experience'],
            'video_price' => isset($request_data['video_price']) ? $request_data['video_price'] : 0,
            'specialties' => !empty($request_data['specialties']) ? $request_data['specialties'] : [],
            'time_slot' => $request_data['time_slot']
        ];

        if (isset($request_data['price_type']) && $request_data['price_type'] === "range") {
            $temp['price'] = $request_data['minPrice'] . '-' . $request_data['maxPrice'];
        }

//        $service_doctor_mapping = new KCServiceDoctorMapping();
        if (!isset($request_data['ID'])) {

            // create new user
            $password = kcGenerateString(12);
            $user = wp_create_user(kcGenerateUsername($request_data['first_name']), $password, sanitize_email( $request_data['user_email']) );
            $u = new WP_User($user);
            $u->display_name = $request_data['first_name'] . ' ' . $request_data['last_name'];
            wp_insert_user($u);
            //add doctor role to user
            $u->set_role($this->getDoctorRole());

            $user_id = $u->ID;

            //clinic id based on role
            if ($current_user_role == $this->getReceptionistRole()) {
                $request_data['clinic_id'] = kcGetClinicIdOfReceptionist();
            }
            if($current_user_role == $this->getClinicAdminRole()){
                $request_data['clinic_id'] = kcGetClinicIdOfClinicAdmin();
            }

            if (isKiviCareProActive()) {
                if ($current_user_role == $this->getReceptionistRole()) {
                    $this->saveDoctorClinic($user_id,kcGetClinicIdOfReceptionist());
                }else if($current_user_role == $this->getClinicAdminRole()){
                    $this->saveDoctorClinic($user_id,kcGetClinicIdOfClinicAdmin());
                }else{
                    foreach ($request_data['clinic_id'] as $value) {
                        $this->saveDoctorClinic($user_id,$value['id']);
                    }
                }
            } else {
                //if pro not active default clinic id in mapping table
                $this->saveDoctorClinic($user_id,kcGetDefaultClinicId());
            }

            //get email/sms/whatsapp template dynamic key array
            $user_email_param = kcCommonNotificationUserData((int)$user_id,$password);

            //send email after doctor save
            kcSendEmail($user_email_param);

            //send sms/whatsapp after doctor save
            if(!empty(kcCheckSmsOptionEnable())){
                $sms = apply_filters('kcpro_send_sms', [
                    'type' => 'doctor_registration',
                    'user_data' => $user_email_param,
                ]);
            }

            $message = esc_html__('Doctor has been saved successfully', 'kc-lang');

            // hook for doctor save
            do_action( 'kc_doctor_save', $user_id );

        } else {

            //update doctor user
            wp_update_user(
                array(
                    'ID' => (int)$request_data['ID'],
                    'user_email' => sanitize_email( $request_data['user_email'] ),
                    'display_name' => $request_data['first_name'] . ' ' . $request_data['last_name']
                )
            );
            $request_data['ID'] = (int)$request_data['ID'];
            $user_id = $request_data['ID'];
            if(in_array($current_user_role,['administrator',$this->getDoctorRole()])){
                if (isKiviCareProActive()) {
                    (new KCDoctorClinicMapping())->delete(['doctor_id' => $request_data['ID']]);
                    foreach ($request_data['clinic_id'] as $value) {
                        $this->saveDoctorClinic($user_id,$value['id']);
                    }
                }else{
                    //if pro not active default clinic id in mapping table
                    (new KCDoctorClinicMapping())->delete(['doctor_id' => $request_data['ID']]);
                    $this->saveDoctorClinic($user_id,kcGetDefaultClinicId());
                }
            }

            // hook for doctor update
            do_action( 'kc_doctor_update', $user_id );

            $message = __('Doctor updated successfully','kc-lang');

        }

        if ($user_id) {

            // Zoom telemed Service entry save
            if(isKiviCareTelemedActive()) {

                //get telemed service id

                //get doctor telemed service
                if($request_data['enableTeleMed'] == '' || empty($request_data['enableTeleMed'])){
                    $request_data['enableTeleMed'] = 'false';
                }
                $request_data['enableTeleMed'] = in_array((string)$request_data['enableTeleMed'],['true','1']) ? 'true' : 'false';
//                $telemed_Service =kcGetTelemedServiceId();
//                $doctor_telemed_service = $service_doctor_mapping->get_by(['service_id' => $telemed_Service, 'doctor_id' => (int)$user_id]);
//                $telemed_service_status = $request_data['enableTeleMed'] === 'true' ? 1 : 0;

                //check doctor telemed exists
//                if (!empty($doctor_telemed_service) > 0) {
//                    $service = new KCServiceController();
//                    //get woocommerce product id of service
//                    $woo_product_id = $service->getProductIdOfService($doctor_telemed_service[0]->id);
//                    //check if product woocommerce product and update woo product price
//                    if($woo_product_id != null &&  get_post_status( $woo_product_id )){
//                        update_post_meta($woo_product_id,'_price', $request_data['video_price']);
//                        update_post_meta($woo_product_id,'_sale_price', $request_data['video_price']);
//                    }
//                    //update doctor telemed service detail
//                    $service_doctor_mapping->update(['charges' => $temp['video_price'], 'status' => $telemed_service_status], [
//                        'service_id' => $telemed_Service,
//                        'doctor_id' => (int)$user_id,
//                    ]);
//                } else {
//                    //insert doctor telemed service
//                    $service_doctor_mapping->insert([
//                        'service_id' => $telemed_Service,
//                        'clinic_id' => kcGetDefaultClinicId(),
//                        'doctor_id' => (int)$user_id,
//                        'charges' => $temp['video_price'],
//                        'status' => $telemed_service_status
//                    ]);
//                }

                //save zoom configurations details
                if(!empty($request_data['api_key']) && !empty($request_data['api_secret'])){
                    apply_filters('kct_save_zoom_configuration', [
                        'user_id' => (int)$user_id,
                        'enableTeleMed' => $request_data['enableTeleMed'],
                        'api_key' => $request_data['api_key'],
                        'api_secret' => $request_data['api_secret']
                    ]);
                }
            }

            //update user firstname
            update_user_meta($user_id, 'first_name', $request_data['first_name']);

            //update/save user lastname
            update_user_meta($user_id, 'last_name', $request_data['last_name']);

            //update/save user other details
            update_user_meta($user_id, 'basic_data', json_encode($temp, JSON_UNESCAPED_UNICODE));

            //update/save user description
            if(isset($request_data['description']) && !empty($request_data['description'])){
                update_user_meta($user_id, 'doctor_description',$request_data['description'] );
            }

            //update/save user custom field
            if (!empty($request_data['custom_fields'])) {
                kvSaveCustomFields('doctor_module', $user_id, $request_data['custom_fields']);
            }

            //update/save user digital signature
            $doc_signature = !empty($request_data['signature']) ? $request_data['signature'] : '';
            update_user_meta($user_id ,'doctor_signature',$doc_signature);

            //update/save doctor profile image
            if(isset($request_data['profile_image']) && !empty((int)$request_data['profile_image']) ) {
                update_user_meta( $user_id, 'doctor_profile_image',  (int)$request_data['profile_image'] );
            }

            //update/save user status
            $wpdb->update($wpdb->base_prefix . 'users', ['user_status' => $request_data['user_status']], ['ID' => (int)$user_id]);
        }


        if (!empty($user->errors)) {
            echo json_encode([
                'status' => false,
                'message' => $user->get_error_message() ? $user->get_error_message() : esc_html__('Failed to save Doctor data.', 'kc-lang')
            ]);
        } else {
            echo json_encode([
                'status' => true,
                'message' => $message
            ]);
        }
    }

    public function edit()
    {
        $is_permission = false;

        //check current login user permission
        if (kcCheckPermission('doctor_edit') || kcCheckPermission('doctor_view') || kcCheckPermission('doctor_profile')) {
            $is_permission = true;
        }
        if(!$is_permission){
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $this->permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();

        
        try {

            if (!isset($request_data['id'])) {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }


            $id = (int)$request_data['id'];

            $user = get_userdata($id);
            unset($user->user_pass);
            $allUserMetaData = get_user_meta( $id);
            $user_data  = !empty($allUserMetaData['basic_data'][0]) ? $allUserMetaData['basic_data'][0] : [];

            //remove null value from object
            if(!empty($user_data)){
                $user_data = array_map(function ($v){
                    if(is_null($v) || $v == 'null'){
                        $v = '';
                    }
                    return $v;
                },(array)json_decode($user_data));
            }else{
                $user_data = [];
            }
            $user_image_url = !empty($allUserMetaData['doctor_profile_image'][0]) ? wp_get_attachment_url($allUserMetaData['doctor_profile_image'][0]) : '';
            $first_name = !empty($allUserMetaData['first_name'][0]) ? $allUserMetaData['first_name'][0] : '';
            $last_name  = !empty($allUserMetaData['last_name'][0]) ? $allUserMetaData['last_name'][0] : '';
            $description = !empty($allUserMetaData['doctor_description'][0]) ? $allUserMetaData['doctor_description'][0] : '';

            $data = (object)array_merge((array)$user->data, $user_data);
            $data->first_name = $first_name;
            $data->username = $data->user_login;
            $data->description = !empty($description) ? $description : '';
            $data->last_name = $last_name;
            $data->qualifications = !empty($data->qualifications) ? $data->qualifications : [];
            $data->specialties = !empty($data->specialties) ? $data->specialties : [];
//            $telemed_charges = getDoctorTelemedServiceCharges($id);
//            $data->video_price = $telemed_charges;
            $doctor_rating = kcCalculateDoctorReview($id,'list');
            $data->rating = $doctor_rating['star'];
            $data->total_rating = $doctor_rating['total_rating'];
            $data->is_enable_doctor_gmeet = isKiviCareGoogleMeetActive() && get_user_meta($id, KIVI_CARE_PREFIX.'google_meet_connect',true) == 'on' &&  get_user_meta($id,'telemed_type',true) === 'googlemeet';
            //doctor clinic
            $clinics = collect($this->db->get_results("SELECT clinic.id AS id ,clinic.name AS label FROM {$this->db->prefix}kc_clinics AS clinic 
            LEFT JOIN {$this->db->prefix}kc_doctor_clinic_mappings AS doctor_clinic ON doctor_clinic.clinic_id =clinic.id 
            WHERE doctor_clinic.doctor_id={$id}"))
                ->map(function ($v){
                    return ['id' => $v->id ,'label' => $v->label];
                })->toArray();
            $data->clinic_id = $clinics;
            if (isset($data->price_type)) {
                if ($data->price_type === "range") {
                    $price = explode("-", $data->price);
                    $data->minPrice = isset($price[0]) ? $price[0] : 0;
                    $data->maxPrice = isset($price[1]) ? $price[1] : 0;
                    $data->price = 0;
                }
            } else {
                $data->price_type = "range";
            }

            //doctor telemed descriptions
            if(isKiviCareTelemedActive()) {

                $config_data = apply_filters('kct_get_zoom_configuration', [
                    'user_id' => $id,
                ]);

                if (isset($config_data['status']) && $config_data['status']) {
                    $data->enableTeleMed =$config_data['data']->enableTeleMed;
                    $data->api_key = !empty($config_data['data']->api_key) && $config_data['data']->api_key !== 'null' ? $config_data['data']->api_key : '';
                    $data->api_secret = !empty($config_data['data']->api_secret) && $config_data['data']->api_secret !== 'null' ? $config_data['data']->api_secret : '';
                    $data->zoom_id = $config_data['data']->zoom_id;
                }
            }

            //doctor digital details
            $data->signature = '';
            $doctor_signature =!empty($allUserMetaData['doctor_signature'][0]) ? $allUserMetaData['doctor_signature'][0] : '';
            if(!empty($doctor_signature)){
                $data->signature = strval($doctor_signature);
            }

            //doctor custom field
            $custom_filed = kcGetCustomFields('doctor_module', $id);
            $data->user_profile =$user_image_url;
            if ($data) {

                //check if request doctor detail is from valid user
                $current_user_role = $this->getLoginUserRole();
                $validate = true;
                if ($current_user_role == $this->getClinicAdminRole()) {
                    $clinic_id = kcGetClinicIdOfClinicAdmin();
                    $clinic_id_array =collect($clinics)->pluck('id')->map(function ($v){
                        return (int)$v;
                    })->toArray();
                    if (!in_array($clinic_id,$clinic_id_array)) {
                        $validate = false;
                    }
                } elseif ($current_user_role == $this->getReceptionistRole()) {
                    $clinic_id = kcGetClinicIdOfReceptionist();
                    $clinic_id_array =collect($clinics)->pluck('id')->map(function ($v){
                        return (int)$v;
                    })->toArray();
                    if (!in_array($clinic_id,$clinic_id_array)) {
                        $validate = false;
                    }
                } elseif ($current_user_role == $this->getDoctorRole()) {
                    if (get_current_user_id() !== $id) {
                        $validate = false;
                    }
                }

                //request doctor detail is not from valid user redirect to 403 page
                if (!$validate) {
                    echo json_encode( [
                        'status' => false,
                        'status_code' => 403,
                        'message' => esc_html__('You don\'t have permission to access', 'kc-lang'),
                        'data' => []
                    ]);
                    die;
                }
                $data = apply_filters('kivicare_doctor_edit_data',$data,$allUserMetaData);
                echo json_encode([
                    'status' => true,
                    'message' => 'Doctor data',
                    'id' => $id,
                    'user_data' => $user_data,
                    'data' => $data,
                    'custom_filed'=>$custom_filed
                ]);

            } else {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }


        } catch (Exception $e) {

            $code = $e->getCode();
            $message = $e->getMessage();

            header("Status: $code $message");

            echo json_encode([
                'status' => false,
                'message' => $message
            ]);
        }
    }

    public function delete()
    {

        //check current login user permission
        if (!kcCheckPermission('doctor_delete')) {
            echo json_encode([
                'status' => false,
                'status_code' => 403,
                'message' => $this->permission_message,
                'data' => []
            ]);
            wp_die();
        }

        $request_data = $this->request->getInputs();

        try {

            if (!isset($request_data['id'])) {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }

            $id = (int)$request_data['id'];

            //delete doctor zoom telemed service
            if (isKiviCareTelemedActive()) {
                apply_filters('kct_delete_patient_meeting', ['doctor_id' => $id]);
            }

            // hook for doctor delete
            do_action( 'kc_doctor_delete', $id );

            //delete all doctor related entry

            //delete doctor holiday
            (new KCClinicSchedule())->delete(['module_id' => $id, 'module_type' => 'doctor']);

            //delete doctor clinic session
            (new KCClinicSession())->delete(['doctor_id' => $id]);

            //doctor clinic mapping entry
            (new KCDoctorClinicMapping())->delete(['doctor_id' => $id]);

            // send appointment cancel notification  based on conditiom
            $allAppointments = $this->db->get_results("SELECT * FROM {$this->db->prefix}kc_appointments WHERE doctor_id={$id}");
            if(!empty($allAppointments)){
                foreach ($allAppointments as $res){
                    //check if appointment date and time greater than current time and status is booked
                    if($res->appointment_start_date >= current_time('Y-m-d') && in_array($res->status,['1',1])){
                        $email_data = kcCommonNotificationData($res,[],'','cancel_appointment');
                        //send cancel email
                        kcSendEmail($email_data);
                        if(!empty(kcCheckSmsOptionEnable()) || !empty(kcCheckWhatsappOptionEnable())){
                            apply_filters('kcpro_send_sms', [
                                'type' => 'cancel_appointment',
                                'appointment_id' => $res->id,
                            ]);
                        }
                    }
                    //cancel zoom meeting
                    if(isKiviCareTelemedActive()){
                        apply_filters('kct_delete_appointment_meeting', ['id'=>$res->id]);
                    }
                    //remove google calendar event
                    if(kcCheckGoogleCalendarEnable()){
                        apply_filters('kcpro_remove_appointment_event', ['appoinment_id'=>$res->id]);
                    }

                    //remove google meet event
                    if(isKiviCareGoogleMeetActive()){
                        apply_filters('kcgm_remove_appointment_event',['appoinment_id' => $res->id]);
                    }
                    //delete appointment service mappings
                    (new KCAppointmentServiceMapping())->delete(['appointment_id' => $res->id]);

                    //delete appointment custom fields
                    (new KCCustomFieldData())->delete(['module_type' => 'appointment_module','module_id' => $res->id]);
                }
            }
            //delete doctor appointment
            (new KCAppointment())->delete(['doctor_id' => $id]);
            //delete doctor encounter
            (new KCPatientEncounter())->delete(['doctor_id' => $id]);

            // delete woocommerce product on service delete
            collect((new KCServiceDoctorMapping())->get_by(['doctor_id' => $id]))->pluck('id')->map(function($v){
                $product_id = kivicareGetProductIdOfService($v);
                if($product_id != null && get_post_status( $product_id )){
                    do_action( 'kc_woocoomerce_service_delete', $product_id );
                    wp_delete_post($product_id);
                }
                return $v;
            });
            //delete doctor service
            (new KCServiceDoctorMapping())->delete(['doctor_id' => $id]);
            //delete current doctor custom field
            (new KCCustomField())->delete(['module_type' => 'appointment_module','module_id' => $id]);
            (new KCCustomFieldData())->delete(['module_type' => 'doctor_module','module_id' => $id]);

            //delete doctor usermeta
            delete_user_meta($id, 'basic_data');
            delete_user_meta($id, 'first_name');
            delete_user_meta($id, 'last_name');
            //delete user
            $results = wp_delete_user($id);
            if ($results) {
                echo json_encode([
                    'status' => true,
                    'message' => esc_html__('Doctor has been deleted successfully', 'kc-lang'),
                ]);
            } else {
                throw new Exception(esc_html__('Data not found', 'kc-lang'), 400);
            }


        } catch (Exception $e) {

            $code = $e->getCode();
            $message = $e->getMessage();

            header("Status: $code $message");

            echo json_encode([
                'status' => false,
                'message' => $message
            ]);
        }
    }

    public function getDoctorWorkdays(){
        $request_data = $this->request->getInputs();
        $results = [];
        $status = false;
        $login_user_role = $this->getLoginUserRole();

        if($this->getDoctorRole() === $login_user_role) {
            $request_data['doctor_id'] = get_current_user_id() ;
        }

        if(isKiviCareProActive()){
            if($login_user_role == 'kiviCare_clinic_admin'){
                $request_data['clinic_id'] = kcGetClinicIdOfClinicAdmin();
            }elseif ($login_user_role == 'kiviCare_receptionist') {
                $request_data['clinic_id'] = kcGetClinicIdOfReceptionist();
            }
        }


        //check doctor and clini id exists in request data
        if(isset($request_data['clinic_id']) && $request_data['clinic_id'] != '' &&
            isset($request_data['doctor_id']) && $request_data['doctor_id'] != ''){

            $request_data['doctor_id'] = (int)$request_data['doctor_id'];
            $request_data['clinic_id'] = (int)$request_data['clinic_id'];


            //get doctor session days
            $results = collect($this->db->get_results("SELECT DISTINCT day FROM {$this->db->prefix}kc_clinic_sessions where doctor_id={$request_data['doctor_id']} AND clinic_id={$request_data['clinic_id']}"))->pluck('day')->toArray();

            //week day for php vue appointment dashboard
            $days = [1 => 'sun', 2 => 'mon', 3 =>'tue', 4 => 'wed', 5 => 'thu', 6 => 'fri', 7 => 'sat'];
            //week day for php shortcode widget
            if(!empty($request_data['type']) && $request_data['type'] === 'flatpicker'){
                $days = [0 => 'sun', 1 => 'mon', 2 =>'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat'];
            }


            if(count($results) > 0){
                // get unavilable  days
               $results = array_diff(array_values($days),$results);
               //get key of unavilable days
               $results = array_map(function ($v) use ($days){
                   return array_search($v,$days);
               },$results);
               $results = array_values($results);
            }
            else{
                //get all days keys
                $results = array_keys($days);
            }
            $status = true;
        }
        echo json_encode([
            'status' => $status,
             'data' => $results
        ]);
    }

    public function getDoctorWorkdayAndSession(){
        $request_data = $this->request->getInputs();
        $doctors_sessions = doctorWeeklyAvailability(['clinic_id'=>$request_data['clinic_id'],'doctor_id'=>$request_data['doctor_id']]);
        echo json_encode([
            'data' => $doctors_sessions,
            'status' => true
        ]);
    }

    public function saveDoctorClinic($doctor_id,$clinic_id){
        //save/update doctor clinic mappings
        $doctor_mapping = new KCDoctorClinicMapping;
        $new_temp = [
            'doctor_id' => (int)$doctor_id,
            'clinic_id' => (int)$clinic_id,
            'owner' => 0,
            'created_at' => current_time('Y-m-d H:i:s')
        ];
        $doctor_mapping->insert($new_temp);
    }
}
