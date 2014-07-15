<?php
include_once DIR_SYSTEM . 'library/gocoinlib/src/GoCoin.php';

class ControllerPaymentGocoin extends Controller {

    var $pay_url = 'https://gateway.gocoin.com/merchant/';

    protected function index() {
        $this->data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->data['action'] = $this->url->link('payment/gocoin/processorder', '', '');
        

        $this->data['currency_code'] = $order_info['currency_code'];
        $this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $this->data['cart_order_id'] = $this->session->data['order_id'];
        $this->data['card_holder_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $this->data['street_address'] = $order_info['payment_address_1'];
        $this->data['city'] = $order_info['payment_city'];

        if ($order_info['payment_iso_code_2'] == 'US' || $order_info['payment_iso_code_2'] == 'CA') {
            $this->data['state'] = $order_info['payment_zone'];
        } else {
            $this->data['state'] = 'XX';
        }

        $this->data['zip'] = $order_info['payment_postcode'];
        $this->data['country'] = $order_info['payment_country'];
        $this->data['email'] = $order_info['email'];
        $this->data['phone'] = $order_info['telephone'];

        if ($this->cart->hasShipping()) {
            $this->data['ship_street_address'] = $order_info['shipping_address_1'];
            $this->data['ship_city'] = $order_info['shipping_city'];
            $this->data['ship_state'] = $order_info['shipping_zone'];
            $this->data['ship_zip'] = $order_info['shipping_postcode'];
            $this->data['ship_country'] = $order_info['shipping_country'];
        } else {
            $this->data['ship_street_address'] = $order_info['payment_address_1'];
            $this->data['ship_city'] = $order_info['payment_city'];
            $this->data['ship_state'] = $order_info['payment_zone'];
            $this->data['ship_zip'] = $order_info['payment_postcode'];
            $this->data['ship_country'] = $order_info['payment_country'];
        }

        $this->data['products'] = array();

        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $this->data['products'][] = array(
                'product_id' => $product['product_id'],
                'name' => $product['name'],
                'description' => $product['name'],
                'quantity' => $product['quantity'],
                'price' => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value'], false)
            );
        }

        $this->data['demo'] = '';

        $this->data['display'] = 'Y';
        $this->data['lang'] = $this->session->data['language'];

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/gocoin.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/gocoin.tpl';
        } else {
            $this->template = 'default/template/payment/gocoin.tpl';
        }

