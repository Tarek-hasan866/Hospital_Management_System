<?php

namespace App\controllers;

use App\baseClasses\KCBase;
use App\baseClasses\KCRequest;
use App\models\KCAppointment;
use App\models\KCAppointmentServiceMapping;
use Omnipay\Omnipay;
use Exception;
class KCPaymentController extends KCBase
{
    /**
     * @var KCRequest
     */
    private $request;

    public $db;

    public $returnUrl;

    public $cancelUrl;

    public function __construct()
    {
        $this->request = new KCRequest();

        $this->returnUrl = get_site_url().'?kivicare_payment=success&appointment_id=';
        $this->cancelUrl = get_site_url().'?kivicare_payment=failed&appointment_id=';

        global $wpdb;

        $this->db = $wpdb;

        parent::__construct();
    }

    public function changeWooCommercePaymentStatus () {
        if ( $this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }
        $data = $this->request->getInputs();
        $status = false;
        $message = esc_html__('Woocommerce status can\'t change.', 'kc-lang');

        if(kcLocalPaymentGatewayEnable() == 'off'
            && empty(kcPaypalSettingData('enablePaypal'))
            && !(apply_filters('kivicare_razorpay_enable',false)) && $data['status'] == 'off'){
            echo json_encode([
                'status'  => false,
                'message' => esc_html__('Atleast One Payment Gateway should by enable', 'kc-lang'),
            ]);
            die;
        }

        if(!iskcWooCommerceActive()){
            echo json_encode([
                'status'  => false,
                'message' => esc_html__('Woocommerce Plugin Is Not Active', 'kc-lang'),
            ]);
            die;
        }

        if(isKiviCareTelemedActive()){
             apply_filters('kct_change_woocommerce_module_status', [
                'status' => $data['status']
            ]);
            $status = true;
            $message = esc_html__('Woocommerce change status.', 'kc-lang');
        }elseif (isKiviCareGoogleMeetActive()){
             apply_filters('kcgm_change_woocommerce_module_status', [
                'status' => $data['status']
            ]);
            $status = true;
            $message = esc_html__('Woocommerce change status.', 'kc-lang');
        }
        elseif(isKiviCareProActive()){
             apply_filters('kcpro_change_woocommerce_module_status', [
                'status' => $data['status']
            ]);
            $status = true;
            $message = esc_html__('Woocommerce change status.', 'kc-lang');
        }

        echo json_encode([
            'status'  => $status,
            'message' => $message
        ]);
    }

    public function savePaypalConfig(){

        if ( $this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }

        $request_data = $this->request->getInputs();
        $request_data['data'] = stripslashes($request_data['data']);

        if(!empty($request_data['data'])){
            if(kcLocalPaymentGatewayEnable() == 'off'
                && kcWoocommercePaymentGatewayEnable() === 'off'
                && !(apply_filters('kivicare_razorpay_enable',false))
                && empty($request_data['data']['enablePaypal']) ){
                $response = [
                    'status' => false,
                    'message' => esc_html__('Atleast One Payment Gateway should by enable', 'kc-lang'),
                ];
            }else{
                update_option(KIVI_CARE_PREFIX.'paypalConfig', $request_data['data']);
                $response = [
                    'status' => true,
                    'message' => esc_html__('Paypal Setting Saved Successfully', 'kc-lang'),
                ];
            }
        }else{
            $response = [
                'status' => false,
                'message' => esc_html__('Paypal Setting Update Failed', 'kc-lang'),
            ];
        }

        echo json_encode($response);
    }

    public function saveRazorpayConfig(){
        if ( $this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }

        $request_data = $this->request->getInputs();
        
        $request_data = $request_data['data'];

        $rules = [
			'enable'    => 'required',
			'api_key'    => 'required',
			'secret_key' => 'required',
			'currency'   => 'required',
		];

		$errors = kcValidateRequest( $rules, $request_data );

		if ( count( $errors ) ) {
			echo json_encode( [
				'status'  => false,
				'message' => $errors[0]
			] );
			die;
		}

        $response = apply_filters('kivicare_save_razorpay_configurations',$request_data);
        if(is_array($response)){
            echo json_encode($response);
        }else{
            echo json_encode([
                'status' => false,
                'message' => esc_html__('Failed to saved razorpay configurations','kc-lang')
            ]);
        }

        wp_die();

    }

