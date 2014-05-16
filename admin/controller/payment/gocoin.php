<?php 
class ControllerPaymentGocoin extends Controller {
	private $error = array(); 

	public function index() {
		$this->language->load('payment/gocoin');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		   if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			         $this->data['base'] = HTTPS_CATALOG;
		      } else {
			         $this->data['base'] =HTTP_CATALOG;
      		}	
   
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('gocoin', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		
		$this->data['entry_gocoinmerchant'] = $this->language->get('entry_gocoinmerchant');
		$this->data['entry_gocoinsecretkey'] = $this->language->get('entry_gocoinsecretkey');
		$this->data['entry_gocointoken'] = $this->language->get('entry_gocointoken');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		 
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->error['gocoinmerchant'])) {
			$this->data['error_gocoinmerchant'] = $this->error['gocoinmerchant'];
		} else {
			$this->data['error_gocoinmerchant'] = '';
		}	
		
		if (isset($this->error['gocoinsecretkey'])) {
			$this->data['error_gocoinsecretkey'] = $this->error['gocoinsecretkey'];
		} else {
			$this->data['error_gocoinsecretkey'] = '';
		}
		
		if (isset($this->error['gocointoken'])) {
			$this->data['error_gocointoken'] = $this->error['gocointoken'];
		} else {
			$this->data['error_gocointoken'] = '';
		}		
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/gocoin', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/gocoin', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['gocoin_gocoinmerchant'])) {
			$this->data['gocoin_gocoinmerchant'] = $this->request->post['gocoin_gocoinmerchant'];
		} else {
			$this->data['gocoin_gocoinmerchant'] = $this->config->get('gocoin_gocoinmerchant');
		}

		if (isset($this->request->post['gocoin_gocoinsecretkey'])) {
			$this->data['gocoin_gocoinsecretkey'] = $this->request->post['gocoin_gocoinsecretkey'];
		} else {
			$this->data['gocoin_gocoinsecretkey'] = $this->config->get('gocoin_gocoinsecretkey');
		}

		if (isset($this->request->post['gocoin_gocointoken'])) {
			$this->data['gocoin_gocointoken'] = $this->request->post['gocoin_gocointoken'];
		} else {
			$this->data['gocoin_gocointoken'] = $this->config->get('gocoin_gocointoken');
		}
		
				
		if (isset($this->request->post['gocoin_order_status_id'])) {
			$this->data['gocoin_order_status_id'] = $this->request->post['gocoin_order_status_id'];
		} else {
			$this->data['gocoin_order_status_id'] = $this->config->get('gocoin_order_status_id'); 
		}
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['gocoin_geo_zone_id'])) {
			$this->data['gocoin_geo_zone_id'] = $this->request->post['gocoin_geo_zone_id'];
		} else {
			$this->data['gocoin_geo_zone_id'] = $this->config->get('gocoin_geo_zone_id'); 
		}
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['gocoin_status'])) {
			$this->data['gocoin_status'] = $this->request->post['gocoin_status'];
		} else {
			$this->data['gocoin_status'] = $this->config->get('gocoin_status');
		}
		
		if (isset($this->request->post['gocoin_sort_order'])) {
			$this->data['gocoin_sort_order'] = $this->request->post['gocoin_sort_order'];
		} else {
			$this->data['gocoin_sort_order'] = $this->config->get('gocoin_sort_order');
		}

		$this->template = 'payment/gocoin.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/gocoin')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['gocoin_gocoinmerchant']) {
			//$this->error['gocoinmerchant'] = $this->language->get('gocoinmerchant');
		}

		if (!$this->request->post['gocoin_gocoinsecretkey']) {
			//$this->error['gocoinsecretkey'] = $this->language->get('gocoinsecretkey');
		}
		
		if (!$this->request->post['gocoin_gocointoken']) {
			//$this->error['gocointoken'] = $this->language->get('gocointoken');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>