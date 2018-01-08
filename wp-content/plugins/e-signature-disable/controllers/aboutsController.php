<?php
/*
 * generalsController
 * @since 1.0.1
 * @author Michael Medaglia
 * For use with static pages
 */

class WP_E_aboutsController extends WP_E_appController {

	public function __construct(){
		parent::__construct();
		$this->queueScripts();
		$this->settings = new WP_E_Setting();
		 $this->document =new WP_E_Document();  
		  $this->user= new WP_E_User();
		  $this->general = new WP_E_General();
	}
	
	public function calling_class(){
		return get_class();
	}
	
	private function queueScripts(){
	
		wp_enqueue_style('esig-about-style', ESIGN_ASSETS_DIR_URI  . "/css/esign.about.css");
		wp_enqueue_script('esig-system-js', ESIGN_ASSETS_DIR_URI  . "/js/esig_system_js.js");
		
	}
	
	public function index()
	{
    
     
	  $template_data=array (
	   "version_no" => esig_plugin_name_get_version(),
	   "ESIGN_ASSETS_URL" => ESIGN_ASSETS_DIR_URI ,
	   );
       
      
	   $this->view->render('about','about',$template_data);
		
	} 
	
	public function systeminfo($data_return=false)
	{
	  global $wpdb;
	  
	  $template_data=array ();
	   
	  $template_data['home_url']=home_url() ;
	  
	  $template_data['site_url']=site_url() ;
	  $template_data['e-sign_version']=esig_plugin_name_get_version();
	  $template_data['e-sign_database_version']=get_option( "esig_db_version"); 	   	  
	  $template_data['wp_version']=get_bloginfo('version');
	  $template_data['wp_multisite_enabled']=is_multisite() ? "Yes" : "No";  	  
	  $template_data['web_server_info']=esc_html( $_SERVER['SERVER_SOFTWARE'] ); 
	  $template_data['php_version']=function_exists( 'phpversion' )? esc_html( phpversion() ) : "" ;
	  $template_data['mysql_version']=$wpdb->db_version();
	  
	  $memory = WP_MEMORY_LIMIT;
	

				if ( $memory < 67108864 ) {
					$memorytxt= '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 64MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'esig' ), $memory, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
				} else {
					$memorytxt = '<mark class="yes">' . $memory . '</mark>';
				}
	  $template_data['wp_memory_limit']=$memorytxt;
	  
	  if(defined('WP_DEBUG') && WP_DEBUG )
		$template_data['wp_debug_mode'] ='<mark class="yes">' . __( 'Yes', 'esig' ) . '</mark>';
	  else
		$template_data['wp_debug_mode'] ='<mark class="no">'. __( 'No', 'esig' ) . '</mark>';
	  if ( defined( 'WPLANG' ) && WPLANG ) $template_data['wp_language']=WPLANG; else  $template_data['wp_language']="default language"; 
	   
		$template_data['wp_max_upload_size']=size_format( wp_max_upload_size() );		
	   
	   $template_data['php_post_max_size']=function_exists( 'ini_get' )? size_format( ini_get('post_max_size')) : "" ;
	   $template_data['php_time_limit']=function_exists( 'ini_get' )? ini_get('max_execution_time') : "" ;
	   $template_data['php_max_input_vars']=function_exists( 'ini_get' )? ini_get('max_input_vars') : "" ;
	   $template_data['suhosin_installed']= extension_loaded( 'suhosin' ) ? 'Yes' : 'No' ;
	   
	   if (@fopen( ESIGN_PLUGIN_PATH . '/changelog.txt', 'a' ) )
					$template_data['e-sign_logging']= '<mark class="yes">'. __( ' Log directory is writable', 'esig' ) . '</mark>';
				else
					$template_data['e-sign_logging']= '<mark class="error">'. __( 'Log directory  is not writable. Logging will not be possible', 'esig' ) . '</mark>';
	   
	   $default_timezone = date_default_timezone_get();
				if ( 'UTC' !== $default_timezone ) {
					$template_data['default_timezone']='<mark class="error">'. sprintf( __( 'Default timezone is %s - it should be UTC ', 'esig' ), $default_timezone ) . '</mark>';
				} else {
					$template_data['default_timezone']='<mark class="yes">' . sprintf( __( 'Default timezone is %s', 'esig' ), $default_timezone ).'</mark>';
				}
	   
	   $template_data['fsockopen_curl']=function_exists('curl_version')? "Yes" : "No" ;
	   $template_data['soap_client']=class_exists("SOAPClient")? '<mark class="yes">'. __( 'Yes', 'esig' ). '</mark>' : '<mark class="no">'. __( 'No', 'esig' ).'</mark>';
	   

	   $remote_post = $this->setting->get_generic('esig_wp_esignature_license_active') ; 
	   $template_data['wp_remote_post']= $remote_post=="valid" ? '<mark class="yes">'. __( 'Yes', 'esig' ) . '</mark>' : '<mark class="no">' . __( 'No', 'esig' ) .'</mark>';	   
	   
	   $locale = localeconv();

			foreach ( $locale as $key => $val ){
				if ( in_array( $key, array( 'decimal_point', 'mon_decimal_point', 'thousands_sep', 'mon_thousands_sep' ) ) )
							 $template_data[$key]= $val;
			}
	   
	   
	   $active_plugins = (array) get_option( 'active_plugins', array() );

				if ( is_multisite() )
					  $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	   
	   foreach ( $active_plugins as $plugin ) {

					$plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
					$dirname        = dirname( $plugin );
					$version_string = '';

					if ( ! empty( $plugin_data['Name'] ) ) {

						// link the plugin name to the plugin url if available
						$plugin_name = $plugin_data['Name'];
						if ( ! empty( $plugin_data['PluginURI'] ) ) {
							$plugin_name = '<a href="' . esc_url( $plugin_data['PluginURI'] ) . '" title="'.__( 'Visit plugin homepage', 'esig' ).'">' . $plugin_name . '</a>';
						}

						

						$esign_plugins[] = $plugin_name . ' ' . __( 'by', 'esig' ) . ' ' . $plugin_data['Author'] . ' ' . __( 'version', 'esig' ) . ' ' . $plugin_data['Version'] . $version_string;

					}
				}
				
	   if ( sizeof( $esign_plugins ) == 0 )
					$template_data['installed_plugins'] = '-';
				else
					$template_data['installed_plugins']= implode( '<br/>', $esign_plugins );
	   
	   
	   $force_ssl= $this->setting->get_generic('force_ssl_enabled') ; 
	   $template_data['force_ssl']= $force_ssl ? "Yes" : "No" ;	   
	   
	   // esign pages start here 
	   $esign_pages=array(); 
	   $pageID = $this->setting->get_generic('default_display_page');
	   $core_html='' ;  
	   if(!$this->document->document_document_page_exists($pageID)){
			
			
			$page_data = get_page($pageID);
			if($page_data) :
                 if (function_exists('has_shortcode'))
                {   
		            if(has_shortcode($page_data->post_content, 'wp_e_signature' ))
		             {
				            $page_title=$page_data->post_title ; 
				            $permalink = get_permalink($page_data->ID );
				            //$permalink="post.php?post={$pageID}&action=edit";
			                $core_html .=' <tr><td>' . sprintf( __( '(Core) %s', 'esig' ), $page_title ).'</td><td><mark class="yes">'. $permalink .'</mark></td></tr> ' ; 
		             }
                 }else{
                            $core_html .=' <tr><td>' . sprintf( __( '(Core) %s', 'esig' ), $page_title ).'</td><td><mark class="yes">'. $permalink .'</mark></td></tr> ' ; 
                 }
		 endif ;
		} 
		global $wpdb;
		$table =  $wpdb->prefix . 'esign_documents_stand_alone_docs';
			$default_page=array();
			if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
			$sad_page_id= $wpdb->get_col("SELECT page_id FROM {$table}");
			
			 foreach($sad_page_id as $page_id) {
			   
			 $page_data = get_page($page_id);
			if($page_data) :
                if (function_exists('has_shortcode'))
                {
		            if(has_shortcode($page_data->post_content, 'wp_e_signature_sad' ))
		             {
				            $page_title=$page_data->post_title ;
				            $permalink = get_permalink($page_data->ID);				
				            //$permalink="post.php?post={$page_id}&action=edit";
			            $core_html .=' <tr><td>' . sprintf( __( '(Sad) %s', 'esig' ), $page_title ).'</td><td><mark class="yes">'. $permalink .'</mark></td></tr> ' ; 
		             }
                 }
		    endif ;
		 
			  }
			
			}
		
		$template_data['esign_pages']= $core_html ; 
		
		// theme start here 
		$active_theme = wp_get_theme();
		
		$template_data['theme_name']= $active_theme->Name ;
		$template_data['theme_version']= $active_theme->Version;
		$template_data['author_url']= $active_theme->{'Author URI'};		
		
		// php mcrypt extension
			$mcry='';
		if (!function_exists('mcrypt_create_iv')){
					$mcry .='<mark class="no">'. __('No','esig') .'</mark>';
			}
			else {
				$mcry .='<mark class="yes">' . __('Yes','esig') . '</mark>';
				}
		$template_data['mcrypt_extension']=$mcry ; 
		// templates start here 
		if($data_return){
                return $template_data;
        }
	   $this->view->render('about','systeminfo',$template_data);
       
	} 
		
}

