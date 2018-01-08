<?php
class WP_E_Setting extends WP_E_Model {

	private $table;

	public function __construct(){
		parent::__construct();
		$this->table = $this->prefix . "settings";
	}

	public function get($name, $user_id = null){

		if(!$user_id){
			$user_id = get_current_user_id();  // settings are user-specific
		}
		
		$setting = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT setting_value FROM " . $this->table . " WHERE user_id=%d and setting_name=%s LIMIT 1",$user_id, $name
			)
		);
		if(isset($setting)) return $setting->setting_value;
		else return false;
	}
	
	// Gets id of document page. If user exists, use that. Otherwise, get the main admin
	public function get_generic($name){
		
		//$user_id = get_current_user_id();  // settings are user unique
		//if($user_id){
		//return $this->get($name);
		//} else {
			$setting = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT setting_value FROM " . $this->table . " WHERE setting_name=%s LIMIT 1", $name
				)
			);
			
			if(isset($setting)) return $setting->setting_value;
			else return false;	
		//}
	}

	public function exists($name){
		$user_id = get_current_user_id();  //wordpress method
		return $this->wpdb->query(
			$this->wpdb->prepare(
				"SELECT setting_id FROM " . $this->table . " WHERE user_id=%d and setting_name='%s'",$user_id,$name
			)
		);
	}

	public function set($name, $value){
	
	     
		if($this->exists($name)){
			$this->update($name, $value);
		}else{
			
			$user_id = get_current_user_id();  //wordpress method

			$this->wpdb->query(
				$this->wpdb->prepare(
					"INSERT INTO " . $this->table . " VALUES(null, %d, %s, %s)", 
					$user_id, 
					$name, 
					$value
				)
			);
		}
		return $this->wpdb->insert_id;
	}
	
	/**
	*  set an array settings 
	* @param undefined $name
	* @param undefined $value
	* 
	* @return
	*/
	public function set_array($name, $value){
	
	    $new_array=array() ;
	    
	    $new_array[]=$value ;
	     
		if($this->exists($name)){
			
			$old_array =json_decode($this->get_generic($name));
			
			$old_array[]=$value ;
			
			$old_array = json_encode($old_array);
			
			$this->update_generic($name,$old_array);
			
		}else{
			
			
			$new_array= json_encode($new_array);
			
			$user_id = get_current_user_id();  //wordpress method

			$this->wpdb->query(
				$this->wpdb->prepare(
					"INSERT INTO " . $this->table . " VALUES(null, %d, %s, %s)", 
					$user_id, 
					$name, 
					$new_array
				)
			);
		}
		return $this->wpdb->insert_id;
	}
	
	
	public function set_generic($name, $value){
	
	     
		if($this->exists($name)){
			$this->update_generic($name, $value);
		}else{
			
			$user_id = get_current_user_id();  //wordpress method

			$this->wpdb->query(
				$this->wpdb->prepare(
					"INSERT INTO " . $this->table . " VALUES(null, %d, %s, %s)", 
					$user_id, 
					$name, 
					$value
				)
			);
		}
		return $this->wpdb->insert_id;
	}

	public function update($name, $value){
	
		if(!$this->exists($name)){
			$this->set($name, $value);
		}else{
			
			$user_id = get_current_user_id();  //wordpress method

			$this->wpdb->query(
				$this->wpdb->prepare(
					"UPDATE " . $this->table . " SET user_id=%d, setting_value='%s' WHERE setting_name='%s'", 
					$user_id, 
					$value, 
					$name
				)
			);
		}
		return $this->wpdb->insert_id;
	}
	
	public function update_generic($name, $value){
	
		if(!$this->exists($name)){
			$this->set($name, $value);
		}else{
			
			$user_id = get_current_user_id();  //wordpress method

			$this->wpdb->query(
				$this->wpdb->prepare(
					"UPDATE " . $this->table . " SET setting_value='%s' WHERE user_id=%d and setting_name='%s'", 
					$value, 
					$user_id, 
					$name
				)
			);
		}
		return $this->wpdb->insert_id;
	}
    
    
	
	public function delete($name){
	
		if($this->exists($name)){
			//wordpress method
		return	$this->wpdb->query(
				$this->wpdb->prepare(
					"DELETE from " . $this->table . " WHERE setting_name='%s'", 
					$name
				)
			);
		}
		 
	}
    
        
       public function delete_generic($name){
			//wordpress method
		return	$this->wpdb->query(
				$this->wpdb->prepare(
					"DELETE from " . $this->table . " WHERE setting_name='%s'", 
					$name
				)
			);	 
	}
     
    
    public function esign_super_admin(){
            
             $wp_user_id = get_current_user_id();
             
             $admin_user_id = $this->get_generic('esig_superadmin_user');
              if($wp_user_id == $admin_user_id){
                    return true ;
              } else {
                return false ; 
              }
    }
    
    public function esign_hide_esig_menus(){
		
		$hide_esign=$this->get_generic('esig_unlimited_hide_settings');// getting hide settings from settings table 
		$esig_super_admin = $this->get_generic('esig_superadmin_user'); // getting e-signature super admin 
		$wp_user_id=get_current_user_id(); // getting current wp user id 
		
		// getting esigrole class
		$esigrole= new WP_E_Esigrole();
		
		if($esig_super_admin != $wp_user_id){ // checking super and current user match
       
			if($hide_esign == "1") { // checking hide setting true or false 
				
				if($esigrole->esig_current_user_can('edit_document'))
				{	
					
					return true ;
				}
				else 
				{
					
					return false ; 
				}
			} else {
              return true ;
            }
		}
       return true ;
	}
	
	public function get_company_name()
	{
		
		 	  $defalut_page_id=$this->get_generic('default_display_page') ; 

			    $page_id = get_the_ID();
			    
			    $document_api = new WP_E_Document();

			    if($defalut_page_id==$page_id) 
			    {

			    	$doc_id=isset($_GET['csum'])?$document_api->document_id_by_csum($_GET['csum']):null;
			   		
			   		if(array_key_exists('document_id',$_GET)){ $doc_id = $_GET['document_id'];}	 
			   	}
			   	else 
			   	{
			   		
			   		// getting sad document id 
					$sad_document = new esig_sad_document();
					
					$doc_id = $sad_document->get_sad_document_id();
				}
				
				// get document creator id 
				$owner_id = $document_api->get_document_owner_id($doc_id);
				
				return $this->get('company_logo', $owner_id);
				
	}
	
	public function esig_license_expired()
	{
		// check licese key empty then return false . 
		$license_key = $this->get_generic('esig_wp_esignature_license_key') ; 
		if(!$license_key)
		{
			return false ; 
		}
		
		if(get_transient('esig-license-check'))
		{
			$license_check = get_transient('esig-license-check');
			if($license_check == "expired")
			{
				return true ;
			}
			elseif($license_check == "disabled")
			{
				return true; 
			}
                        else{
                            return false;
                        }
		}
		
		$api_params = array(
			'edd_action' 	=> 'check_license',
			'item_name'=>'WP E-Signature',
			'license' 		=> trim( $this->get_generic('esig_wp_esignature_license_key')),
			'url' 			=> 'http://www.approveme.me/',
			);
			
		 $request = wp_remote_post('http://www.approveme.me/', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		 if ( !is_wp_error( $request ) )
		 {
			$request =json_decode(wp_remote_retrieve_body( $request ));	
			set_transient( 'esig-license-check', $request->license, 12 * HOUR_IN_SECONDS );
			if($request->license =="expired")
			{
				
				return true ; 
			}
			else
			{
			 	return false;
			}
			
		}
		else
		{
			return false ;
		}
	}
        
        /**
         * return esignature default display page 
         * @return type
         */
        public function get_default_page(){
            return $this->get_generic("default_display_page");
        }
}