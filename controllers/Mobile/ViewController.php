<?php

/**
 * Class Enterprisepayment_Mobile_ViewController
 */


require_once path("app/local/modules/Enterprisepayment/controllers/Api/stripe-php/init.php");


/**
 * Class Enterprisepayment_Mobile_ViewController
 */
class Enterprisepayment_Mobile_ViewController extends Application_Controller_Mobile_Default
{

    /**
     *
     */
    const SET_EXPRESS_CHECKOUT = 'SetExpressCheckout';
    /**
     * @var string
     */
    private $__pay_url = "";

    /*Get all payments from db*/
    /**
     *
     */
    public function getmethodsAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {

                /*get currency name and symbol*/
                $currency_name = Core_Model_Language::getCurrentCurrency()->getShortName();
                $currency = Core_Model_Language::getCurrentCurrency()->getSymbol();

                $gateway = new Enterprisepayment_Model_Gatewaydetail();
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                $gatewaydata = $gateway->getAllMethods($data['value']);
                $base_url = $this->getRequest()->getBaseUrl();
                $data = [
                    'success' => '1',
                    'methods' => $gatewaydata,
                    'currency' => $currency,
                    'basepath' => $this->getRequest()->getBaseUrl(),
                ];
            } catch (Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => __($e->getMessage()),
                ];
            }
            $this->_sendHtml($data);
        }
    }

    /*Paypal payment process*/
    /**
     *
     */
    public function paypalpaymentAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                $detailmodel = new Enterprisepayment_Model_Gatewaydetail();
                $paypalCredentials = $detailmodel->find(['gid' => $data['gid'], 'value_id' => $data['value_id']]);
                if ($paypalCredentials->getData()) {
                    $currency = Core_Model_Language::getCurrentCurrency()->getShortName();
                    $base_url = $this->getRequest()->getBaseUrl();

                    if ($data['is_webview']) {
                        $return_url = $base_url . '/var/apps/browser/index-prod.html#' . $data['BASE_PATH'] . '/enterprisepayment/mobile_list/index/value_id/' . $data['value_id'];
                        $cancel_url = $base_url . '/var/apps/browser/index-prod.html#' . $data['BASE_PATH'] . '/enterprisepayment/mobile_list/index/value_id/' . $data['value_id'];
                    } else {
                        $return_url = $base_url . '/var/apps/browser/index-prod.html#' . $data['current_url'] . 'confirm';
                        $cancel_url = $base_url . '/var/apps/browser/index-prod.html#' . $data['current_url'] . 'cancel';
                    }

                    $params = [
                        'RETURNURL' => trim($return_url),
                        'CANCELURL' => trim($cancel_url),
                        'PAYMENTREQUEST_0_CURRENCYCODE' => $currency,
                        'PAYMENTREQUEST_0_AMT' => $data['amount'],
                    ];

                    $paypal_payment_mode = $paypalCredentials->getPaymentMode();
                    if ($paypalCredentials->getPaymentMode() == 0) {
                        $testCredentials = json_decode($paypalCredentials->getTestData());
                        $paypal_api_user = $testCredentials->sandboxusername;
                        $paypal_api_user_pwd = $testCredentials->sandboxpassword;
                        $paypal_api_user_signature = $testCredentials->sandboxsignature;
                    } else {
                        $liveCredentials = json_decode($paypalCredentials->getLiveData());
                        $paypal_api_user = $liveCredentials->username;
                        $paypal_api_user_pwd = $liveCredentials->password;
                        $paypal_api_user_signature = $liveCredentials->signature;
                    }

                    $response = $this->request(self::SET_EXPRESS_CHECKOUT, $params, $paypalCredentials->getPaymentMode(), $paypal_api_user, $paypal_api_user_pwd, $paypal_api_user_signature);

                    $detailmodel = new Enterprisepayment_Model_Setting();
                    $stripeCredentials = $detailmodel->find(['value_id' => $data['value_id'], 'return_value_id' => $data['return_value_id']]);
                    $return_url = $stripeCredentials->getReturnLink();
                    $return_state = $stripeCredentials->getReturnState();
                    $return_value_id = $stripeCredentials->getReturnValueId();

                    $html = ['success' => 1, 'token_url' => $this->__pay_url . '&webview=1', 'gid' => $paypalCredentials->getGid(), 'return_url' => $return_url, 'return_state' => $return_state, 'return_value_id' => $return_value_id];

                } else {
                    $html = ['success' => 0, 'message' => __("Don't have Paypal Credential!")];
                }

            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        } else {
            $html = ['error' => 1, 'message' => __('An error occurred during process. Please try again later.')];
        }

        $this->_sendHtml($html);
    }

    /*Save paypal payment in transaction table*/
    /**
     *
     */
    public function transactionAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                $rData = ['token' => $data['token'], 'payer' => $data['payer']];
                $jsonRdata = json_encode($rData);
                $application_id = $this->getApplication()->getId();
                $transactionmodel = new Enterprisepayment_Model_Transaction();

                /*Amount with proper format*/
                $payment_format = strlen(substr(strrchr($data['amount'], "."), 1));
                $amount_pay = $data['amount'];
                if ($payment_format == 1) {
                    $amount_paid = $amount_pay . '0';
                } else if ($payment_format == 0) {
                    $amount_paid = $amount_pay . '.00';
                } else {
                    $amount_paid = $amount_pay;
                }
                $transaction_date = date("Y-m-d H:i:s");
                $transaction = $transactionmodel->setValueId($data['value'])
                    ->setAppId($application_id)
                    ->setCustomerId($data['customer_id'])
                    ->setType($data['gid'])
                    ->setAmount($amount_paid)
                    ->setResponseData($jsonRdata)
                    ->setStatus($data['status'])
                    ->setTransactionDate($transaction_date)
                    ->save();
                $send_email = $this->sendemail($data['customer_id'], $transaction_date, $amount_paid, $data['token'], 'Paypal');
                $detailmodel = new Enterprisepayment_Model_Setting();
                $papCredentials = $detailmodel->find(['value_id' => $data['value'], 'return_value_id' => $data['return_value_id']]);
                $return_url = $papCredentials->getReturnLink();
                $return_state = $papCredentials->getReturnState();
                $return_value_id = $papCredentials->getReturnValueId();

                $html = ['success' => 1, 'return_url' => $return_url, 'return_state' => $return_state, 'return_value_id' => $return_value_id];
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        } else {
            $html = ['error' => 1, 'message' => __('An error occurred during process. Please try again later.')];
        }

        $this->_sendHtml($html);
    }

    /*Function for paypal request*/
    /**
     * @param $method
     * @param $params
     * @param $paypal_payment_mode
     * @param $paypal_api_user
     * @param $paypal_api_user_pwd
     * @param $paypal_api_user_signature
     * @return array|bool
     * @throws Zend_Exception
     */
    public function request($method, $params, $paypal_payment_mode, $paypal_api_user, $paypal_api_user_pwd, $paypal_api_user_signature)
    {
        $logger = Zend_Registry::get("logger");

        //for paypal in live mode
        if ($paypal_payment_mode == 1) {
            $api_url = "https://api-3t.paypal.com/nvp";
            $paypal_url = "https://www.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=";
        } else {
            //paypalpay in test mode
            $api_url = "https://api-3t.sandbox.paypal.com/nvp";
            $paypal_url = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=";
        }
        $params = array_merge($params, [
            'METHOD' => $method,
            'VERSION' => '74.0',
            'USER' => $paypal_api_user,
            'PWD' => $paypal_api_user_pwd,
            'SIGNATURE' => $paypal_api_user_signature,
        ]);

        $orig_params = $params;

        $params = http_build_query($params);
        $curl = curl_init();
        $curlParams = [
            CURLOPT_URL => $api_url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_SSL_VERIFYPEER => false, //si certificat SSL => true
            CURLOPT_SSL_VERIFYHOST => false, //si certificat SSL => 2
        ];

        curl_setopt_array($curl, $curlParams);
        /** @todo testing in production for integration */
        if (APPLICATION_ENV == "development") {
            curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        }
        $response = curl_exec($curl);
        $responseArray = [];
        parse_str($response, $responseArray);

        if (curl_errno($curl)) {
            $this->_errors = curl_error($curl);
            $this->_params = $params;
            curl_close($curl);
            $logger->log("CURL error n° " . print_r($this->_errors, true) . ' - response: ' . print_r($response, true), Zend_Log::DEBUG);
            return false;
        } else {

            if ($responseArray['ACK'] === 'Success') {
                curl_close($curl);

                if (!empty($responseArray['TOKEN']) AND $token = $responseArray['TOKEN']) {
                    $this->__pay_url = $paypal_url . $responseArray['TOKEN'];
                } else {
                    $this->_response = $responseArray;
                }

                return $responseArray;

            } else {
                $this->_errors = $responseArray;
                $this->_params = $params;
                curl_close($curl);
                $logger->log("CURL error: " . print_r($this->_errors, true), Zend_Log::DEBUG);
                return false;
            }
        }
    }

    /*Stripe payment process*/
    /**
     *
     */
    public function stripepaymentAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                \Stripe\Stripe::setApiKey($data['secretkey']);
                $stripe_amt = $data['amount'] * 100;
                $currency = Core_Model_Language::getCurrentCurrency()->getShortName();

                /*Stripe payment charge process*/
                $charge = \Stripe\Charge::create([
                    'amount' => $stripe_amt,
                    'currency' => $currency,
                    'source' => $data['token'],
                    'description' => $data['order_id'],
                ]);
                $stripedata = $charge->jsonSerialize();
                $amount_pay = $stripedata['amount'] / 100;

                /*Amount with proper format*/
                $payment_format = strlen(substr(strrchr($amount_pay, "."), 1));
                if ($payment_format == 1) {
                    $amount_paid = $amount_pay . '0';
                } else if ($payment_format == 0) {
                    $amount_paid = $amount_pay . '.00';
                } else {
                    $amount_paid = $amount_pay;
                }
                $stripe_id = $stripedata['id'];
                $balance_transaction = $stripedata['balance_transaction'];
                $status = $stripedata['status'];
                if ($status == 'succeeded') {
                    $status_response = 1;
                } else {
                    $status_response = 0;
                }
                $sData = ['id' => $stripe_id, 'balance_transaction' => $balance_transaction];
                $jsonRdata = json_encode($sData);
                $transactiondetailmodel = new Enterprisepayment_Model_Transaction();
                $application_id = $this->getApplication()->getId();

                /*Save payment detail into db*/
                $transaction = $transactiondetailmodel->setValueId($data['value'])
                    ->setAppId($application_id)
                    ->setCustomerId($data['customer_id'])
                    ->setType($data['gid'])
                    ->setAmount($amount_paid)
                    ->setResponseData($jsonRdata)
                    ->setStatus($status_response)
                    ->setTransactionDate(date("Y-m-d H:i:s"))
                    ->save();
                $send_email = $this->sendemail($data['customer_id'], date("Y-m-d H:i:s"), $amount_paid, $stripe_id, 'Stripe');
                if ($status_response == 1) {
                    $detailmodel = new Enterprisepayment_Model_Setting();
                    $stripeCredentials = $detailmodel->find(['value_id' => $data['value'], 'return_value_id' => $data['return_value_id']]);
                    $return_url = $stripeCredentials->getReturnLink();
                    $return_state = $stripeCredentials->getReturnState();
                    $return_value_id = $stripeCredentials->getReturnValueId();
                }
                $html = [
                    'success' => 1,
                    'return_url' => $return_url,
                    'transaction_id' => $stripe_id,
                    'return_state' => $return_state,
                    'return_value_id' => $return_value_id,
                ];
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }

        } else {
            $html = ['error' => 1, 'message' => __('An error occurred during process. Please try again later.')];
        }
        $this->_sendHtml($html);
    }

    /*Get publishkey for stripe payment*/
    /**
     *
     */
    public function getpublishkeyAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());

                $detailmodel = new Enterprisepayment_Model_Gatewaydetail();
                $stripeCredentials = $detailmodel->find(['gid' => $data['id'], 'value_id' => $data['value']]);
                if ($stripeCredentials->getData()) {
                    $base_url = $this->getRequest()->getBaseUrl();
                    $stripe_payment_mode = $stripeCredentials->getPaymentMode();

                    /*Test mode payment for stripe get publish and secret key*/
                    if ($stripe_payment_mode == 0) {
                        $testCredentials = json_decode($stripeCredentials->getTestData());
                        $publishkey = $testCredentials->test_publishkey;
                        $secretekey = $testCredentials->test_secrete_key;

                    } else {
                        /*Live mode payment in stripe get publish and secret key*/
                        $liveCredentials = json_decode($stripeCredentials->getLiveData());
                        $publishkey = $liveCredentials->live_publishkey;
                        $secretekey = $liveCredentials->live_secrete_key;
                    }

                    $html = [
                        'success' => 1,
                        'publishkey' => $publishkey,
                        'secretekey' => $secretekey,
                    ];
                } else {
                    $html = ['success' => 0, 'message' => __("Don't have stripe Credential!")];
                }
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        }
        $this->_sendHtml($html);
    }

    /*Get bank transfer details*/
    /**
     *
     */
    public function getbanktransferformAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                $detailmodel = new Enterprisepayment_Model_Gatewaydetail();
                $bank_data = $detailmodel->find(['gid' => $data['gid'], 'value_id' => $data['value_id']]);
                if ($bank_data->getData()) {
                    $data_response = $bank_data->getData();
                    $json_data = json_decode($data_response['live_data'], true);
                    unset($json_data['payment-table_length']);
                    unset($json_data['return_link']);
                    unset($json_data['return_url']);
                    unset($json_data['return_state']);
                    unset($json_data['transactionTable_length']);
                    foreach ($json_data as $key => $value) {
                        $bankfieldsmodel = new Enterprisepayment_Model_Banksetting();
                        $getfields = $bankfieldsmodel->find(['id' => $key]);
                        unset ($json_data[$key]);
                        $new_key = $getfields->getFieldName();
                        $json_data[$new_key] = $value;
                    }
                    $bankfieldsmodel = new Enterprisepayment_Model_Banksetting();
                    $getfields = $bankfieldsmodel->find(['id' => $id]);
                    $getfieldata = $getfields->getData();
                    $base_url = $this->getRequest()->getBaseUrl();
                    $html = [
                        'success' => 1,
                        'bank_data' => $json_data,
                    ];
                } else {
                    $html = ['success' => 0, 'message' => __("Don't have bank transfer Credential!")];
                }
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        }
        $this->_sendHtml($html);
    }

    /*Save bank transfer details*/
    /**
     *
     */
    public function postbankresponseAction()
    {
        if ($this->getRequest()->getRawBody()) {

            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                $token = $this->generateRandomString();
                $response = ['token' => $token, 'type' => 'bank_transfer_response'];
                $jsonResponse = json_encode($response);
                $application_id = $this->getApplication()->getId();

                $transactionmodel = new Enterprisepayment_Model_Transaction();
                /*Amount with proper format*/
                $payment_format = strlen(substr(strrchr($data['amount'], "."), 1));
                $amount_pay = $data['amount'];
                if ($payment_format == 1) {
                    $amount_paid = $amount_pay . '0';
                } else if ($payment_format == 0) {
                    $amount_paid = $amount_pay . '.00';
                } else {
                    $amount_paid = $amount_pay;
                }
                $transaction = $transactionmodel->setValueId($data['value_id'])
                    ->setAppId($application_id)
                    ->setCustomerId($data['customer_id'])
                    ->setType($data['gid'])
                    ->setAmount($amount_paid)
                    ->setResponseData($jsonResponse)
                    ->setStatus('2')
                    ->setTransactionDate(date("Y-m-d H:i:s"))
                    ->save();
                //$send_email = $this->sendemail($data['customer_id'],date("Y-m-d H:i:s"),$amount_paid,$token,'Bank Transfer');
                $detailmodel = new Enterprisepayment_Model_Setting();
                $bank_response = $detailmodel->find(['value_id' => $data['value_id'], 'return_value_id' => $data['return_value_id']]);
                $return_url = $bank_response->getReturnLink();
                $return_state = $bank_response->getReturnState();
                $return_value_id = $bank_response->getReturnValueId();
                $html = ['success' => 1, 'return_url' => $return_url, 'return_state' => $return_state, 'return_value_id' => $return_value_id, 'token' => $token];
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        } else {
            $html = ['error' => 1, 'message' => __('An error occurred during process. Please try again later.')];
        }

        $this->_sendHtml($html);
    }

    /*Save cash details*/
    /**
     *
     */
    public function postcashresponseAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                $token = $this->generateRandomString();
                $response = ['token' => $token, 'type' => 'cash'];
                $jsonResponse = json_encode($response);
                $application_id = $this->getApplication()->getId();
                $transactionmodel = new Enterprisepayment_Model_Transaction();
                /*Amount with proper format*/
                $payment_format = strlen(substr(strrchr($data['amount'], "."), 1));
                $amount_pay = $data['amount'];
                if ($payment_format == 1) {
                    $amount_paid = $amount_pay . '0';
                } else if ($payment_format == 0) {
                    $amount_paid = $amount_pay . '.00';
                } else {
                    $amount_paid = $amount_pay;
                }
                $transaction_date = date("Y-m-d H:i:s");
                $transaction = $transactionmodel->setValueId($data['value_id'])
                    ->setAppId($application_id)
                    ->setCustomerId($data['customer_id'])
                    ->setType($data['gid'])
                    ->setAmount($amount_paid)
                    ->setResponseData($jsonResponse)
                    ->setStatus('2')
                    ->setTransactionDate($transaction_date)
                    ->save();
                //print_r($data);
                $detailmodel = new Enterprisepayment_Model_Setting();
                $bank_response = $detailmodel->find(['value_id' => $data['value_id'], 'return_value_id' => $data['return_value_id']]);
                //print_r($bank_response);die;
                $return_url = $bank_response->getReturnLink();
                $return_state = $bank_response->getReturnState();
                $return_value_id = $bank_response->getReturnValueId();

                $html = ['success' => 1, 'return_url' => $return_url, 'return_state' => $return_state, 'return_value_id' => $return_value_id, 'token' => $token];
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }

        } else {
            $html = ['error' => 1, 'message' => __('An error occurred during process. Please try again later.')];
        }
        $this->_sendHtml($html);
    }

    /*Get payment module payment value id*/
    /**
     *
     */
    public function getpaymentvalueidAction()
    {
        try {
            $enterprisepaymentModel = new Enterprisepayment_Model_Enterprisepayment;
            $response = $enterprisepaymentModel->getModuleDetailsByCode('code', 'enterprisepayment', $this->getApplication()->getId());
            $html = $response['value_id'];
        } catch (Exception $e) {
            $html = ['success' => 0, 'message' => $e->getMessage()];
        }

        $this->_sendHtml($html);
    }

    /*Generate random strings for token of bank transfer and cash payment*/
    /**
     * @param int $length
     * @return string
     */
    function generateRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $customer_id
     * @param $date
     * @param $amt
     * @param $token
     * @param $payment_type
     * @throws Zend_Currency_Exception
     * @throws Zend_Exception
     * @throws Zend_Filter_Exception
     * @throws Zend_Mail_Exception
     * @throws \rock\sanitize\SanitizeException
     */
    function sendemail($customer_id, $date, $amt, $token, $payment_type)
    {

        $customersmodel = new Customer_Model_Customer();
        $customer_getdata = $customersmodel->find(['customer_id' => $customer_id])->getData();
        $currency_name = Core_Model_Language::getCurrentCurrency()->getShortName();
        $currency = Core_Model_Language::getCurrentCurrency()->getSymbol();
        $config = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $sender = $config->getOption('sendermail');

        $layout = $this->getLayout()->loadEmail('enterprisepayment', 'enterprisepayment_email');

        $layout->getPartial('content_email')
            ->setEmail($customer_getdata['email'])
            ->setDate(date($format, $timestamp))
            ->setCustomer($customer_getdata['firstname'])
            ->setToken($token)
            ->setAmount($amt . '' . $currency)
            ->setPaymentType($payment_type)
            ->setDate($date)
            ->setApp($this->getApplication()->getName())->setIcon($this->getApplication()->getIcon());

        $content = $layout->render();
        $mail = new Siberian_Mail();
        $mail->_is_default_mailer = false;
        $mail->setBodyHtml($content);
        $mail->setFrom($sender, $this->getApplication()->getName());
        $mail->_sender_name = $this->getApplication()->getName();
        $mail->addTo($customer_getdata['email'], 'test');
        $mail->setSubject(__('Your payment details!', $this->getApplication()->getName()));
        $mail->send();
    }
    // STEP 1
    /*Get token for payu latam payment*/
    /**
     *
     */
    public function getpayulatamtokenAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());

                $detailmodel = new Enterprisepayment_Model_Gatewaydetail();
                $payu_latam_credentials = $detailmodel->find(['gid' => $data['id'], 'value_id' => $data['value']]);
                if ($payu_latam_credentials->getData()) {
                    $base_url = $this->getRequest()->getBaseUrl();
                    $payu_latam_payment_mode = $payu_latam_credentials->getPaymentMode();

                    $post_c_data = $data['form_data'];
                    $billing = [
                        "phone" => "+1-541-754-3010",
                        "country" => "BRA",
                        "line1" => "test",

                    ];
                    $check_month = strlen($post_c_data['exp_month']);
                    if ($check_month < 2) {
                        $expiration_month = '0' . $post_c_data['exp_month'];
                    } else {
                        $expiration_month = $post_c_data['exp_month'];
                    }
                    $expiration_year = substr($post_c_data['exp_year'], -2);

                    $expiration_date = $expiration_month . '/' . $expiration_year;

                    $post_for_token = [
                        "token_type" => "credit_card",
                        "credit_card_cvv" => (string) $post_c_data['cvc'],
                        "card_number" => (string) $post_c_data['number'],
                        "expiration_date" => $expiration_date,  //"10/29",
                        "holder_name" => $post_c_data['name'],
                        "billing_address" => $billing,
                    ];

                    if ($payu_latam_payment_mode == 1) {
                        $live_credentials = json_decode($payu_latam_credentials->getLiveData());
                        $api_data = [
                            'api_app_id' => $live_credentials->live_payulatam_app_id,
                            'api_public_key' => $live_credentials->live_payulatam_public_key,
                            'api_env' => 'live',
                        ];
                    } else {
                        $test_credentials = json_decode($payu_latam_credentials->getTestData());

                        $api_data = [
                            'api_app_id' => $test_credentials->test_payulatam_app_id,
                            'api_public_key' => $test_credentials->test_payulatam_public_key,
                            'api_env' => 'test',
                        ];
                    }
                    $payu_latam_token = $this->getPayuLatmKeyfromApi($post_for_token, $api_data);
                    if ($payu_latam_token->token) {
                        $html = [
                            'success' => 1,
                            'payu_latam_token' => $payu_latam_token->token,
                        ];
                    } else {
                        $html = [
                            'success' => 0,
                            'payu_latam_token' => $payu_latam_token->token,
                        ];
                    }

                } else {
                    $html = ['success' => 0, 'message' => __("Don't have stripe Credential!")];
                }
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        }
        $this->_sendHtml($html);
    }

    /**
     * @param $post_for_token
     * @param $api_data
     * @return bool|mixed
     */
    public function getPayuLatmKeyfromApi($post_for_token, $api_data)
    {
        $curl = curl_init();
        $curlPost = json_encode($post_for_token);
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paymentsos.com/tokens",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $curlPost,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "api-version: 1.2.0",
                "app_id:" . $api_data['api_app_id'],
                "cache-control: no-cache",
                "public_key:" . $api_data['api_public_key'],
                "x-payments-os-env:" . $api_data['api_env'],
            ],
        ]);
        $response = curl_exec($curl);
        $api_response = json_decode($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return $api_response;
        }
    }

    /*create payment using payu latam*/
    /**
     *
     */
    public function createpayulatampaymentidAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());

                $detailmodel = new Enterprisepayment_Model_Gatewaydetail();
                $payu_latam_credentials = $detailmodel->find(['gid' => $data['id'], 'value_id' => $data['value']]);
                if ($payu_latam_credentials->getData()) {
                    $base_url = $this->getRequest()->getBaseUrl();
                    $payu_latam_payment_mode = $payu_latam_credentials->getPaymentMode();

                    if ($payu_latam_payment_mode == 1) {
                        $live_credentials = json_decode($payu_latam_credentials->getLiveData());
                        $api_data = [
                            'api_app_id' => $live_credentials->live_payulatam_app_id,
                            'api_private_key' => $live_credentials->live_payulatam_private_key,
                            'api_env' => 'live',
                        ];
                    } else {
                        $test_credentials = json_decode($payu_latam_credentials->getTestData());
                        $api_data = [
                            'api_app_id' => $test_credentials->test_payulatam_app_id,
                            'api_private_key' => $test_credentials->test_payulatam_private_key,
                            'api_env' => 'test',
                        ];
                    }

                    $billing_address = [
                        "phone" => "+1-541-754-3010",
                        "country" => "BRA",
                        "line1" => "test",

                    ];
                    $four_digits = 3;
                    $order_id = rand(pow(10, $four_digits - 1), pow(10, $four_digits) - 1);
                    $myorder_id = ['id' => "myorderid" . $order_id,];
                    $post_for_payment_id = [
                        "amount" => (int) $data['amount'] * 100,
                        "currency" => "BRL",
                        "billing_address" => $billing_address,
                        "order" => $myorder_id,
                    ];
                    $four_digits = 4;
                    $first_rand = rand(pow(10, $four_digits - 1), pow(10, $four_digits) - 1);
                    $six_digits = 6;
                    $sec_rand = rand(pow(10, $six_digits - 1), pow(10, $six_digits) - 1);
                    $randomInt = 'cust-' . $first_rand . '-trans-' . $sec_rand . 'p';

                    $payment_api_respose = $this->getPayuPaymentId($post_for_payment_id, $api_data);
                    if ($payment_api_respose->id) {
                        $html = [
                            'success' => 1,
                            'payu_latam_payment_id' => $payment_api_respose->id,
                            'reconciliation_id' => $randomInt,
                        ];
                    } else {
                        $html = [
                            'success' => 0,
                            'payu_latam_payment_id' => $payment_api_respose->id,
                            'reconciliation_id' => false,
                        ];
                    }

                } else {
                    $html = ['success' => 0, 'message' => __("Don't have stripe Credential!")];
                }
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        }
        $this->_sendHtml($html);
    }

    //Step -2

    /**
     * @param $post_for_payment_id
     * @param $api_data
     * @param $randomInt
     * @return bool|mixed
     */
    public function getPayuPaymentId($post_for_payment_id, $api_data, $randomInt)
    {
        $curl = curl_init();
        $curlPost = json_encode($post_for_payment_id);


        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paymentsos.com/payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $curlPost,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "api-version: 1.2.0",
                "app_id:" . $api_data['api_app_id'],
                "cache-control: no-cache",
                "idempotency_key:" . $randomInt,
                "private_key:" . $api_data['api_private_key'],
                "x-payments-os-env:" . $api_data['api_env'],
            ],
        ]);
        $response = curl_exec($curl);
        $api_response = json_decode($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {

            return $api_response;
        }
    }
    //Step 4
    /*create payment using payu latam*/
    /**
     *
     */
    public function createpayulatamchanrgeAction()
    {
        if ($this->getRequest()->getRawBody()) {
            try {
                $data = Zend_Json::decode($this->getRequest()->getRawBody());
                $detailmodel = new Enterprisepayment_Model_Gatewaydetail();
                $payu_latam_credentials = $detailmodel->find(['gid' => $data['id'], 'value_id' => $data['value']]);
                if ($payu_latam_credentials->getData()) {
                    $base_url = $this->getRequest()->getBaseUrl();
                    $payu_latam_payment_mode = $payu_latam_credentials->getPaymentMode();

                    if ($payu_latam_payment_mode == 1) {
                        $live_credentials = json_decode($payu_latam_credentials->getLiveData());
                        $api_data = [
                            'api_app_id' => $live_credentials->live_payulatam_app_id,
                            'api_private_key' => $live_credentials->live_payulatam_private_key,
                            'api_env' => 'live',
                        ];
                    } else {
                        $test_credentials = json_decode($payu_latam_credentials->getTestData());
                        $api_data = [
                            'api_app_id' => $test_credentials->test_payulatam_app_id,
                            'api_private_key' => $test_credentials->test_payulatam_private_key,
                            'api_env' => 'test',
                        ];
                    }
                    $payment_details = [
                        "type" => "tokenized",
                        "token" => $data['token'],
                        "country_code" => "BR",

                    ];
                    ///////////////
                    $billing_address['billing_address'] = [
                        "phone" => "+1-541-754-3010",
                        "country_code" => "BR",

                        "line1" => "test",

                    ];
                    $provider_specific_data = [
                        "payu_latam" => [
                            "additional_details" => [
                                "accept_terms_and_conditions" => true,
                                "cookie" => "",
                                "customer_cnpj_identify_number" => "32593371000110",
                                "customer_national_identify_number" => "123456",
                                "payer_email" => "John.Doe@email.com",
                            ],
                            "device_fingerprint" => [
                                "fingerprint" => "35",
                                "provider" => "PayULatam",
                            ],
                        ],
                    ];
                    //////////////////
                    $post_form_payment_response = [
                        "payment_method" => $payment_details,
                        "provider_specific_data" => $provider_specific_data,
                        "payment" => $billing_address,

                        "reconciliation_id" => $data['reconciliation_id'],
                    ];
                    $payment_api_respose = $this->createPayUCharge($post_form_payment_response, $api_data, $data['payu_latam_payment_id'], $data['reconciliation_id']);
                    if ($payment_api_respose->id) {
                        $html = [
                            'success' => 1,
                            'payu_latam_payment_id' => $payment_api_respose->id,
                        ];
                    } else {
                        $html = [
                            'success' => 0,
                            'payu_latam_payment_id' => $payment_api_respose->id,
                        ];
                    }

                } else {
                    $html = ['success' => 0, 'message' => __("Don't have PayU Latam Credential!")];
                }
            } catch (Exception $e) {
                $html = ['success' => 0, 'message' => $e->getMessage()];
            }
        }
        $this->_sendHtml($html);
    }

    /**
     * @param $post_form_payment_response
     * @param $api_data
     * @param $payment_id
     * @param $reconciliation_id
     */
    public function createPayUCharge($post_form_payment_response, $api_data, $payment_id, $reconciliation_id)
    {

        $curlPost = json_encode($post_form_payment_response);
        $four_digits = 4;
        $first_rand = rand(pow(10, $four_digits - 1), pow(10, $four_digits) - 1);
        $six_digits = 6;
        $sec_rand = rand(pow(10, $six_digits - 1), pow(10, $six_digits) - 1);
        $randomInt = 'cust-' . $first_rand . '-trans-' . $sec_rand . 'p';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.paymentsos.com/payments/" . (string) $payment_id . "/charges",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $curlPost,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "api-version: 1.2.0",
                "app_id:" . $api_data['api_app_id'],
                "cache-control: no-cache",
                "idempotency_key:" . $randomInt,
                "private_key:" . $api_data['api_private_key'],
                "x-payments-os-env:" . $api_data['api_env'],
            ],
        ]);
        $response = curl_exec($curl);
        var_dump($response);
        die;
        $api_response = json_decode($response);
        print_r($api_response);
        die;
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
    }
}