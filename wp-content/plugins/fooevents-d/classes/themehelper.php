<?php if ( ! defined( 'ABSPATH' ) ) exit;
class FooEvents_Theme_Helper {
    
    public $Config;
    public $MailHelper;
    private $BarcodeHelper;
 
    public function __construct($Config) {
        
        $this->Config = $Config;
        add_action( 'admin_menu',  array( $this, 'add_menu_item' ));
        
        //MailHelper
        require_once($this->Config->classPath.'mailhelper.php');
        $this->MailHelper = new FooEvents_Mail_Helper($this->Config);

    }
    
    /**
     * Add admin ticket themes menu item
     * 
     */
    public function add_menu_item() {
        
        add_submenu_page( 'edit.php?post_type=event_magic_tickets', 'Ticket Themes', 'Ticket Themes', 'edit_posts', 'fooevents-ticket-themes', array( $this, 'display_page' ) );

    }
    
    /**
     * Display ticket themes page
     * 
     */
    public function display_page() {

        if(!empty($_POST['fooevents-theme-viewer-preview-input'])) {
            
            $this->send_preview($_POST);
            
        }
        
        if(!empty($_FILES['fooevents-theme-viewer-upload-file'])) {
            
            $this->upload_themes($_FILES);
            
        }
        
        $themes = $this->get_ticket_themes();
        
        $user = wp_get_current_user();
        $user_email = $user->user_email;
        
        include($this->Config->templatePath.'ticketthemesviewer.php'); 
        
    }
    
    /**
     * Upload new ticket theme 
     * 
     * @param array $form
     */
    public function upload_themes($form) {

        if(!empty($form['fooevents-theme-viewer-upload-file'])) {

            $type = $_FILES["fooevents-theme-viewer-upload-file"]["type"];
            
            $accepted = false;
            
            $acceptedFileTypes = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
            foreach($acceptedFileTypes as $fileType) {
                
                if($fileType == $type) {
                        $accepted = true;
                        break;
                } 
                
            }

            if($accepted) {
                
                $filename = explode(".", $_FILES["fooevents-theme-viewer-upload-file"]["name"]);

                $path = $this->Config->themePacksPath.$_FILES["fooevents-theme-viewer-upload-file"]["name"];

                $alreadyExists = false;
                if(file_exists($this->Config->themePacksPath.$filename[0])) {
                    
                    $alreadyExists = true;
                    $this->output_notices(array(__( 'Theme already exists.', 'woocommerce-events' )));
                    
                }
                
                if(!$alreadyExists) {
                
                    move_uploaded_file($_FILES["fooevents-theme-viewer-upload-file"]["tmp_name"], $path);

                    if(file_exists($path)) {

                        $zip = new ZipArchive();
                        $status = $zip->open($path);

                        if ($status === true) {

                            $zip->extractTo($this->Config->themePacksPath); 
                            $zip->close();
                            $theme_path = $this->Config->themePacksPath.$filename[0];
                            
                            if(file_exists($theme_path.'/header.php') && file_exists($theme_path.'/footer.php') && file_exists($theme_path.'/ticket.php')) {

                                $this->output_notices(array(__( 'Theme has been uploaded.', 'woocommerce-events' )));

                            } else {
                                
                                $this->output_notices(array(__( 'File does not contain valid theme files.', 'woocommerce-events' )));
                                //rmdir($theme_path);
                                
                            }
                            
                            unlink($path);

                        }

                    }
                
                }
                
            } else {
                
                $this->output_notices(array(__( 'Please upload valid .zip file', 'woocommerce-events' )));
                
            }
            
            
        }
        
    }
    
    /**
     * Send ticket theme preview
     * 
     * @param array $form
     */
    public function send_preview($form) {
        
        if(!empty($form['fooevents-theme-viewer-preview-input']) && !empty($form['fooevents-theme-viewer-preview-path'])) {

            $theme = $form['fooevents-theme-viewer-preview-path'];
            $sendTo = $form['fooevents-theme-viewer-preview-input'];
            $themeName = $form['fooevents-theme-viewer-preview-theme-name'];
            
            $ticket = $this->get_demo_ticket();
            
            $header = $this->MailHelper->parse_email_template($theme.'/header.php', array(), $ticket); 
            $footer = $this->MailHelper->parse_email_template($theme.'/footer.php', array(), $ticket);
            $body = $this->MailHelper->parse_ticket_template($theme.'/ticket.php', $ticket);
            
            $subject = $themeName.": Preview Ticket";
            
            $mailStatus = $this->MailHelper->send_ticket($sendTo, $subject, $header.$body.$footer);
            
            $this->output_notices(array(__( 'Preview ticket has been sent', 'woocommerce-events' )));

        }
        
    }
    
