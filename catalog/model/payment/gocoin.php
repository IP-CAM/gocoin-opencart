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
                `fingerprint` varchar(250) NOT NULL,
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
      price, currency, currency_type, invoice_time, expiration_time, updated_time,fingerprint)
      VALUES ( 
          '".$this->db->escape($details['order_id'])."',
          '".$this->db->escape($details['invoice_id'])."',
          '".$this->db->escape($details['url'])."',
          '".$this->db->escape($details['status'])."',
          '".$this->db->escape($details['btc_price'])."',
          '".$this->db->escape($details['price'])."',
          '".$this->db->escape($details['currency'])."',
          '".$this->db->escape($details['currency_type'])."',
          '".$this->db->escape($details['invoice_time'])."',
          '".$this->db->escape($details['expiration_time'])."',
          '".$this->db->escape($details['updated_time'])."',
          '".$this->db->escape($details['fingerprint'])."' )");
    }
    
    public function getFPStatus($details){
        $query = $this->db->query("SELECT invoice_id FROM " . DB_PREFIX . "gocoin_ipn where invoice_id = '".$this->db->escape($details['invoice_id'])."' and   
            fingerprint = '".$this->db->escape($details['fingerprint'])."'       
         ");
        if(isset($query->num_rows) && $query->num_rows > 0){
            if(isset($query->row['invoice_id'])) {
                return $query->row['invoice_id'];
            }
        }
    }
    
    function updateTransaction($type = 'payment', $details) {
         return $query = $this->db->query("
        update   ".DB_PREFIX."gocoin_ipn set 
            status           = '".$this->db->escape($details['status'])."',   
            updated_time     = '".$this->db->escape($details['updated_time'])."'      where
            invoice_id       = '".$this->db->escape($details['invoice_id'])."' and   
            order_id         = '".$this->db->escape($details['order_id'])."'       
         ");

    }
    
    public function getOrderStatus($sts){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE name = '".$this->db->escape($sts)."' ");
        if(isset($query->num_rows) && $query->num_rows > 0){
            if(isset($query->row['order_status_id'])) {
                return $query->row['order_status_id'];
            }
        }
    }
    
}
?>