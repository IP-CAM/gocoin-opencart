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
        
        // $sts_pending = $this->model_payment_gocoin->getOrderStatus('Pending'); //pending
        //=======================Order Object ==========================
         $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
         $currency_code = $order_info['currency_code'];
        //=======================GoCoin config ==========================
        $merchant_id = $this->config->get('gocoin_gocoinmerchant');
        $access_token = $this->config->get('gocoin_gocoinsecretkey');
        $sts_pending = $this->config->get('gocoin_status');
        
        
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

        $json = array();
        $result = 'error';
         
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
           $php_version_allowed = true ;
         }
        else{
          $php_version_allowed = false ;
        }
        
        
        if (empty($access_token)) {
                  $msg = 'Improper Gateway set up. Access token not found.';
                  $json['error'] = $msg;
                  $this->model_payment_gocoin->log($msg);
        }
              //Check to make sure we have a merchant ID
        elseif (empty($merchant_id)) {
                  $msg = 'Improper Gateway set up. Merchant ID not found.';
                  $json['error'] = $msg;
                  $this->model_payment_gocoin->log($msg);
        }
        elseif ($php_version_allowed == false) {
                  $msg = 'The minimum PHP version required for GoCoin plugin is 5.3.0';
                  $json['error'] = $msg;
                  $this->model_payment_gocoin->log($msg);
        }
        // Proceed
        else {   
              $options = array(
                    "type"                  => 'bill',
                    'base_price'            => $price,
                    'base_price_currency'   => $currency_code,
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
                    'customer_email'        => $customer_email,);

                  $signature                  = $this->sign($options, $access_token);
                  $options['user_defined_8']  = $signature;
                  $gocoin_url                 = $this->pay_url;

        
                  
                try {
                  $invoice = GoCoin::createInvoice($access_token, $merchant_id, $options);
                  $url = $invoice->gateway_url;
                  $json['success'] = $url;
                  $this->model_checkout_order->confirm($this->session->data['order_id'], $sts_pending,'Your Order status : Pending Waiting for payment confirmation ',true);
                  $this->cart->clear();                   
                } catch (Exception $e) {
                  $msg = $e->getMessage();
                  $json['error'] = 'GoCoin Error '.$msg;
                  $this->model_payment_gocoin->log($msg);
                }
         }
        $this->response->setOutput(json_encode($json));
    }

    public function callback() {
        $this->_paymentStandard();
    }

    public function success() {
        
    }

    private function _paymentStandard() {
        $this->load->model('checkout/order');
        $this->load->model('payment/gocoin');
        
        $sts_failed     = $this->model_payment_gocoin->getOrderStatus('Failed'); // Failed
        $sts_pending    = $this->model_payment_gocoin->getOrderStatus('Pending'); //pending
        $sts_processing = $this->model_payment_gocoin->getOrderStatus('Processing'); // Processing (For Hold status)
        $sts_processed  = $this->model_payment_gocoin->getOrderStatus('Processed'); // Processed
        $module_display = 'gocoin';
        
        $key            = $this->config->get('gocoin_gocoinsecretkey');
        if(empty($key)){
            $this->model_payment_gocoin->log('gocoin-callback', 'Api Key is  blank');
        } 
      $data = $this->postData(); 
      if (isset($data->error)){
        $this->model_payment_gocoin->log($data->error);
      }
      else {
      
        $event_id           = $data -> id;
        $event              = $data -> event;
        $invoice            = $data -> payload;
        $payload_arr        = get_object_vars($invoice) ;
                 ksort($payload_arr);
        $signature          = $invoice -> user_defined_8;
        
        $sig_comp           = $this->sign($payload_arr, $key);
        $status             = $invoice -> status;
        $order_id           = (int) $invoice -> order_id;
        $order_info         = $this->model_checkout_order->getOrder($order_id);
        
        if (!is_array($order_info)) {
          $msg = "Order with id: " . $order_id . " was not found. Event ID: " . $event_id;
          $this->model_payment_gocoin->log($msg);
        }
       
        // Check that if a signature exists, it is valid
        if (isset($signature) && ($signature != $sig_comp)) {
          $msg = "Signature : " . $signature . "does not match for Order: " . $order_id ."$sig_comp        |    $signature ";
          $msg .= ' Event ID: '. $event_id;  
          $this->model_payment_gocoin->log($msg);
        }
        elseif (empty($signature) || empty($sig_comp) ) {
          $msg = "Signature is blank for Order: " . $order_id;
          $msg .= ' Event ID: '. $event_id;  
          $this->model_payment_gocoin->log($msg);
        }
        elseif($signature == $sig_comp) {
            
          switch($event) {

            case 'invoice_created':
              break;

            case 'invoice_payment_received':
              switch ($status) {
                 case 'ready_to_ship':
                  $msg = 'Order ' . $order_id .' is paid and awaiting payment confirmation on blockchain.'; 
                  break; 
                case 'paid':
                  $msg = 'Order ' . $order_id .' is paid and awaiting payment confirmation on blockchain.'; 
                  break;
                case 'underpaid':
                  $msg = 'Order ' . $order_id .' is underpaid.';
                  break;
              }
              $msg .= ' Event ID: '. $event_id;   
              $this->model_checkout_order->update($order_id,$sts_processing,'Your Order Status: Processing(awaiting payment confirmation) ',true);
              $this->model_payment_gocoin->log($msg);
              
              break;

            case 'invoice_merchant_review':
                $msg = 'Order ' . $order_id .' is under review. Action must be taken from the GoCoin Dashboard.';
                $msg .= ' Event ID: '. $event_id;  
                 
                $this->model_checkout_order->update($order_id,$sts_processing,'Your Order Status:  Processing(awaiting payment confirmation) ',true);
                $this->model_payment_gocoin->log($msg);
              break;

            case 'invoice_ready_to_ship':
              $msg = 'Order ' . $order_id .' has been paid in full and confirmed on the blockchain.';
              $msg .= ' Event ID: '. $event_id;  
               
                $this->model_checkout_order->update($order_id,$sts_processed,'Your Order Status:  Processed',true);
                $this->model_payment_gocoin->log($msg);
               
              break;

            case 'invoice_invalid':
                $msg = 'Order ' . $order_id . ' is invalid and will not be confirmed on the blockchain.';
                $msg .= ' Event ID: '. $event_id;  
                $this->model_checkout_order->update($order_id,$sts_failed,'Your Order Status:  Failed',true);
                $this->model_payment_gocoin->log($msg);
             break;

            default: 
              $msg = "Unrecognized event type: ". $event;
              $msg .= ' Event ID: '. $event_id;  
                    $this->model_payment_gocoin->log($msg);  
          }
          
        } 
      }
        
    }

    public function postData() {
      //get webhook content
      $response = new stdClass();
      $post_data = file_get_contents("php://input");

      if (!$post_data) {
        $response->error = 'Request body is empty';
      }

      $post_as_json = json_decode($post_data);
      if (is_null($post_as_json)){
        $response->error = 'Request body was not valid json';
      } else {
        $response = $post_as_json;
      }
      return $response;
  }
                    
    public function sign($data, $key){
    //  $include = array('price_currency','base_price','base_price_currency','order_id','customer_name');
      $include = array('base_price','base_price_currency','order_id','customer_name');
      // $data must be an array
      if(is_array($data)) {

        $querystring = "";
        while(count($include) > 0) {
          $k = $include[0];
          if (isset($data[$k])) {
            $querystring .= $k . "=" . $data[$k] . "&";
            array_shift($include);
          }
          else {
            return false;
          }
        }

        //Strip trailing '&' and lowercase 
        $msg = substr($querystring, 0, strlen($querystring) - 1);
        $msg = strtolower($msg);

        // hash with key
        $hash = hash_hmac("sha256", $msg, $key, true);
        $encoded = base64_encode($hash);
        return $encoded;
      }
      else {
        return false;
      }
  }

}

?>