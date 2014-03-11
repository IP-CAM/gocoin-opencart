<?php

class ControllerPaymentGocoin extends Controller {

    var $pay_url = 'https://gateway.gocoin.com/merchant/';

    protected function index() {
        $this->data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        //$this->data['action'] = 'https://www.2checkout.com/checkout/purchase';
        $this->data['action'] = $this->url->link('payment/gocoin/processorder', '', '');
        ;

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

//		$this->data['return_url'] = $this->url->link('payment/twocheckout/callback', '', 'SSL');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/gocoin.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/gocoin.tpl';
        } else {
            $this->template = 'default/template/payment/gocoin.tpl';
        }

        $this->render();
    }

    public function processorder() {
        
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $coin_currency = $this->request->request['gocoin_coincurrency'];

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
            'price_currency' => $coin_currency,
            'base_price' => $price,
            'base_price_currency' => "USD", //$order_info['currency_code'],
            'notification_level' => "all",
            'callback_url' => $this->url->link('payment/gocoin/callback', '', ''),
            'redirect_url' =>  $this->url->link('checkout/success', '' , ''),
            'order_id' => $this->session->data['order_id'],
            'customer_name' => $customer_name,
            'customer_address_1' => $customer_address_1,
            'customer_address_2' => $customer_address_2,
            'customer_city' => $customer_city,
            'customer_region' => $customer_region,
            'customer_postal_code' => $customer_postal_code,
            'customer_country' => $customer_country,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
        );
        $data_string = json_encode($options);
        
        $merchant_id = $this->config->get('gocoin_gocoinmerchant');
        $gocoin_access_key = $this->config->get('gocoin_gocoinsecretkey');
        $gocoin_token       =   $this->config->get('gocoin_gocointoken');
        $gocoin_url = $this->pay_url;


        $arr = array(
            'client_id' => $merchant_id,
            'client_secret' => $gocoin_access_key,
            'scope' => "user_read_write+merchant_read_write+invoice_read_write",);


        include DIR_SYSTEM . 'library/gocoinlib/src/client.php';
        $client = new Client($arr);
        $client->setToken($gocoin_token);
        if (!$client) {
            $result = 'error';
            $json['error'] = 'GoCoin does not permit';
            
        }
        $user = $client->api->user->self();
        if (!$user) {
            $result = 'error';
           $json['error'] = 'GoCoin does not permit';
             
        } else {
            $invoice_params = array(
                'id' => $user->merchant_id,
                'data' => $data_string
            );
            if (!$invoice_params) {
                $result = 'error';
                $json['error'] = $client->getError();
            }
            $invoice = $client->api->invoices->create($invoice_params);
            
            if (isset($invoice->errors)) {
                $result = 'error';
                $json['error'] = 'GoCoin does not permit';
            } elseif (isset($invoice->error)) {
                $result = 'error';
                $json['error'] = $invoice->error;
            } elseif (isset($invoice->merchant_id) && $invoice->merchant_id != '' && isset($invoice->id) && $invoice->id != '') {
                $url = $gocoin_url . $invoice->merchant_id . "/invoices/" . $invoice->id;
                $result = 'success';
                $messages = 'success';
                $redirect = $url;
            }

            if (isset($result) && $result == 'success' && isset($url)) {
                $json['success'] =  $url;
            } else {
                //$json['error'] = 'GoCoin does not permit';
            }
        }
        
        $this->response->setOutput(json_encode($json));
    }

    public function callback() {
         $this->_paymentStandard();
    }

    public function success() {
        
    }
    public function getNotifyData() {
            $post_data = file_get_contents("php://input");
            if (!$post_data) {
                $response = new stdClass();
                $response->error = 'Post Data Error';
                return $response;
            }
            $response = json_decode($post_data);
            return $response;
  }
  
  
	private function _paymentStandard()
	{
      $this->load->model('checkout/order');
      $this->load->model('payment/gocoin');
      $sts_processing = $this->model_payment_gocoin->getOrderStatus('Processing'); // Processing
      $sts_failed     = $this->model_payment_gocoin->getOrderStatus('Failed'); // Failed
                    
      $module_display = 'gocoin';
      $response = $this->getNotifyData();
      if(!$response){
        //======================Error=============================     
      }
      if(isset($response->error) && $response->error!='') {
         
      }
      if(isset($response->payload)){
        
        //======================IF Response Get=============================     
          
         $event             = $response->event ;          
         $order_id           = (int) $response->payload->order_id; 
         $redirect_url      = $response->payload->redirect_url;   
         $transction_id     = $response->payload->id;  
         $total             = $response->payload->base_price;  
         $status            = $response->payload->status;
         $currency_id       = $response->payload->user_defined_1;
         $secure_key        = $response->payload->user_defined_2 ;
         $currency          = $response->payload->base_price_currency;
         $currency_type     = $response->payload->price_currency;
         $invoice_time      = $response->payload->created_at   ;      
         $expiration_time   = $response->payload->expires_at   ;
         $updated_time      = $response->payload->updated_at   ;
         $merchant_id       = $response->payload->merchant_id  ;
         $btc_price         = $response->payload->price  ;  
         $price             = $response->payload->base_price  ;
         $url = "https://gateway.gocoin.com/merchant/".$merchant_id ."/invoices/".$transction_id;
         
         //=================== Set To Array=====================================//
         //Used for adding in db
         $iArray    = array(
             'order_id'=>$order_id,
             'invoice_id'=>$transction_id,
             'url'=>$url,
             'status'=>$event,
             'btc_price'=>$btc_price,
             'price'=>$price,
             'currency'=>$currency,
             'currency_type'=>$currency_type,
             'invoice_time'=>$invoice_time,
             'expiration_time'=>$expiration_time,
             'updated_time'=>$updated_time);   
         
         
                switch ($status) {
                    case 'paid':
                        $sts = $sts_processing;
                        break;

                    case 'unpaid':
                        $sts = $sts_failed; // Failed
                        break;

                    default:
                        $sts = $sts_failed; // Failed
                        break;
                }
                    
                       
                if ($sts == '') {
                    $sts = '10';
                }

                if ($event == 'invoice_created') {
                    $this->model_checkout_order->confirm($order_id, $sts);
                    $this->model_payment_gocoin->addTransaction('payment',$iArray );
                }
          

        if(isset($redirect_url) && $redirect_url!=''){
            header("location: ".$redirect_url);
            exit;
        }
      }      
      
	}
 
}

?>