    /**
     * Get demo details for ticket preview
     * 
     * @return array
     */
    private function get_demo_ticket() {
        
        $ticket = array(
            'WooCommerceEventsDate'     => '24-11-2020',
            'WooCommerceEventsVariations' => '',
            'WooCommerceEventsVariationID' => '',
            'fooevents_custom_attendee_fields_options' => 'Shirt size: L',
            'WooCommerceEventsEvent' => 'Event',
            'WooCommerceEventsHour' => '13',
            'WooCommerceEventsMinutes' => '00',
            'WooCommerceEventsPeriod' => '',
            'WooCommerceEventsHourEnd' => '14',
            'WooCommerceEventsMinutesEnd' => '00',
            'WooCommerceEventsEndPeriod' => '',
            'WooCommerceEventsLocation' => __( 'Local Stadium', 'woocommerce-events' ),
            'WooCommerceEventsTicketLogo' => '',
            'WooCommerceEventsTicketHeaderImage' => '',
            'WooCommerceEventsSupportContact' => '0841111111',
            'WooCommerceEventsTicketBackgroundColor' => '#050505',
            'WooCommerceEventsTicketButtonColor' => '#55AF71',
            'WooCommerceEventsTicketTextColor' => '#FFFFFF',
            'WooCommerceEventsTicketPurchaserDetails' => 'on', 
            'WooCommerceEventsTicketAddCalendar' => 'on',
            'WooCommerceEventsTicketDisplayDateTime' => 'on',
            'WooCommerceEventsTicketDisplayBarcode' => 'on',
            'WooCommerceEventsTicketText' => 'This is preview text',
            'WooCommerceEventsDirections' => 'These are preview directions',
            'WooCommerceEventsTicketDisplayPrice' => 'on',
            'WooCommerceEventsTicketType' => 'Early Bird',
            'WooCommerceEventsProductID' => '',
            'WooCommerceEventsTicketID' => '111111111',
            'WooCommerceEventsOrderID' => '',
            'name' => __( 'Preview Event', 'woocommerce-events' ),
            'cancelLink' => '',
            'WooCommerceEventsAttendeeTelephone' => '',
            'WooCommerceEventsAttendeeCompany' => '',
            'WooCommerceEventsAttendeeDesignation' => '',
            'WooCommerceEventsAttendeeEmail' => '',
            'customerFirstName' => __( 'John', 'woocommerce-events' ),
            'customerLastName' => __( 'Doe', 'woocommerce-events' ),
            'customerEmail' => '',
            'FooEventsTicketFooterText' => '',
            'price' => '$99.00'
        );
        
        return $ticket;
        
    }
    
     /**
     * Gets a list of FooEvents Ticket themes
     * 
     */
    public function get_ticket_themes()  {
        
        $valid_themes = array();
        
        foreach (new DirectoryIterator($this->Config->themePacksPath) as $file) {
            
            if ($file->isDir() && !$file->isDot()) {
                
                $theme_name = $file->getFilename();
                
                $theme_path = $file->getPath();
                $theme_path = $theme_path.'/'.$theme_name;
                
                $theme_name_pretty = str_replace('_', " ", $theme_name);
                $theme_name_prep = ucwords($theme_name_pretty);
                
                if(file_exists($theme_path.'/header.php') && file_exists($theme_path.'/footer.php') && file_exists($theme_path.'/ticket.php')) {
                    
                    $valid_themes[$theme_name_prep]['path'] = $theme_path;
                    $theme_url = $this->Config->themePacksURL.$theme_name;
                    $valid_themes[$theme_name_prep]['url'] = $theme_url;
                    
                    if(file_exists($theme_path.'/preview.jpg')) {   
                        
                        $valid_themes[$theme_name_prep]['preview'] = $theme_url.'/preview.jpg';
                        
                    } else {
                        
                        $valid_themes[$theme_name_prep]['preview'] = $this->Config->eventPluginURL.'images/no-preview.jpg';
                        
                    }
                    
                    $valid_themes[$theme_name_prep]['file_name'] = $file->getFilename();
                    
                }

            }
            
        }

        return $valid_themes;
        
    }
    
    /**
     * Outputs notices to screen.
     * 
     * @param array $notices
     */
    private function output_notices($notices) {

        foreach ($notices as $notice) {

                echo "<div class='updated'><p>$notice</p></div>";

        }

    }
    
}    