<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use Exception;
use WP_User;
use StdClass;

class KCAuthController extends KCBase {


	public $db;

	private $request;

	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->request = new KCRequest();

        parent::__construct();
	}

    //login patient by vue appointment shortcode and patient dashboard shortcode
	public function patientLogin() {

		$parameters = $this->request->getInputs();

		try {

			$errors = kcValidateRequest( [
				'username' => 'required',
				'password' => 'required',
			], $parameters );


			if ( count( $errors ) ) {
				throw new Exception( $errors[0], 422 );
			}

            $auth_success = wp_authenticate( $parameters['username'], $parameters['password'] );

            if ( isset( $auth_success->errors ) && count( $auth_success->errors ) ) {
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html('Username/Password did not match.', 'kc-lang'),
                ] );
                wp_die();
            }

            $user_meta = get_userdata((int)$auth_success->data->ID);

            if($this->getPatientRole() !== $user_meta->roles[0] ) {
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html__( 'User not found user must be a patient.', 'kc-lang' ),
                ] );
                wp_die();
            }

            wp_set_current_user( $auth_success->data->ID, $auth_success->data->user_login );
            wp_set_auth_cookie( $auth_success->data->ID );
            do_action( 'wp_login', $auth_success->data->user_login, $auth_success );

            echo json_encode( [
				'status'  => true,
				'message' => esc_html__( 'Logged in successfully', 'kc-lang' ),
                'data'    => $auth_success
			] );

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

    //login patient by php appointment shortcode
    public function appointmentPatientLogin() {

        $parameters = $this->request->getInputs();

        try {
            if($parameters['username'] != '' && $parameters['password'] != ''){
                $errors = kcValidateRequest( [
                    'username' => 'required',
                    'password' => 'required',
                ], $parameters );


                if ( count( $errors ) ) {
                    throw new Exception( $errors[0], 422 );
                }

                $auth_success = wp_authenticate( $parameters['username'], $parameters['password'] );

                if ( isset( $auth_success->errors ) && count( $auth_success->errors ) ) {
                    echo json_encode( [
                        'status'  => false,
                        'message' => $auth_success->get_error_message(),
                    ] );
                    wp_die();
                }

                $user_meta = get_userdata($auth_success->data->ID);

                if($this->getPatientRole() !== $user_meta->roles[0] ) {
                    echo json_encode( [
                        'status'  => false,
                        'message' => esc_html__( 'User not found user must be a patient.', 'kc-lang' ),
                    ] );
                    wp_die();
                }

                wp_set_current_user( $auth_success->data->ID, $auth_success->data->user_login );
                wp_set_auth_cookie( $auth_success->data->ID );
                do_action( 'wp_login', $auth_success->data->user_login, $auth_success );

            }else{
                $auth_success = new StdClass();
                $auth_success->data->ID = get_current_user_id();
            }

            $auth_success->basic_data = get_user_meta($auth_success->data->ID, 'basic_data', true);
            $auth_success->mobile_number = !empty(json_decode($auth_success->basic_data)->mobile_number) ? json_decode($auth_success->basic_data)->mobile_number : '';
            $auth_success->first_name = get_user_meta($auth_success->data->ID, 'first_name', true);
            $auth_success->last_name = get_user_meta($auth_success->data->ID, 'last_name', true);
            $patient_data = get_userdata( $auth_success->data->ID );
            $auth_success->email = !empty($patient_data->user_email) ? $patient_data->user_email : '';
            $auth_success->display_name = !empty($patient_data->display_name) ? $patient_data->display_name : '';
            echo json_encode( [
                'status'  => true,
                'message' => esc_html__( 'Logged in successfully', 'kc-lang' ),
                'data'    => $auth_success
            ] );

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

    //registration patient by appointment shortcode or patient dashboard shortcode
	public function patientRegister() {

		$parameters = $this->request->getInputs();


        if(!empty($parameters['widgettype']) && $parameters['widgettype'] === 'new_appointment_widget'){
            $recaptcha = $this->googleRecaptchaVerify($parameters);
            if(empty($recaptcha['status'])){
                echo json_encode( $recaptcha );
                wp_die();
            }
        }

        if(email_exists($parameters['user_email'])) {
            echo json_encode([
                'status'  => false,
                'message' => esc_html__( "Email already exists. Please use a different email." , 'kc-lang' )
            ]);
            wp_die();
        }

		try {

            $temp = [
                'mobile_number' => !empty($parameters['mobile_number']) ? $parameters['mobile_number'] : '',
                'dob'           => !empty($parameters['dob']) ? $parameters['dob'] : '',
                'address'       => !empty($parameters['address']) ? $parameters['address'] : '' ,
                'city'          => !empty($parameters['city']) ? $parameters['city'] : '' ,
                'state'         => '',
                'country'       => !empty($parameters['country']) ? $parameters['country'] : '' ,
                'postal_code'   => !empty($parameters['postal_code']) ? $parameters['postal_code'] : '',
            ];

			$username = kcGenerateUsername($parameters['first_name']);

			$password = kcGenerateString(12);

			$user = wp_create_user( $username, $password, sanitize_email($parameters['user_email'] ) );

			$u               = new WP_User( $user );

			$u->display_name = $parameters['first_name'] . ' ' .$parameters['last_name'];

			wp_insert_user( $u );

			$u->set_role( $this->getPatientRole() );

			update_user_meta( $user, 'basic_data', json_encode( $temp ) );

            update_user_meta($user, 'first_name',$parameters['first_name']);
            update_user_meta($user, 'last_name',$parameters['last_name']) ;

            $patient_clinic_map_temp = [
                'patient_id' => $u->ID,
                'created_at' => current_time('Y-m-d H:i:s')
            ];
            if(isKiviCareProActive() && !empty($parameters['clinic'][0]['id'])){
                $patient_clinic_map_temp['clinic_id'] = (int)$parameters['clinic'][0]['id'];
            }else{
                $patient_clinic_map_temp['clinic_id'] = kcGetDefaultClinicId();
            }
            $this->db->insert($this->db->prefix.'kc_patient_clinic_mappings',$patient_clinic_map_temp);
            if(kcPatientUniqueIdEnable('status')){
                update_user_meta( $user, 'patient_unique_id',generatePatientUniqueIdRegister());
            }

            if (isset($parameters['custom_fields']) && !empty($parameters['custom_fields'])) {
                kvSaveCustomFields('patient_module', $user, $parameters['custom_fields']);
            }

            $auth_success = '';
            if ( $user ) {
                // hook for patient save
                do_action( 'kc_patient_save', $u->ID );

                $auth_success = wp_authenticate( $u->user_email, $u->user_pass );
                wp_set_current_user( $u->ID, $u->user_login );
                wp_set_auth_cookie(  $u->ID );
                do_action( 'wp_login',$u->user_login, $u );

                $user_email_param = kcCommonNotificationUserData($u->ID,$password);
                kcSendEmail($user_email_param);
                if(kcCheckSmsOptionEnable()){
                    $sms = apply_filters('kcpro_send_sms', [
                        'type' => 'patient_register',
                        'user_data' => $user_email_param,
                    ]);
                }
            }

			if($user) {
				$status = true ;
				$message = esc_html__( "Patient registration successful. Check your email for login credentials." , 'kc-lang' );
			} else {
				$status = false ;
				$message = esc_html__( "Patient registration failed. Please try again." , 'kc-lang' );
			}

			echo json_encode([
				'status'  => $status,
				'message' => $message,
                'data'    => $auth_success
			]);


		} catch ( Exception $e ) {

			$code    = $e->getCode();
			$message =  $e->getMessage();

			header( "Status: $code $message" );

			echo json_encode( [
				'status'  => false,
				'message' => $message
			] );
		}
	}

    public function logout()
    {

        wp_logout();
        echo json_encode([
            'status' => true,
            'message' => esc_html__('Logout successful.', 'kc-lang'),
        ]);
    }

    //user login and redirect to dashboard by login/register shortcode
    public function loginNewUser() {

        $parameters = $this->request->getInputs();
        try {

            $errors = kcValidateRequest( [
                'username' => 'required',
                'password' => 'required',
            ], $parameters );


            if ( count( $errors ) ) {
                echo json_encode( [
                    'status'  => false,
                    'message' => $errors[0],
                ] );
                wp_die();
            }

            $auth_success = wp_authenticate( $parameters['username'], $parameters['password'] );

            if ( isset( $auth_success->errors ) && count( $auth_success->errors ) ) {
                echo json_encode( [
                    'status'  => false,
                    'message' => $auth_success->get_error_message(),
                ] );
                wp_die();
            }

            $user_meta = get_userdata((int)$auth_success->data->ID);

            if (!in_array($user_meta->roles[0], [
                $this->getPatientRole(),
                $this->getDoctorRole(),
                $this->getReceptionistRole(),
                $this->getClinicAdminRole()
            ] ) ) {
                echo json_encode( [
                    'status'  => false,
                    'message' => esc_html__( 'Login user is not kivicare user', 'kc-lang' ),
                ] );
                wp_die();
            }

            wp_set_current_user( $auth_success->data->ID, $auth_success->data->user_login );
            wp_set_auth_cookie( $auth_success->data->ID );
            do_action( 'wp_login', $auth_success->data->user_login, $auth_success );

            echo json_encode( [
                'status'  => true,
                'message' => esc_html__( 'Logged in successfully', 'kc-lang' ),
                'data'    => $auth_success
            ] );

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

    //user registration by login/register shortcode
    public function registerNewUser(){
        $parameters = $this->request->getInputs();

        $commonConditionField = [
            'first_name' => 'required',
            'last_name' => 'required',
            'user_email' => 'required|email',
            'mobile_number' => 'required',
            'user_role' => 'required',
            'user_clinic' => 'required'
        ];

        if(kcGoogleCaptchaData('status') === 'on'){
            $commonConditionField['g-recaptcha-response'] = 'required';
        }

        $errors = kcValidateRequest($commonConditionField, $parameters );


        if ( count( $errors ) ) {
            echo json_encode( [
                'status'  => false,
                'message' => $errors[0],
            ] );
            wp_die();
        }

        $recaptcha = $this->googleRecaptchaVerify($parameters);
        if(empty($recaptcha['status'])){
            echo json_encode( $recaptcha );
            wp_die();
        }

        if(email_exists($parameters['user_email'])) {
            echo json_encode([
                'status'  => false,
                'message' => esc_html__( "Email already exists. Please use a different email." , 'kc-lang' )
            ]);
            wp_die();
        }

        $redirect = '';
        $emailstatus = false;
        $sms = '';
        $user_table = $this->db->base_prefix.'users';
        try {
            $temp = [ 'mobile_number'  => $parameters['mobile_number'] ];

            $username = kcGenerateUsername($parameters['first_name']);

            $password = kcGenerateString(12);

            $user = wp_create_user( $username, $password, sanitize_email($parameters['user_email'] ) );

            $u               = new WP_User( $user );

            $u->display_name = $parameters['first_name'] . ' ' .$parameters['last_name'];

            wp_insert_user( $u );

            update_user_meta( $user, 'basic_data', json_encode( $temp ) );
            update_user_meta($user, 'first_name',$parameters['first_name']);
            update_user_meta($user, 'last_name',$parameters['last_name']) ;

            $auth_success = '';
            $user_email_param = [
                'id' => $u->ID,
                'username' => $username,
                'user_email' => $parameters['user_email'],
                'password' => $password,
            ];
            switch ($parameters['user_role']){
                case $this->getPatientRole():
                    if(kcPatientUniqueIdEnable('status')){
                        update_user_meta( $user, 'patient_unique_id',generatePatientUniqueIdRegister());
                    }
                    if (isset($parameters['custom_fields']) && $parameters['custom_fields'] !== []) {
                        kvSaveCustomFields('patient_module', $user, $parameters['custom_fields']);
                    }
                    $u->set_role( $this->getPatientRole() );
                    do_action( 'kc_patient_save', $u->ID );
                    $new_temp = [
                        'patient_id' => $u->ID,
                        'clinic_id' => (int)$parameters['user_clinic'],
                        'created_at' => current_time('Y-m-d H:i:s')
                    ];
                    $this->db->insert($this->db->prefix.'kc_patient_clinic_mappings',$new_temp);
                    $user_email_param['patient_name'] = $parameters['first_name'] . ' ' .$parameters['last_name'];
                    $templateType = 'patient_register';
                    if(kcGetUserRegistrationShortcodeSetting('patient') !== 'on'){
                        $this->db->update($user_table,['user_status' => 1],['ID' => $u->ID]);
                    }
                    if ( $user ) {
                        if(!is_user_logged_in()){
                            $redirect = admin_url('admin.php?page=dashboard');
                            $auth_success = wp_authenticate( $u->user_email, $u->user_pass );
                            wp_set_current_user( $u->ID, $u->user_login );
                            wp_set_auth_cookie(  $u->ID );
                            do_action( 'wp_login',$u->user_login, $u );
                        }
                    }
                    break;
                case $this->getDoctorRole():
                    if($this->getLoginUserRole() !== 'administrator' && kcGetUserRegistrationShortcodeSetting('doctor') !== 'on'){
                        update_user_meta((int)$u->ID,'kivicare_user_account_status','no');
                        $this->db->update($user_table,['user_status' => 1],['ID' => (int)$u->ID]);
                    }
                    $u->set_role( $this->getDoctorRole());
                    $this->db->insert($this->db->prefix.'kc_doctor_clinic_mappings',[
                        'doctor_id' => (int)$u->ID,
                        'clinic_id' => (int)$parameters['user_clinic'],
                        'owner' => 0,
                        'created_at' => current_time('Y-m-d H:i:s')
                    ]);
                    if (isset($parameters['custom_fields']) && $parameters['custom_fields'] !== []) {
                        kvSaveCustomFields('patient_module', $user, $parameters['custom_fields']);
                    }
                    do_action( 'kc_doctor_save', $u->ID );
                    $user_email_param['doctor_name'] = $parameters['first_name'] . ' ' .$parameters['last_name'];
                    $templateType = 'doctor_registration';
                    break;
                case $this->getReceptionistRole():
                    if($this->getLoginUserRole() !== 'administrator' && kcGetUserRegistrationShortcodeSetting('receptionist') !== 'on'){
                        update_user_meta((int)$u->ID,'kivicare_user_account_status','no');
                        $this->db->update($user_table,['user_status' => 1],['ID' => (int)$u->ID]);
                    }
                    $u->set_role($this->getReceptionistRole() );
                    $user_email_param['Receptionist_name'] = $parameters['first_name'] . ' ' .$parameters['last_name'];
                    $templateType = 'receptionist_register';
                    $this->db->insert($this->db->prefix.'kc_receptionist_clinic_mappings',[
                        'receptionist_id' => (int)$u->ID,
                        'clinic_id'       => (int)$parameters['user_clinic'],
                        'created_at'      =>   current_datetime('Y-m-d H:i:s' )
                    ]);
                    do_action( 'kc_receptionist_save', $u->ID );
                    break;
            }

            if(!empty($templateType) && !empty($user_email_param)){
                $user_email_param['email_template_type'] = $templateType;
                $emailstatus = kcSendEmail($user_email_param);
                if(kcCheckSmsOptionEnable()){
                    $sms = apply_filters('kcpro_send_sms', [
                        'type' => $templateType,
                        'user_data' => $user_email_param,
                    ]);
                }
            }

            $adminemailstatus = false;
            $adminsms = [];

            if($user) {

                if($this->getLoginUserRole() !== 'administrator'){
                    $admin_email_param = [
                        'user_contact' => $parameters['mobile_number'],
                        'user_role' => $parameters['user_role'],
                        'username' => $parameters['first_name'] . ' ' .$parameters['last_name'],
                        'user_email' => $parameters['user_email'],
                        'email_template_type' => 'admin_new_user_register'
                    ];
                    $adminemailstatus = kcSendEmail($admin_email_param);
                    if(kcCheckSmsOptionEnable()){
                        $adminsms = apply_filters('kcpro_send_sms', [
                            'type' => 'admin_new_user_register',
                            'user_data' => $admin_email_param,
                        ]);
                    }
                }
                $status = true ;
                $message = esc_html__( "User registration successfully. Check your email for login credentials." , 'kc-lang' );
            } else {
                $status = false ;
                $message = esc_html__( "User registration not success." , 'kc-lang' );
            }


            echo json_encode([
                'status'  => $status,
                'message' => $message,
                'data'    => $auth_success,
                'redirect' => $redirect,
                'notification' => [
                    'sms' => $sms,
                    'email' => $emailstatus
                ],
                'adminNotification' =>[
                    'sms' => $adminsms,
                    'email' => $adminemailstatus
                ]
            ]);

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

    //verify doctor and receptionist by admin and send notification to verify user
    public function verifyUser(){
        $parameters = $this->request->getInputs();

        if(empty($parameters['data']['ID'])){
            echo json_encode( [
                'status'  => false,
                'message' => __('User id not found','kc-lang')
            ] );
            die;
        }

        $id = (int)$parameters['data']['ID'];
        update_user_meta($id,'kivicare_user_account_status','yes');
        $this->db->update($this->db->base_prefix.'users',['user_status' => 0],['ID' => $id]);

        $user_email_param = [
            'user_contact' => $parameters['data']['mobile_number'],
            'user_email' => $parameters['data']['user_email'],
            'email_template_type' => 'user_verified'
        ];
        $sms = [];
        $emailstatus = kcSendEmail($user_email_param);
        if(kcCheckSmsOptionEnable()){
            $sms = apply_filters('kcpro_send_sms', [
                'type' => 'user_verified',
                'user_data' => $user_email_param,
            ]);
        }
        echo json_encode( [
            'status'  => true,
            'message' => __('User Verified Successfully','kc-lang'),
            'notification' => [
                'sms' => $sms,
                "email" => $emailstatus
            ]
        ] );
        die;
    }

    public function googleRecaptchaVerify($parameters){
        if(kcGoogleCaptchaData('status') === 'on'){
            $recaptchaSecret   = kcGoogleCaptchaData('secret_key');
            $response = file_get_contents(
                "https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptchaSecret . "&response=" . $parameters['g-recaptcha-response'] . "&remoteip=" . $_SERVER['REMOTE_ADDR']
            );
            $response = json_decode($response);
            if ($response->success === false || (!empty($response->score) && $response->score < 0.5)) {
                return [
                    'status'  => false,
                    'message' => __('Invalid Google recaptcha Value'),
                    'data' => $response
                ];
            }
        }

        return [
            'status'  => true,
        ];
    }
}
