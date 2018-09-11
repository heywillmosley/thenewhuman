<?php if ( ! defined( 'ABSPATH' ) ) exit;
class FooEvents_Mail_Helper {
    
    public $Config;
    
    public function __construct($config) {
        
        $this->Config = $config;

    }
    
    /**
     * Includes email template and parses PHP.
     * 
     */
    /*public function parse_email_template($template, $customerDetails) {

        ob_start();
        
        $eventPluginURL = $this->Config->eventPluginURL;
        $barcodeURL =  $this->Config->barcodeURL;
  
        //Check theme directory for template first
        if(file_exists($this->Config->emailTemplatePathThemeEmail.$template) ) {

             include($this->Config->emailTemplatePathThemeEmail.$template);

        }else {

            include($this->Config->emailTemplatePath.$template); 

        }

        return ob_get_clean();

    }*/
    
    /**
     * Includes email template and parses PHP
     * 
     * @param string $template
     * @param array $customerDetails
     * @param array $ticket
     * @return string
     */
    public function parse_email_template($template, $customerDetails = array(), $ticket) {
        
        ob_start();
	$themePacksURL = $this->Config->themePacksURL;
        include($template); 
        
        return ob_get_clean();
        
    }
    
    /**
     * Includes the ticket template and parses PHP.
     * 
     * @param array $tickets
     */
    public function parse_ticket_template($template, $ticket) {

        ob_start();
        $themePacksURL = $this->Config->themePacksURL;
        $eventPluginURL = $this->Config->eventPluginURL;
        $barcodeURL =  $this->Config->barcodeURL;
        
        $themeDetails = explode('/', $template);
        $themeDetails = array_reverse($themeDetails);
        $themeName = $themeDetails[1];
        $templateName = $themeDetails[0];
        
        //echo $this->Config->emailTemplatePathThemeEmail.$themeName.'/'.$templateName; exit();
        
        if(file_exists($this->Config->emailTemplatePathThemeEmail.$themeName.'/'.$templateName) ) {

            include($this->Config->emailTemplatePathThemeEmail.$themeName.'/'.$templateName);

        } else {
            
            include($template); 

        }

        return ob_get_clean();

    }
    
    /**
     * Sends ticket
     * 
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $headers
     */
    public function send_ticket($to, $subject, $body, $attachment = '') {
        
        $subject = html_entity_decode($subject);
        
        add_filter( 'wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type'));
        
        $globalWooCommerceEventsEmailTicketAdmin = get_option("globalWooCommerceEventsEmailTicketAdmin", true);
        
        $from       = get_option( 'woocommerce_email_from_name' ).' <'.sanitize_email( get_option( 'woocommerce_email_from_address' ) ).'>';

        $headers  = 'Content-type: text/html;charset=utf-8' . "\r\n";
        $headers .= 'From: '.$from;
        
        $sendMail = wp_mail($to, $subject, $body, $headers, $attachment);

        if($globalWooCommerceEventsEmailTicketAdmin === "yes") {
            
            $admin_email = get_option('admin_email', true);
            $sendMail = wp_mail($admin_email, $subject, $body, $headers, $attachment);
            
        }
        
        remove_filter( 'wp_mail_content_type', array($this, 'wpdocs_set_html_mail_content_type'));
        
        if($sendMail) {

            return true;
            
        } else {

            return false;
            
        }
        
    }
    
    /**
     * Sets Wordpress mail content type
     * 
     * @return string
     */
    public function wpdocs_set_html_mail_content_type() {
        return 'text/html';
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