<?php

namespace App\Controllers;

use App\baseClasses\KCBase;
use App\models\KCAppointment;
use App\models\KCAppointmentServiceMapping;
use App\models\KCClinic;
use App\models\KCClinicSchedule;
use App\models\KCClinicSession;
use App\models\KCCustomField;
use App\models\KCCustomFieldData;
use App\models\KCDoctorClinicMapping;
use App\models\KCPatientClinicMapping;
use App\models\KCPatientEncounter;
use App\models\KCReceptionistClinicMapping;
use App\baseClasses\KCRequest;
use App\models\KCServiceDoctorMapping;
use Exception;
use WP_User;

class KCClinicController extends KCBase {

	public $db;

	private $request;

	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->request = new KCRequest();
        parent::__construct();
	}

	public function index() {

		if ( ! kcCheckPermission( 'clinic_list' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     =>  $this->permission_message,
				'data'        => []
			] );
			wp_die();
		}

        $request_data = $this->request->getInputs();

		$condition = ' WHERE 0=0 ' ;
        $search_condition = ' ';

        if(!isKiviCareProActive()){
            $condition = " AND clinic.id=".kcGetDefaultClinicId();
        }
        $orderByCondition = " ORDER BY clinic.id DESC ";
        $paginationCondition = ' ';
        if((int)$request_data['perPage'] > 0){
            $perPage = (int)$request_data['perPage'];
            $offset = ((int)$request_data['page'] - 1) * $perPage;
            $paginationCondition = " LIMIT {$perPage} OFFSET {$offset} ";
        }

        if(!empty($request_data['sort'])){
            $request_data['sort'] = kcRecursiveSanitizeTextField(json_decode(stripslashes($request_data['sort'][0]),true));
            if(!empty($request_data['sort']['field']) && !empty($request_data['sort']['type']) && $request_data['sort']['type'] !== 'none'){
                $sortField = esc_sql($request_data['sort']['field']);
                $sortByValue = esc_sql(strtoupper($request_data['sort']['type']));
                switch($request_data['sort']['field']){
                    case 'id':
                    case 'name':
                    case 'email':
                    case 'telephone_no':
                    case 'status':
                        $orderByCondition= " ORDER BY clinic.{$sortField} {$sortByValue}";
                        break;
                    case 'clinic_admin_email':
                        $orderByCondition = " ORDER BY us.user_email $sortByValue} ";
                        break;
                }
            }
        }

        if(isset($request_data['searchTerm']) && trim($request_data['searchTerm']) !== ''){
            $request_data['searchTerm'] = esc_sql(strtolower(trim($request_data['searchTerm'])));
            $search_condition.= " AND (
                           clinic.id LIKE '%{$request_data['searchTerm']}%' 
                           OR clinic.name LIKE '%{$request_data['searchTerm']}%' 
                           OR clinic.email LIKE '%{$request_data['searchTerm']}%'
                           OR clinic.telephone_no LIKE '%{$request_data['searchTerm']}%'
                           OR clinic.specialties LIKE '%{$request_data['searchTerm']}%'
                           OR clinic.status LIKE '%{$request_data['searchTerm']}%'
                           OR us.user_email LIKE '%{$request_data['searchTerm']}%' 
                           OR CONCAT(clinic.address, ', ',clinic.city,', ',clinic.postal_code,', ',clinic.country) LIKE '%{$request_data['searchTerm']}%'  
                           ) ";
        }else{
            if(!empty($request_data['columnFilters'])){
                $request_data['columnFilters'] = json_decode(stripslashes($request_data['columnFilters']),true);
                foreach ($request_data['columnFilters'] as $column => $searchValue){
                    $searchValue = esc_sql(strtolower(trim($searchValue)));
                    $column = esc_sql($column);
                    if($searchValue === ''){
                        continue;
                    }
                    switch($column){
                        case 'id':
                        case 'name':
                        case 'email':
                        case 'telephone_no':
                        case 'status':
                        case 'specialties':
                            $search_condition.= " AND clinic.{$column} LIKE '%{$searchValue}%' ";
                            break;
                        case 'clinic_admin_email':
                            $search_condition.= " AND us.user_email LIKE '%{$searchValue}%' ";
                            break;
                        case 'clinic_full_address':
                            $search_condition.= " AND CONCAT(clinic.address, ', ',clinic.city,', ',clinic.postal_code,', ',clinic.country) LIKE '%{$searchValue}%' ";
                            break;
                    }
                }
            }
        }

		$clinics_query = "SELECT clinic.*,CONCAT(clinic.address, ', ',clinic.city,', '
		           ,clinic.postal_code,', ',clinic.country) AS clinic_full_address, us.user_email AS 
		               clinic_admin_email from {$this->db->prefix}kc_clinics AS clinic LEFT JOIN {$this->db->base_prefix}users 
		                   AS us ON us.ID = clinic.clinic_admin_id {$condition} {$search_condition} {$orderByCondition}  {$paginationCondition}" ;

        $total = $this->db->get_var("SELECT count(*) from {$this->db->prefix}kc_clinics AS clinic LEFT JOIN {$this->db->base_prefix}users 
		                   AS us ON us.ID = clinic.clinic_admin_id {$condition} {$search_condition} ");

		$clinics = collect($this->db->get_results($clinics_query))->map(function($x){
            $profile_img_url = wp_get_attachment_url($x->profile_image);
            $x->specialties = !empty($x->specialties) ?  collect(json_decode($x->specialties))->pluck('label')->implode(',') : '';
            $x->profile_image = !$profile_img_url ? '' : $profile_img_url;
            return $x;
        });
		if (empty($clinics)) {

			echo json_encode( [
				'status'  => false,
				'message' => esc_html__('No clinics found', 'kc-lang'),
				'data' => []
			]);
			wp_die();
		}

		echo json_encode( [
			'status'  => true,
			'message' => esc_html__('Clinic list', 'kc-lang'),
			'data' => $clinics,
            'total' => $total
		]);
	}

	public function save() {

        $is_permission = false ;

        if (kcCheckPermission( 'clinic_add' )  ) {
            $is_permission = true ;
        }

        if (!$is_permission) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => $this->permission_message,
                'data'        => []
            ] );
            wp_die();
        }

        $requestData = $this->request->getInputs();

        $rules=[
            'name' => 'required',
            'email' => 'required',
            'telephone_no' => 'required',
            'specialties' => 'required',
            'status' => 'required',
            'address' => 'required',
            'city' => 'required',
            'country' => 'required',
            'postal_code' => 'required',
            'first_name' => 'required',
            'last_name'   => 'required',
            'user_email' => 'required',
            'mobile_number' => 'required',
            'gender' => 'required',
        ];

        $errors = kcValidateRequest($rules, $requestData);

        if (count($errors)) {

            echo json_encode([
                'status' => false,
                'message' => $errors[0]
            ]);
            die;

        }

        //check clinic admin email condition
        $email_condition = kcCheckUserEmailAlreadyUsed(['user_email' => $requestData['user_email'],'ID' => !empty($requestData['clinic_admin_id']) ? $requestData['clinic_admin_id'] : '']);
        if(empty($email_condition['status'])){
            echo json_encode($email_condition);
            die;
        }

        //check clinic  email not used by other users
        $email_condition = kcCheckUserEmailAlreadyUsed(['user_email' => $requestData['email'],'ID' => !empty($requestData['clinic_admin_id']) ? $requestData['clinic_admin_id'] : ''],true);
        if(empty($email_condition['status'])){
            echo json_encode($email_condition);
            die;
        }

        $clinic_id =  !empty($requestData['id']) ? (int)$requestData['id'] : '';
        if(!empty($clinic_id) && $this->getLoginUserRole() === $this->getClinicAdminRole()){
            $clinic_id_of_admin = kcGetClinicIdOfClinicAdmin();
            if((int)$clinic_id !== $clinic_id_of_admin){
                echo json_encode( [
                    'status'      => false,
                    'status_code' => 403,
                    'message'     => $this->permission_message,
                    'data'        => []
                ] );
                wp_die();
            }
        }
        $clinic = new KCClinic;
        $clinic_email_exists = $clinic->get_var(['email' => $requestData['email']],'id');
        $clinic_admin_email_exists = $clinic->get_var(['email' => $requestData['user_email']],'id');

        if ((!empty($clinic_email_exists) && (int)$clinic_email_exists !== (int)$clinic_id) ||
            (!empty($clinic_admin_email_exists) && (int)$clinic_admin_email_exists !== (int)$clinic_id)) {
            $text = (!empty($clinic_email_exists) && (int)$clinic_email_exists !== (int)$clinic_id) ? __(" clinic " ,'kc-lang') : __(" clinic admin","kc-lang");
            echo json_encode([
                'status' => false,
                'message' =>  esc_html__('There already exists an clinic or clinic admin registered with this email address,please use other email address for ', 'kc-lang').$text
            ]);
            die;
        }

        if( isset($requestData['clinic_profile']) && !empty((int)$requestData['clinic_profile']) ){
            $requestData['clinic_profile'] = (int)$requestData['clinic_profile'];
        }
        if( isset($requestData['profile_image']) && !empty((int)$requestData['profile_image']) ){
            $requestData['profile_image'] = (int)$requestData['profile_image'];
        }

        $clinicAdminData = array(
            'first_name'=>$requestData['first_name'],
            'last_name'=>$requestData['last_name'],
            'user_email'=>$requestData['user_email'],
            'mobile_number'=>$requestData['mobile_number'],
            'gender'=>$requestData['gender'],
            'dob'=>$requestData['dob'],
            'profile_image'=>$requestData['profile_image'],
            'ID' => !empty($requestData['clinic_admin_id']) ? $requestData['clinic_admin_id'] : ''
        );

        $clinicData = array(
            'name'=>$requestData['name'],
            'email'=>$requestData['email'],
            'specialties'=> json_encode($requestData['specialties']),
            'status'=>$requestData['status'],
            'profile_image'=>$requestData['clinic_profile'],
            'telephone_no'=>$requestData['telephone_no'],
            'address'=>$requestData['address'],
            'city'=>$requestData['city'],
            'country'=>$requestData['country'],
            'postal_code'=>$requestData['postal_code'],
        );

        $currency = [
            'currency_prefix' => $requestData['currency_prefix'],
            'currency_postfix' =>$requestData['currency_postfix'],
        ];

        $clinicData['extra'] = json_encode($currency);

        if(isKiviCareProActive()){
            $response = apply_filters('kcpro_save_clinic', [
                'clinicData' =>  $clinicData,
                'clinicAdminData'=>$clinicAdminData,
                'id'=> $clinic_id
            ]);
            echo json_encode($response);
            die;
        }else{
            try {

                if (empty($clinic_id)) {
                    echo json_encode([
                        'status' => true,
                        'message' => esc_html__('Clinic id is required to update ', 'kc-lang')
                    ]);
                    die;
                }

                if(empty($clinicData['profile_image'])) {
                    unset($clinicData['profile_image']);
                }
                $clinic->update($clinicData, array( 'id' => (int)$clinic_id ));
                $requestData['clinic_admin_id'] = (int)$requestData['clinic_admin_id'];
                wp_update_user(
                    array(
                        'ID'         => $requestData['clinic_admin_id'],
                        'user_email' => $requestData['user_email'],
                        'display_name' =>  $requestData['first_name'] . ' ' . $requestData['last_name']
                    )
                );

                update_user_meta( $requestData['clinic_admin_id'], 'first_name', $requestData['first_name'] );
                update_user_meta( $requestData['clinic_admin_id'], 'last_name', $requestData['last_name'] );
                update_user_meta( $requestData['clinic_admin_id'], 'basic_data', json_encode( $clinicAdminData ) );

                if(!empty($data['clinicAdminData']['profile_image'])) {
                    update_user_meta( $data['clinicAdminData']['ID'], 'clinic_admin_profile_image', $data['clinicAdminData']['profile_image'] );
                }
                echo json_encode([
                    'status' => true,
                    'message' => esc_html__('Clinic saved successfully', 'kc-lang')
                ]);

                wp_die();
            } catch (Exception $e) {

                $code =  $e->getCode();
                $message =  $e->getMessage();

                header("Status: $code $message");

                echo json_encode([
                    'status' => false,
                    'message' => $message
                ]);
                wp_die();
            }
        }
	}

	public function edit() {

		$is_permission = false ;
        
		if (kcCheckPermission( 'clinic_edit' ) || kcCheckPermission( 'clinic_view' ) || kcCheckPermission( 'clinic_profile' ) ) {
			$is_permission = true ;
		}

		if (!$is_permission) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => $this->permission_message,
				'data'        => []
			] );
			wp_die();
		}

        $request_data = $this->request->getInputs();

        if($this->getLoginUserRole() === $this->getClinicAdminRole()){
            $clinic_id = kcGetClinicIdOfClinicAdmin();
        }else{
            if(isKiviCareProActive()){
                $clinic_id = !empty($request_data['id']) ? $request_data['id'] : kcGetDefaultClinicId();
            }else{
                $clinic_id = kcGetDefaultClinicId();
            }
        }

        if(isKiviCareProActive()) {

            $response = apply_filters('kcpro_edit_clinic', [
                'clinic_id' =>  $clinic_id,
            ]);
            echo json_encode($response);
            die;
        }else{
            try {
                $clinic = new KCClinic;
                $results = $clinic->get_by(['id' => $clinic_id], '=',true);
                if (!empty($results)) {
                    $results->specialties = !empty($results->specialties) ?  json_decode($results->specialties) : [];
                    if(!empty($results->extra)) {
                        $extra = json_decode($results->extra);
                        $results->currency_prefix = !empty($extra->currency_prefix) && $extra->currency_prefix !== 'null' ? $extra->currency_prefix : '';
                        $results->currency_postfix = !empty($extra->currency_postfix) && $extra->currency_postfix !== 'null' ? $extra->currency_postfix : '';
                    }
                    $results->clinic_profile = !empty($results->profile_image) ? wp_get_attachment_url($results->profile_image) : '';
                    $clinicAdmin = WP_User::get_data_by('ID', $results->clinic_admin_id);
                    $allUserMeta = get_user_meta( $clinicAdmin->ID);
                    $results->profile_image = !empty($allUserMeta['clinic_admin_profile_image'][0]) ? wp_get_attachment_url($allUserMeta['clinic_admin_profile_image'][0]) : '';
                    $results->first_name = !empty($allUserMetaData['first_name'][0]) ? $allUserMetaData['first_name'][0] : '';
                    $results->last_name = !empty($allUserMetaData['first_name'][0]) ? $allUserMetaData['first_name'][0] : '';
                    $basic_data = !empty($allUserMetaData['basic_data'][0]) ? $allUserMetaData['basic_data'][0] : [];
                    $results->user_email = $results->mobile_number = $results->dob = $results->gender = '';
                    if(!empty($basic_data)){
                        $basic_data = json_decode($basic_data);
                        $results->user_email = $basic_data->user_email;
                        $results->mobile_number = !empty($basic_data->mobile_number) ? $basic_data->mobile_number : '' ;
                        $results->dob = !empty($basic_data->dob) ? $basic_data->dob : '';
                        $results->gender = !empty($basic_data->gender) ? $basic_data->gender : '';
                    }
                    $results->profile_card_image = $results->profile_image;
                    echo json_encode([
                        'status' => true,
                        'message' => esc_html__('Clinic data', 'kc-lang'),
                        'data' => $results
                    ]);
                    wp_die();

                } else {
                    throw new Exception( esc_html__('Data not found', 'kc-lang'), 400);
                }


            } catch (Exception $e) {

                $code =  $e->getCode();
                $message =  $e->getMessage();

                header("Status: $code $message");

                echo json_encode([
                    'status' => false,
                    'message' => $message
                ]);
                wp_die();
            }
        }
	}

	public function delete() {

		if ( ! kcCheckPermission( 'clinic_delete' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => $this->permission_message,
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();
        $id = (int)$request_data['id'];
        if($id == kcGetDefaultClinicId()){
            echo json_encode( [
                'status'      => false,
                'message'     => __("You can't Delete default clinic","kc-lang"),
                'data'        => []
            ] );
            wp_die();
        }

		try {

			if ( ! isset( $request_data['id'] ) ) {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}
            
            if (kcGetDefaultClinicId() == $id) {
                echo json_encode( [
					'status'      => true,
					'message'     => esc_html__('You can not delete the default clinic.', 'kc-lang' ),
				] );
                die;
            }else{

                //delete all clinic related entry
                (new KCDoctorClinicMapping())->delete([ 'clinic_id' => $id]);
                (new KCReceptionistClinicMapping())->delete([ 'clinic_id' => $id]);
                (new KCPatientClinicMapping())->delete([ 'clinic_id' => $id]);
                $clinic_admin_id = $this->db->get_var("SELECT clinic_admin_id FROM {$this->db->prefix}kc_clinics WHERE id={$id}");
                if(!empty($clinic_admin_id)){
                    wp_delete_user($clinic_admin_id);
                }
                (new KCClinicSchedule())->delete(['module_id' => $id, 'module_type' => 'clinic']);
                (new KCClinicSession())->delete(['clinic_id' => $id]);
                $allAppointments = $this->db->get_results("SELECT * FROM {$this->db->prefix}kc_appointments WHERE clinic_id={$id}");
                if(!empty($allAppointments)){
                    foreach ($allAppointments as $res){
                        if($res->appointment_start_date >= current_time('Y-m-d') && in_array($res->status,['1',1])){
                            $email_data = kcCommonNotificationData($res,[],'','cancel_appointment');
                            //send cancel email
                            kcSendEmail($email_data);
                            if(kcCheckSmsOptionEnable() || kcCheckWhatsappOptionEnable()){
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
                        (new KCAppointmentServiceMapping())->delete(['appointment_id' => $res->id]);
                        (new KCCustomFieldData())->delete(['module_type' => 'appointment_module','module_id' => $res->id]);
                    }
                }
                (new KCAppointment())->delete(['doctor_id' => $id]);
                (new KCPatientEncounter())->delete(['doctor_id' => $id]);
                $results = (new KCClinic())->delete([ 'id' => $id]);
    
                if ( $results ) {
                    echo json_encode( [
                        'status'      => true,
                        'message'     => esc_html__('Clinic has been deleted successfully', 'kc-lang' ),
                    ] );
                } else {
                    throw new Exception( esc_html__('Data not found', 'kc-lang') , 400 );
                }
            }
		


		} catch ( Exception $e ) {

			$code    = $e->getCode();
			$message = $e->getMessage();

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => $message
			] );
		}
	}

    public function getUserClinic(){
        $request_data = $this->request->getInputs();
        $response = apply_filters('kcpro_get_user_clinic', [
            'requestData'=>$request_data
        ]);
        echo json_encode($response);
    }

    public function patientClinicCheckOut(){
        $request_data = $this->request->getInputs();
        if(isKiviCareProActive()){
            $response = apply_filters("kcpro_patient_clinic_checkin_checkout",$request_data);
        }else{
            $response = [
                'status' => false,
                'message' => esc_html__("Kivicare Pro is not activated",'kc-lang'),
                'notification' => []
            ];
        }
        echo json_encode($response);
        die;
    }
}