    public function changeLocalPaymentStatus(){
        if ( $this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }
        $data = $this->request->getInputs();
        $status = false;

        if(empty(kcPaypalSettingData('enablePaypal')) 
           && kcWoocommercePaymentGatewayEnable() === 'off' 
           && !(apply_filters('kivicare_razorpay_enable',false))
            && $data['status'] === 'off'){
            $message = esc_html__('Atleast One Payment Gateway should by enable', 'kc-lang');
        }else{
            update_option(KIVI_CARE_PREFIX.'local_payment_status', $data['status']);
            $status = true;
            $message = esc_html__('Local Payment Setting Saved Successfully', 'kc-lang');
        }

        echo json_encode([
            'status' => $status,
            'message' => $message,
        ]);
        die;
    }
    public function getPaymentStatusAll(){

        if ( $this->getLoginUserRole() !== 'administrator' ) {
            echo json_encode( [
                'status'      => false,
                'status_code' => 403,
                'message'     => esc_html__('You don\'t have permission to access', 'kc-lang'),
                'data'        => []
            ] );
            wp_die();
        }

        //paypal payment setting
        $paypalSetting = kcPaypalSettingData('all');

        echo json_encode([
            'status'  => true,
            'data' => kcWoocommercePaymentGatewayEnable(),
            'paypal' => !empty($paypalSetting) ? $paypalSetting : 'off',
            'local_payment' => kcLocalPaymentGatewayEnable(),
            'razorpay' => apply_filters('kivicare_get_razorpay_configurations',[]),
            'message' => esc_html__('Woocommerce status.', 'kc-lang'),
        ]);
    }

