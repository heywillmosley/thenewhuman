<?php if ( ! defined( 'ABSPATH' ) ) exit;
class FooEvents_Update_Helper {
    
    private $Config;
    private $slug;
    private $pluginData;
    private $fooeventsAPIKey;
    private $envatoAPIKey;
    private $homeURL;
    private $fooeventsReponse;
    
    
    public function __construct($Config) {
        
        $this->Config = $Config;
        
        $this->fooeventsAPIKey = get_option('globalWooCommerceEventsAPIKey', true);
        $this->envatoAPIKey = get_option('globalWooCommerceEnvatoAPIKey', true);
        $this->homeURL = get_home_url();

        add_filter("pre_set_site_transient_update_plugins", array( $this, "set_transitent"));

        add_filter("plugins_api", array( $this, "set_plugin_info"), 10, 3 );
        
        add_action('in_plugin_update_message-fooevents/fooevents.php', array($this, 'show_upgrade_notification'), 10, 2);

    }

    public function set_transitent($transient) {
        
        /*if (empty($transient->checked)) {
            return $transient;
        }*/
        
        $this->init_plugin_data();
        $this->get_latest_plugin_details_fooevents();

        if(isset($this->fooeventsReponse['update_available']) && $this->fooeventsReponse['update_available'] == 'yes') {
            
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $this->fooeventsReponse['version'];
            $obj->url = $this->fooeventsReponse['url'];
            $obj->package = $this->fooeventsReponse['url'];
            /*$obj->sections = array(
                'description' => 'The new version of the Auto-Update plugin',
                'another_section' => 'This is another section',
                'changelog' => 'Some new features'
              );*/
            $transient->response[$this->slug] = $obj;
            
        }

        return $transient;
        
    }
    
    public function init_plugin_data() {

        $this->slug = plugin_basename($this->Config->pluginFile);
        $this->pluginData = get_plugin_data($this->Config->pluginFile);
        
    }

    private function get_latest_plugin_details_fooevents() {
        
        if (empty($this->fooeventsAPIKey) && empty($this->envatoAPIKey)) {
            return;
        }

        if (!empty($this->fooeventsReponse)) {
            return;
        }
        
        if(empty($this->pluginData)) {
            
            $this->pluginData = get_plugin_data($this->Config->pluginFile);
            
        }

        $url = 'https://www.fooevents.com/?rest_route=/fooevents/check_api';

        $params = array(
            "api" => $this->fooeventsAPIKey,
            "envato_api" => $this->envatoAPIKey,
            "plugin_name" => $this->pluginData['Name'],
            "version"   => $this->pluginData['Version'],
            'home_url' => $this->homeURL
        ); 
        
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );

        $this->fooeventsReponse = json_decode($response, true);
        
       
    }
    
    public function set_plugin_info($false, $action, $response) {
        
        if ( empty( $response->slug ) || $response->slug != $this->slug ) {
            return false;
        }
        
        $this->init_plugin_data();
        
        $response->sections = array(
            'description' => $this->pluginData['Name'],
        );
        
        $response->requires = '';
        
        $response->tested = '';
        
        $response->name = $this->pluginData['Name'];
        
        return $response;
        
    }
    
    public function show_upgrade_notification($currentPluginMetadata, $newPluginMetadata) {

        if(empty($this->fooeventsReponse)) {
            
            $this->get_latest_plugin_details_fooevents();
            
        }
        
        if(!empty($this->fooeventsReponse)) {

            if($this->fooeventsReponse['status'] == 'error') {
                
                echo '<p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>Important Upgrade Notice:</strong> ';
                echo $this->fooeventsReponse['message'];
                echo '</p>';
                
            }

            if($this->fooeventsReponse['status'] == 'success') {
                
                echo '<p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>Important Upgrade Notice:</strong> ';
                echo 'Please backup your files and database before updating your site.';
                echo '</p>';
                
            }
            
        }
        
    }

}