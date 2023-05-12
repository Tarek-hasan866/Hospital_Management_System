<?php

namespace App\Controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCMedicalHistory;
use App\models\KCPatientEncounter;
use Exception;

class KCPatientMedicalHistoryController extends KCBase {

	public $db;

	/**
	 * @var KCRequest
	 */
	private $request;

	private $medical_history;

	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->request = new KCRequest();

		$this->medical_history = new KCMedicalHistory();

        parent::__construct();

	}

	public function index() {

		if ( ! kcCheckPermission( 'medical_records_list' ) ) {
			echo json_encode( [
				'status'      => false,
				'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();

		if ( empty( $request_data['encounter_id'] ) ) {
			echo json_encode( [
				'status'  => false,
				'message' =>  esc_html__('Encounter not found', 'kc-lang'),
				'data'    => []
			] );
			wp_die();
		}

        if(!((new KCPatientEncounter())->encounterPermissionUserWise($request_data['encounter_id']))){
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }
		$encounter_id = (int)$request_data['encounter_id'];

		$medical_history = collect( $this->medical_history->get_by( [
			'encounter_id' => $encounter_id,
		] ) );

		if ( ! count( $medical_history ) ) {
			echo json_encode( [
				'status'  => false,
				'message' =>  esc_html__('No medical history found', 'kc-lang'),
				'data'    => []
			] );
			wp_die();
		}

        $medical_history = $medical_history->groupBy('type');

        $medical_history = apply_filters('kivicare_encounter_clinical_details_items',$medical_history,$encounter_id);

        echo json_encode( [
			'status'  => true,
			'message' =>  esc_html__('Medical history', 'kc-lang'),
			'data'    => $medical_history,
		] );
	}

	public function save() {

		if ( ! kcCheckPermission( 'medical_records_add' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();

		$rules = [
			'encounter_id' => 'required',
			'type'         => 'required',
			'title'        => 'required',
		];

		$errors = kcValidateRequest( $rules, $request_data );

		if ( count( $errors ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => $errors[0]
			] );
			die;
		}

        if(!((new KCPatientEncounter())->encounterPermissionUserWise($request_data['encounter_id']))){
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }
		$patient_encounter = ( new KCPatientEncounter )->get_by( [ 'id' => (int)$request_data['encounter_id'] ], '=', true );
		$patient_id        = $patient_encounter->patient_id;

		if ( empty( $patient_encounter ) ) {
			echo json_encode( [
				'status'  => false,
				'message' =>  esc_html__("No encounter found", 'kc-lang')
			] );
			die;
		}

		$temp = [
			'encounter_id' => (int)$request_data['encounter_id'],
			'patient_id'   => (int)$patient_id,
			'type'         => $request_data['type'],
			'title'        => $request_data['title'],
		];

		if ( ! isset( $request_data['id'] ) ) {

			$temp['created_at'] = current_time( 'Y-m-d H:i:s' );
			$temp['added_by']   = get_current_user_id();
			$id                 = $this->medical_history->insert( $temp );

		} else {
			$id     = $request_data['id'];
			$this->medical_history->update( $temp, array( 'id' => (int)$request_data['id'] ) );
		}

		$data = $this->medical_history->get_by( [ 'id' => (int)$id ], '=', true );


		echo json_encode( [
			'status'  => true,
			'message' =>  esc_html__('Medical history saved successfully', 'kc-lang'),
			'data'    => $data
		] );

	}

	public function delete() {


		if ( ! kcCheckPermission( 'medical_records_delete' ) ) {
			echo json_encode( [
				'status'      => false,
				'status_code' => 403,
				'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
				'data'        => []
			] );
			wp_die();
		}

		$request_data = $this->request->getInputs();

		try {

			if ( ! isset( $request_data['id'] ) ) {
				throw new Exception( esc_html__('Data not found', 'kc-lang'), 400 );
			}

			$id = (int)$request_data['id'];
            $medical_history_encounter_id = (new KCMedicalHistory())->get_var(['id' =>$id],'encounter_id');
            if(!empty($medical_history_encounter_id) && !((new KCPatientEncounter())->encounterPermissionUserWise($medical_history_encounter_id))){
                echo json_encode( [
                    'status'      => false,
                    'status_code' => 403,
                    'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                    'data'        => []
                ] );
                wp_die();
            }
			$results = $this->medical_history->delete( [ 'id' => $id ] );

			if ( $results ) {
				echo json_encode( [
					'status'  => true,
					'message' =>  esc_html__('Medical history deleted successfully', 'kc-lang'),
				] );
			} else {
				throw new Exception( esc_html__('Failed to delete Medical history.', 'kc-lang'), 400 );
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
}