    public function makePaypalPayment($request_data,$appointment_id){
        if(empty(kcPaypalSettingData('mode')) || empty(kcPaypalSettingData('client_id'))
        || empty(kcPaypalSettingData('client_secret')) || empty(kcPaypalSettingData('currency'))
        || empty(kcPaypalSettingData('enablePaypal'))){
            return[
                'status' => false,
                'message' => __('Paypal Configuration is not proper')
            ];
        }
        $serviceItems = [];
        $totalPrice = 0;
        $currency = kcPaypalSettingData('currency');
        $currency = $currency->id;
        $request_data['id'] = (int)$appointment_id;
        foreach ($request_data['visit_type'] as $service){
            $tempService = [];
            $price = $this->db->get_var("SELECT charges FROM {$this->db->prefix}kc_service_doctor_mapping WHERE doctor_id =".(int)$request_data['doctor_id']['id']." AND service_id =".(int)$service['service_id']);;
            if(empty($price)){
               $price = 0 ;
            }
            $tempService['price'] = $price;
            $totalPrice += $price;
            $tempService['quantity'] = 1;
            $tempService['name'] = $service['name'];
            $serviceItems[] = $tempService;
        }
        try {
            $gateway = $this->configPaypalApi();
            $response = $gateway->purchase(array(
                'amount' => $totalPrice,
                'currency' => $currency,
                'returnUrl' => $this->returnUrl.$request_data['id'],
                'cancelUrl' => $this->cancelUrl.$request_data['id'],
                'transactionId' => $request_data['id'],
            ))->setItems($serviceItems)->send();
            if ($response->isRedirect()) {
                $this->db->delete($this->db->prefix."kc_payments_appointment_mappings",['appointment_id' => $request_data['id']]);
                $this->db->insert($this->db->prefix."kc_payments_appointment_mappings",[
                        'appointment_id' =>$request_data['id'],
                    'payment_mode' => 'paypal_rest',
                    'amount' => (int)$totalPrice,
                    'request_page_url' => sanitize_text_field(wp_unslash(wp_get_referer())),
                    'currency' => $currency
                ]);
                return[
                    'woocommerce_cart_data' => [ 'woocommerce_redirect' => $response->getRedirectUrl() ],
                    'redirect' => $response->getRedirectUrl(),
                    'status' => true
                ];
            } else {
                return[
                    'status' => false,
                    'message' => $response->getMessage()
                ];
            }
        } catch(Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function paymentSuccess(){
        $payment_status = 'failed';
        $request_data = $this->request->getInputs();
        $appointment_id = $request_data['appointment_id'];
        if (array_key_exists('paymentId', $request_data) && array_key_exists('PayerID', $request_data)) {
            $gateway = $this->configPaypalApi();
            $transaction = $gateway->completePurchase(array(
                'payer_id'             => $request_data['PayerID'],
                'transactionReference' => $request_data['paymentId'],
            ));
            $response = $transaction->send();
            if ($response->isSuccessful()) {
                // The customer has successfully paid.
                $arr_body = $response->getData();
                $payment_id = $arr_body['id'];
                $payer_id = $arr_body['payer']['payer_info']['payer_id'];
                $payer_email = $arr_body['payer']['payer_info']['email'];
                $payment_status = $arr_body['state'];
                $tempData = [
                    'payment_id' => esc_sql($payment_id),
                    'payer_id' => esc_sql($payer_id),
                    'payer_email' => esc_sql($payer_email),
                    'payment_status' => esc_sql($payment_status)
                ];
                $this->db->update($this->db->prefix."kc_payments_appointment_mappings",$tempData,['appointment_id' => $appointment_id]);
                $this->db->update($this->db->prefix."kc_appointments",['status' => 1],['id' => $appointment_id]);
                if(empty($this->db->get_var("SELECT notification_status FROM {$this->db->prefix}kc_payments_appointment_mappings WHERE appointment_id ={$appointment_id}"))){
                    kivicareWoocommercePaymentComplete($appointment_id,'paypal');
                }
            }
        }
        ?>
        <script>
            if(window.opener.document.getElementById('payment_status_child') !== null){
                window.opener.document.getElementById('payment_status_child').value = '<?php echo esc_html(trim($payment_status));?>';
            }
            window.onunload = function (e) {
                opener.kivicareCheckPaymentStatus('<?php echo esc_html(trim($payment_status));?>','<?php echo esc_html($appointment_id);?>');
            };
            window.close();
        </script>
        <?php
    }

    public function paymentFailedPage(){
        $appointment_id = 0;
        $request_data = $this->request->getInputs();
        if(array_key_exists('appointment_id',$request_data) && !empty($request_data['appointment_id'])) {
            $appointment_id = (int)$request_data['appointment_id'];
            $this->db->delete($this->db->prefix."kc_payments_appointment_mappings",['appointment_id' => $appointment_id]);
            (new KCAppointment())->delete(['id' => $appointment_id]);
            (new KCAppointmentServiceMapping())->delete(['appointment_id' => $appointment_id]);
        }
        ?>
        <script>
           if(window.opener.document.getElementById('payment_status_child') !== null){
               window.opener.document.getElementById('payment_status_child').value = 'failed';
           }
            window.onunload = function (e) {
                opener.kivicareCheckPaymentStatus('failed','<?php echo esc_html($appointment_id);?>');
            };
            window.close();
        </script>
        <?php
    }

    public function configPaypalApi(){
        $gateway = Omnipay::create('PayPal_Rest');
        $gateway->setClientId(kcPaypalSettingData('client_id'));
        $gateway->setSecret(kcPaypalSettingData('client_secret'));
        $mode = kcPaypalSettingData('mode');
        $developerMode = !empty($mode) && !empty(kcPaypalSettingData('mode')->label) && kcPaypalSettingData('mode')->label == 'Sandbox';
        if($developerMode){
            if (method_exists($gateway, 'setDeveloperMode')) {
                $gateway->setDeveloperMode(true);
            } else {
                $gateway->setTestMode(true);
            }
        }else{
            $gateway->setTestMode(false);
        }

        return $gateway;
    }

    public function getAppointmentPaymentStatus(){
        $request_data = $this->request->getInputs();
        $request_data['id'] = (int)$request_data['id'];
        $payment_status = $this->db->get_var("SELECT payment_status FROM {$this->db->prefix}kc_payments_appointment_mappings WHERE appointment_id ={$request_data['id']}");
        if(empty($payment_status)){
            $status = 'failed';
        }else{
            $status = $payment_status;
        }

        echo json_encode([
            'data' => $status,
            'status' => true
        ]);
        die;
    }
}