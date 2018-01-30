<?php

class USIN_Woocommerce_Billing_Countries_Loader extends USIN_Standard_Report_Loader {

	protected $countries = null;
	
	public function load_data(){
	
		$data = $this->load_post_meta_data('_billing_country', true);

		foreach($data as &$row){
			$row->label = $this->get_country_name($row->label);
		}

		return $data;
	}

	protected function get_country_name($code){
		if($this->countries === null){
			$this->countries = array();
			if(function_exists('WC')){
				$wc = WC();
				if(property_exists($wc, 'countries') && method_exists($wc->countries, 'get_countries')){
					$this->countries = $wc->countries->countries;
				}
			}
		}

		if(isset($this->countries[$code])){
			return html_entity_decode($this->countries[$code]);
		}

		return $code;
	}

}