        $this->render();
    }

    public function processorder() {
        $this->load->model('checkout/order');
        $this->load->model('payment/gocoin');
        $sts_pending = $this->model_payment_gocoin->getOrderStatus('Pending'); //pending

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $coin_currency = $this->request->request['gocoin_coincurrency'];

        $error_log_path = DIR_LOGS.'gocoin_error.log';
        $errorlog       = '';
        
        $customer_name = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $customer_address_1 = '';
        $customer_address_2 = '';
        $customer_city = '';
        $customer_region = '';
        $customer_postal_code = '';
        $customer_country = '';
        $customer_phone = '';
        $customer_email = '';
        if ($this->cart->hasShipping()) {
            $customer_address_1 = $order_info['shipping_address_1'];
            $customer_city = $order_info['shipping_city'];
            $customer_region = $order_info['shipping_zone'];
            $customer_postal_code = $order_info['shipping_postcode'];
            $customer_country = $order_info['shipping_country'];
        } else {
            $customer_address_1 = $order_info['payment_address_1'];
            $customer_city = $order_info['payment_city'];
            $customer_region = $order_info['payment_zone'];
            $customer_postal_code = $order_info['payment_postcode'];
            $customer_country = $order_info['payment_country'];
        }
        $customer_email = $order_info['email'];
        $customer_phone = $order_info['telephone'];
        $price = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);


        $options = array(
            'price_currency'        => $coin_currency,
            'base_price'            => $price,
            'base_price_currency'   => "USD",
            'callback_url'          => $this->url->link('payment/gocoin/callback', '', ''),
            'redirect_url'          => $this->url->link('checkout/success', '', ''),
            'order_id'              => $this->session->data['order_id'],
            'customer_name'         => $customer_name,
            'customer_address_1'    => $customer_address_1,
            'customer_address_2'    => $customer_address_2,
            'customer_city'         => $customer_city,
            'customer_region'       => $customer_region,
            'customer_postal_code'  => $customer_postal_code,
            'customer_country'      => $customer_country,
            'customer_phone'        => $customer_phone,
            'customer_email'        => $customer_email,
        );

        $key                        = $this->getGUID();
        $signature                  = $this->getSignatureText($options, $key);
        $options['user_defined_8']  = $signature;
        $access_token               = $this->config->get('gocoin_gocointoken');
        $gocoin_url                 = $this->pay_url;

        
        $json = array();
        $result = 'error'; 
        
        if (empty($access_token)) { //-----------If  Token not found 
            $result = 'error';
            $json['error'] = 'GoCoin Payment Paramaters not Set. Please report this to Site Administrator.';
            $errorlog      .= 'Access Token Blank';
        } 
        else { // If  Token  found 
               try{
                        $user = GoCoin::getUser($access_token); //----------- If no Error in user creation from token
                        if ($user) { //----------- If user Variable  is  not blank 
                            $merchant_id = $user->merchant_id;
                            if (!empty($merchant_id)) { //----------- If merchant_id Variable is not blank 
                                $invoice = GoCoin::createInvoice($access_token, $merchant_id, $options);
                                if (isset($invoice->errors)) { //----------- if $invoice->errors found 
                                    $result = 'error';
                                    $errormsg = isset($invoice->errors->currency_code[0])? $invoice->errors->currency_code[0] : '';
                                    $json['error'] = "Error in Processing Order using GoCoin:". $errormsg;
                                    $errorlog      .=  $errormsg;
                                } elseif (isset($invoice->error)) {    //----------- if $invoice->error found 
                                    $result = 'error';
                                    $json['error'] = "Error in Processing Order using GoCoin ".$invoice->error;
                                     $errorlog      .=  $invoice->error;
                                } elseif (isset($invoice->merchant_id) && $invoice->merchant_id != '' && isset($invoice->id) && $invoice->id != '') {
                                    $url = $gocoin_url . $invoice->merchant_id . "/invoices/" . $invoice->id;
                                    $result = 'success';
                                    $messages = 'success';
                                    $json['success'] = $url;
                                    $json_array = array(
                                        'order_id' => $invoice->order_id,
                                        'invoice_id' => $invoice->id,
                                        'url' => $url,
                                        'status' => 'invoice_created',
                                        'btc_price' => $invoice->price,
                                        'price' => $invoice->base_price,
                                        'currency' => $invoice->base_price_currency,
                                        'currency_type' => $invoice->price_currency,
                                        'invoice_time' => $invoice->created_at,
                                        'expiration_time' => $invoice->expires_at,
                                        'updated_time' => $invoice->updated_at,
                                        'fingerprint' => $signature,
                                    );
                                    $this->model_checkout_order->confirm($this->session->data['order_id'], $sts_pending,'Your Order status : Pending Waiting for payment confirmation ',true);
                                    $this->model_payment_gocoin->addTransaction('payment', $json_array);
                                }
                                else{
                                    //-----------  if $invoice is balnk 
                                    $result = 'error';
                                    $json['error'] = "Error in Processing Order using GoCoin ";
                                    $errorlog      .=  'invoice variable blank ';

                                }
                            }
                            else { //----------- If merchant_id Variable is blank 
                                $result = 'error';
                                $json['error'] =  'Error in Processing Order using GoCoin, please try selecting other payment options';
                                $errorlog      .=  'merchant_id variable blank ';
                            }
                        } 
                        else { //----------- If user Variable is blank 
                            $result = 'error';
                            $json['error'] =  'Error in Processing Order using GoCoin, please try selecting other payment options';
                            $errorlog      .=  'User variable blank ';
                        }
                 }
               catch (Exception $e) {
                    //----------- If error in user creation from token
                    $result = 'error'; 
                    $json['error'] = 'Error in Processing Order using GoCoin, please try selecting other payment options';
                    $errorlog      .=  'error in user creation from token';
                }
        }

        
        if(isset($json['error']) && !empty($json['error'])){
               error_log($date = date('d.m.Y h:i:s').':'.$json['error'].$errorlog.'\n', 3, $error_log_path);
        }
        
        $this->response->setOutput(json_encode($json));
    }

    public function callback() {
        $this->_paymentStandard();
    }

    public function success() {
        
    }

    public function gettoken() {
        $code = isset($_REQUEST['code']) && !empty($_REQUEST['code'])? $_REQUEST['code']:'';
        $client_id = $this->config->get('gocoin_gocoinmerchant');
        $client_secret = $this->config->get('gocoin_gocoinsecretkey');

        try {
            $token = GoCoin::requestAccessToken($client_id, $client_secret, $code, null);
            echo "<b>Copy this Access Token into your GoCoin Module: </b><br>" . $token;
        } catch (Exception $e) {
            echo "Problem in getting Token: " . $e->getMessage();
        }
        die();
    }

    public function getNotifyData() {
        $post_data = file_get_contents("php://input");
        error_log('/******************************************************/ \n'.date('h:i:s A').file_get_contents("php://input").'\n',3,DIR_LOGS.'tester.log');
         if (!$post_data) {
            $response = new stdClass();
            $response->error = 'Post Data Error';
            return $response;
        }
        $response = json_decode($post_data);
        return $response;
    }

    private function _paymentStandard() {
        $this->load->model('checkout/order');
        $this->load->model('payment/gocoin');
        $sts_processing = $this->model_payment_gocoin->getOrderStatus('Processing'); // Processing
        $sts_failed = $this->model_payment_gocoin->getOrderStatus('Failed'); // Failed
        $sts_pending = $this->model_payment_gocoin->getOrderStatus('Pending'); //pending

        $module_display = 'gocoin';
        $response = $this->getNotifyData();
        if (!$response) {
            //======================Error=============================     
        }
        if (isset($response->error) && $response->error != '') {
            
        }
        if (isset($response->payload)) {

            //======================IF Response Get=============================     

            $event = $response->event;
            $order_id = (int) $response->payload->order_id;
            $redirect_url = $response->payload->redirect_url;
            $transction_id = $response->payload->id;
            $total = $response->payload->base_price;
            $status = $response->payload->status;
            $currency_id = $response->payload->user_defined_1;
            $secure_key = $response->payload->user_defined_2;
            $currency = $response->payload->base_price_currency;
            $currency_type = $response->payload->price_currency;
            $invoice_time = $response->payload->created_at;
            $expiration_time = $response->payload->expires_at;
            $updated_time = $response->payload->updated_at;
            $merchant_id = $response->payload->merchant_id;
            $btc_price = $response->payload->price;
            $price = $response->payload->base_price;
            $url = "https://gateway.gocoin.com/merchant/" . $merchant_id . "/invoices/" . $transction_id;
            $fprint = $response->payload->user_defined_8;
            //=================== Set To Array=====================================//
            //Used for adding in db
            $iArray = array(
                'order_id' => $order_id,
                'invoice_id' => $transction_id,
                'url' => $url,
                'status' => $event,
                'btc_price' => $btc_price,
                'price' => $price,
                'currency' => $currency,
                'currency_type' => $currency_type,
                'invoice_time' => $invoice_time,
                'expiration_time' => $expiration_time,
                'updated_time' => $updated_time,
                'fingerprint' => $fprint);

            $i_id = $this->model_payment_gocoin->getFPStatus($iArray);
            if (!empty($i_id) && $i_id == $transction_id) {

                $this->model_payment_gocoin->updateTransaction('payment', $iArray);

                switch ($event) {
                    case 'invoice_created':
                    case 'invoice_payment_received':
                       break;
                    case 'invoice_ready_to_ship':
                  
                        $sts = $sts_processing;
                        if (($status == 'paid') || ($status == 'ready_to_ship')) {
                             $this->model_checkout_order->update($order_id, $sts,'Your Order under Processing ',true);
                        }
                        break;
                    default :
                        $sts = $sts_pending;
                       
                }
            } elseif (!empty($fprint)) {
                $msg = "\n Fingerprint : " . $fprint . " does not match for Order id :" . $order_id;
                error_log($msg, 3, 'gocoin_error_log.txt');
            } else {
                $msg = "\n No Fingerprint received for with Order id :" . $order_id;
                error_log($msg, 3, 'gocoin_error_log.txt');
            }
        }
    }

    public function getGUID() {
        if (function_exists('com_create_guid')) {
            $guid = com_create_guid();
            $guid = str_replace("{", "", $guid);
            $guid = str_replace("}", "", $guid);
            return $guid;
        } else {
            mt_srand((double) microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                    . substr($charid, 8, 4) . $hyphen
                    . substr($charid, 12, 4) . $hyphen
                    . substr($charid, 16, 4) . $hyphen
                    . substr($charid, 20, 12); // .chr(125) //"}"
            return $uuid;
        }
    }

    public function getSignatureText($data, $uniquekey) {
        $query_str = '';
        $include_params = array('price_currency', 'base_price', 'base_price_currency', 'order_id', 'customer_name', 'customer_city', 'customer_region', 'customer_postal_code', 'customer_country', 'customer_phone', 'customer_email');
        if (is_array($data)) {
            ksort($data);
            $querystring = "";
            foreach ($data as $k => $v) {
                if (in_array($k, $include_params)) {
                    $querystring = $querystring . $k . "=" . $v . "&";
                }
            }
        } else {
            if (isset($data->payload)) {
                $payload_obj = $data->payload;
                $payload_arr = get_object_vars($payload_obj);
                ksort($payload_arr);
                $querystring = "";
                foreach ($payload_arr as $k => $v) {
                    if (in_array($k, $include_params)) {
                        $querystring = $querystring . $k . "=" . $v . "&";
                    }
                }
            }
        }
        $query_str = substr($querystring, 0, strlen($querystring) - 1);
        $query_str = strtolower($query_str);
        $hash2 = hash_hmac("sha256", $query_str, $uniquekey, true);
        $hash2_encoded = base64_encode($hash2);
        return $hash2_encoded;
    }

}
?>