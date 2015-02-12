<?php 
class ModelPaymentGocoin extends Model {
    
    public function getMethod($address, $total) {
    	$this->language->load('payment/gocoin');
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
   
    public function getOrderStatus($sts){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE name = '".$this->db->escape($sts)."' ");
        if(isset($query->num_rows) && $query->num_rows > 0){
            if(isset($query->row['order_status_id'])) {
                return $query->row['order_status_id'];
            }
        }
    }
    
    public function log($message) {
			$log = new Log('gocoin-payment.log');
			$log->write($message);
	}
}
?>