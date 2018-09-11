<?php if ( ! defined( 'ABSPATH' ) ) exit;

class FooEvents_Config {
	
    public $pluginVersion;
    public $pluginDirectory;
    public $path;
    public $classPath;
    public $templatePath; 
    public $barcodePath;
    public $pdfTicketPath;
    public $pdfTicketURL;
    public $scriptsPath;
    public $stylesPath;
    public $emailTemplatePath;
    public $pluginURL;
    public $eventPluginURL;
    public $clientMode;
    public $salt;
	
        /**
         * Initialize configuration variables to be used as object.
         * 
         */
	public function __construct() {
            
            /*error_reporting(E_ALL);
            ini_set('display_errors', 1);*/

            $upload_dir = wp_upload_dir();
            
            $this->pluginVersion = '1.7.12';
            $this->pluginDirectory = 'fooevents';
            $this->path = plugin_dir_path( __FILE__ );
            $this->pluginFile = $this->path.'fooevents.php';
            $this->pluginURL = plugin_dir_url(__FILE__);
            $this->classPath = plugin_dir_path( __FILE__ ).'classes/';
            $this->templatePath = plugin_dir_path( __FILE__ ).'templates/';
            $this->uploadsDirPath = $upload_dir['basedir'];
            $this->uploadsPath = $upload_dir['basedir'].'/fooevents/';
            $this->barcodePath = $upload_dir['basedir'].'/fooevents/barcodes/';
            $this->barcodeURL = $upload_dir['baseurl'].'/fooevents/barcodes/';
            $this->themePacksPath = $upload_dir['basedir'].'/fooevents/themes/';
            $this->themePacksURL = $upload_dir['baseurl'].'/fooevents/themes/';
            $this->pdfTicketPath = $upload_dir['basedir'].'/fooevents/pdftickets/'; 
            $this->pdfTicketURL = $upload_dir['baseurl'].'/fooevents/pdftickets/'; 
            $this->emailTemplatePath = plugin_dir_path( __FILE__ ).'templates/email/';
            $this->emailTemplatePathThemeEmail = get_stylesheet_directory().'/'.$this->pluginDirectory.'/themes/';
            $this->emailTemplatePathTheme = get_stylesheet_directory().'/'.$this->pluginDirectory.'/templates/';
            $this->scriptsPath = plugin_dir_url(__FILE__) .'js/';
            $this->stylesPath = plugin_dir_url(__FILE__) .'css/';
            $this->eventPluginURL = plugins_url().'/'.$this->pluginDirectory.'/';
            $this->clientMode = false;
            $this->salt = get_option('woocommerce_events_do_salt');
            
	}

}