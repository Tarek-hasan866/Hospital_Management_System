<?php

namespace App\controllers;
use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCAppointment;
use App\models\KCAppointmentServiceMapping;
use App\models\KCClinic;


class KCPatientDashboardWidget extends KCBase {

    public $db;
    private $request;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->request = new KCRequest();

        parent::__construct();
    }

    public function getPatientDetail() {
        $user_detail = wp_get_current_user();
        echo json_encode( [
            'status'  => true,
            'message' => esc_html__('Appointments', 'kc-lang'),
            'data'    => $user_detail
        ] );
    }

}


