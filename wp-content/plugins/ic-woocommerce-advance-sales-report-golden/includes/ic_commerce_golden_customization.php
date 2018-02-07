<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'IC_Commerce_Golden_Customization' ) ) {
	//require_once('ic_commerce_golden_functions.php');
	class IC_Commerce_Golden_Customization extends IC_Commerce_Golden_Functions{
		
		public $constants 				= array();
		
		function __construct($constants = array(), $admin_page = ''){
			if($admin_page == "icwoocommercegolden_details_page"){
				$this->constants = $constants;				
				$this->init();
			}
		}
		
		function init(){
			
			$admin_page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "";
			
			if($admin_page == "icwoocommercegolden_details_page"){
				add_action("ic_commerce_detail_page_search_form_before_order_by",	array($this, "ic_commerce_detail_page_search_form_before_order_by"),20);
				add_action("ic_commerce_details_page_footer_area",					array($this, "ic_commerce_details_page_footer_area"),20);
			}
		}//End Method
		
		function ic_commerce_detail_page_search_form_before_order_by($this_){
			$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "";
			$state_code = isset($_REQUEST['state_code']) ? $_REQUEST['state_code'] : "";
			
			if($page == "icwoocommercegolden_details_page"){
				?>
            	<div class="form-group">
                    <div class="FormRow FirstRow">
                        <div class="label-text"><label for="country_code"><?php _e('Country:','icwoocommerce_textdomains');?></label></div>
                        <div class="input-text">
                            <?php 
                                $country_code = $this->get_request('country_code');
                                $country_data = $this->get_paying_state('billing_country');															
                                $this->create_dropdown($country_data,"country_code[]","country_code2","Select All","country_code2",$country_code, 'object', true, 5);
                            ?>                                                        
                        </div>                                                    
                    </div>
                    <div class="FormRow ">
                        <div class="label-text"><label for="state_code"><?php _e('State:','icwoocommerce_textdomains');?></label></div>
                        <div class="input-text">
                            <?php 
                                echo '<select name="state_code[]" id="state_code2" class="state_code2" multiple="multiple" size="1"  data-size="1">';
                                if($state_code != "-1"){
                                    echo "<option value=\"{$state_code}\">{$state_code}</option>";
                                }
                                echo '</select>';
                            ?>                                                        
                        </div>                                                    
                    </div>
                 </div>
            	<div class="form-group">
                    <div class="FormRow FirstRow">
                        <div class="label-text"><label for="billing_postcode"><?php _e('Postcode(Zip):','icwoocommerce_textdomains');?></label></div>
                        <div class="input-text"><input type="text" id="billing_postcode" name="billing_postcode" class="regular-text" maxlength="100" value="<?php echo $this->get_request('billing_postcode','',true);?>" /></div>
                    </div>
                    <div class="FormRow">
                        <div class="label-text"><label for="order_meta_key"><?php _e('Min and Max By:','icwoocommerce_textdomains');?></label></div>
                        <div class="input-text">
                            <?php 
                                $order_meta_key = $this->get_request('order_meta_key');
                                $reports_data = array(
                                    "_order_total"			=>__("Order Net Amount",			'icwoocommerce_textdomains'),
                                    "_order_discount"		=>__("Order Discount Amount",		'icwoocommerce_textdomains'),
                                    "_order_shipping"		=>__("Order Shipping Amount",		'icwoocommerce_textdomains'),
                                    "_order_shipping_tax"	=>__("Order Shipping Tax Amount",	'icwoocommerce_textdomains')
                                );
                                $this->create_dropdown($reports_data,"order_meta_key[]","order_meta_key2","Select All","order_meta_key normal_view_only",$order_meta_key, 'array', false, 5);
                            ?>                                                        
                        </div>
                        <span class="detail_view_seciton normal_view_seciton_note"><?php _e("Enable this selection by uncheck 'Show Order Item Details'",'icwoocommerce_textdomains');?></span>
                    </div>
                 </div>             
             	<div class="form-group">
                    <div class="FormRow FirstRow">
                        <div class="label-text"><label for="min_amount"><?php _e('Min Amount:','icwoocommerce_textdomains');?></label></div>
                        <div class="input-text"><input type="text" id="min_amount" name="min_amount" class="regular-text normal_view_only" maxlength="100" value="<?php echo $this->get_request('min_amount','',true);?>" /></div>
                        <span class="detail_view_seciton normal_view_seciton_note"><?php _e("Enable this selection by uncheck 'Show Order Item Details'",'icwoocommerce_textdomains');?></span>
                    </div>
                    
                    <div class="FormRow">
                        <div class="label-text"><label for="max_amount"><?php _e('Max Amount:','icwoocommerce_textdomains');?></label></div>
                        <div class="input-text"><input type="text" id="max_amount" name="max_amount" class="regular-text normal_view_only" maxlength="100" value="<?php echo $this->get_request('max_amount','',true);?>" /></div>
                        <span class="detail_view_seciton normal_view_seciton_note"><?php _e("Enable this selection by uncheck 'Show Order Item Details'",'icwoocommerce_textdomains');?></span>
                    </div>
                 </div>
            	<?php
			}//End icwoocommercepremiumgold_details_page;
		}
		
		function ic_commerce_details_page_footer_area(){
			
			$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "";			
			if($page == "icwoocommercegolden_details_page"){
				?>	
				<script type="text/javascript">
					jQuery(document).ready(function($) {
					
						<?php
							$country_states = $this->get_country_state();
							$json_country_states = json_encode($country_states);
							
							$country_code = $this->get_request('country_code');						
							$state_code = $this->get_request('state_code');
						 ?>	
						 ic_commerce_vars['json_country_states'] 	= <?php echo $json_country_states;?>;
						 
						 ic_commerce_vars['country_code']			= "<?php echo $country_code	== '-1' ? '-2': $country_code;?>";
						 ic_commerce_vars['state_code']				= "<?php echo $state_code	== '-1' ? '-2': $state_code;?>";
						 
						 ic_commerce_vars['country_dropdown'] 		= $('#country_code2').attr('size');
						 
						 create_dropdown(ic_commerce_vars['json_country_states'],ic_commerce_vars['json_country_states'],"state_code2",Array(ic_commerce_vars['country_code']),Array(ic_commerce_vars['state_code']),'array');
						$('#country_code2').change(function(){
							var parent_id = $(this).val();
							if(parent_id == null) parent_id = Array("-1");
							create_dropdown(ic_commerce_vars['json_country_states'],ic_commerce_vars['json_country_states'],"state_code2",parent_id,Array('-2'),"array");
						});									
						
						jQuery("select#state_code2").attr('size',ic_commerce_vars['country_dropdown']);
						jQuery("select#state_code2").attr('data-size',ic_commerce_vars['country_dropdown']);
						
						$('#ResetForm').click(function(){					
							create_dropdown(ic_commerce_vars['json_country_states'],ic_commerce_vars['json_country_states'],"state_code2",Array(ic_commerce_vars['country_code']),Array(ic_commerce_vars['state_code']),'array');
						});				
					
					
					});				 
				 </script>
				<?php
			}//End icwoocommercepremiumgold_details_page;
		}
		
		function get_paying_state($state_key = 'billing_state',$country_key = false, $deliter = "-"){
				global $wpdb;
				if($country_key){
					//$sql = "SELECT CONCAT(billing_country.meta_value,'{$deliter}', billing_by.meta_value) as id, billing_by.meta_value as label, billing_country.meta_value as billing_country ";
					$sql = "SELECT billing_by.meta_value as id, billing_by.meta_value as label, billing_country.meta_value as billing_country ";
				}else
					$sql = "SELECT billing_by.meta_value as id, billing_by.meta_value as label ";
				
				$sql .= "
					FROM `{$wpdb->prefix}posts` AS posts
					LEFT JOIN {$wpdb->prefix}postmeta as billing_by ON billing_by.post_id=posts.ID";
				if($country_key)
					$sql .= " 
					LEFT JOIN {$wpdb->prefix}postmeta as billing_country ON billing_country.post_id=posts.ID";
				$sql .= "
					WHERE billing_by.meta_key='_{$state_key}' AND posts.post_type='shop_order'
				";
				
				if($country_key)
					$sql .= "
					AND billing_country.meta_key='_{$country_key}'";
				
				$sql .= " 
				GROUP BY billing_by.meta_value
				ORDER BY billing_by.meta_value ASC";
				
				$results	= $wpdb->get_results($sql);
				$country    = $this->get_wc_countries();//Added 20150225
				
				if($country_key){
					foreach($results as $key => $value):
							$v = $this->get_state($value->billing_country, $value->label);
							$v = trim($v);
							if(strlen($v)>0)
								$results[$key]->label = $v ." (".$value->billing_country.")";
							else
								unset($results[$key]);
					endforeach;
				}else{
					
					foreach($results as $key => $value):
							$v = isset($country->countries[$value->label]) ? $country->countries[$value->label]: $value->label;
							$v = trim($v);
							if(strlen($v)>0)
								$results[$key]->label = $v;
							else
								unset($results[$key]);
					endforeach;
				}
				return $results; 
		}
		
		function get_state($cc = NULL,$st = NULL){
			global $woocommerce;
			$state_code = $st;
			
			if(!$cc) return $state_code;
			
			$states = $this->get_wc_states($cc);//Added 20150225
			
			if(is_array($states)){
				foreach($states as $key => $value){
					if($key == $state_code)
						return $value;
				}
			}else if(empty($states)){
				return $state_code;
			}			
			return $state_code;
		}
		
		function get_country_state(){
			global $wpdb;
			$sql = "SELECT 
					billing_country.meta_value as parent_id,
					billing_state.meta_value as id,
					CONCAT(billing_country.meta_value,'-', billing_state.meta_value) billing_country_state
					
					FROM `{$wpdb->prefix}posts` AS posts
					LEFT JOIN {$wpdb->prefix}postmeta as billing_state ON billing_state.post_id=posts.ID
					LEFT JOIN {$wpdb->prefix}postmeta as billing_country ON billing_country.post_id=posts.ID
					
					WHERE 
					billing_state.meta_key='_billing_state' 
					AND billing_country.meta_key='_billing_country' 
					AND posts.post_type='shop_order'
					AND LENGTH(billing_state.meta_value) > 0
					GROUP BY billing_country_state
					ORDER BY billing_state.meta_value ASC
			";
			
			$results	= $wpdb->get_results($sql);
			
			foreach($results as $key => $value):
					$v = $this->get_state($value->parent_id, $value->id);
					$v = trim($v);
					if(strlen($v)>0)
						$results[$key]->label = $v ." (".$value->parent_id.")";
					else
						unset($results[$key]);
			endforeach;
			
			return $results;
			
			//$this->print_array($results);
		}		
		
		function get_results($sql_query = ""){
			global $wpdb;
			
			$wpdb->query("SET SQL_BIG_SELECTS=1");
			
			$results = $wpdb->get_results($sql_query);			
			
			if($wpdb->last_error){
				echo $wpdb->last_error;
				$this->print_sql($sql_query);
			}
			
			$wpdb->flush();
			
			return $results;			
		}	
    }//End Class
}//End Class