<?php 
class ModelPaymentGocoin extends Model {
    
    public function getDbTable() {
        $sql="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."gocoin_ipn` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `order_id` int(10) unsigned DEFAULT NULL,
                `invoice_id` varchar(200) NOT NULL,
                `url` varchar(400) NOT NULL,
                `status` varchar(100) NOT NULL,
                `btc_price` decimal(16,8) NOT NULL,
                `price` decimal(16,8) NOT NULL,
                `currency` varchar(10) NOT NULL,
                `currency_type` varchar(10) NOT NULL,
                `invoice_time` datetime NOT NULL,
                `expiration_time` datetime NOT NULL,
                `updated_time` datetime NOT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        
       	$query = $this->db->query($sql); 
    }
    
  	public function getMethod($address, $total) {
        
		$this->language->load('payment/gocoin');
		$this->getDbTable(); // Create gocoin_ipn Table
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('gocoin_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if (!$this->config->get('gocoin_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'gocoin',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('gocoin_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
   
    public function addTransaction($type = 'payment', $details){
      return $query = $this->db->query("
      INSERT INTO ".DB_PREFIX."gocoin_ipn (  order_id, invoice_id, url, status, btc_price,
      price, currency, currency_type, invoice_time, expiration_time, updated_time)
      VALUES ( 
          '".$details['order_id']."',
          '".$details['invoice_id']."',
          '".$details['url']."',
          '".$details['status']."',
          '".$details['btc_price']."',
          '".$details['price']."',
          '".$details['currency']."',
          '".$details['currency_type']."',
          '".$details['invoice_time']."',
          '".$details['expiration_time']."',
          '".$details['updated_time']."' )");
    }
    
    public function getOrderStatus($sts){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE name = '".$sts."' ");
        if(isset($query->num_rows) && $query->num_rows > 0){
            if(isset($query->row['order_status_id'])) {
                return $query->row['order_status_id'];
            }
        }
    }
    
}
?>