<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Esign_licenses extends WP_E_Model {
    
    const  APP_URL = "http://www.approveme.me/";

    public function __construct() {
        parent::__construct();
    }

    public static function is_license_active() {

        $result= self::check_license();

        if ($result->license == "valid") {
            return true;
        } else {
            return false;
        }
    }
    
    public static function is_valid_license(){
        
             if(WP_E_Sig()->setting->get_generic("esig_wp_esignature_license_active") == "valid" && self::get_license_type() == "business-license"){
                 return true ; 
             }
             return false;
    }

    public static function get_license_key() {
        return WP_E_Sig()->setting->get_generic("esig_wp_esignature_license_key");
    }
    
    public static function get_license_name(){
        return WP_E_Sig()->setting->get_generic("esig_wp_esignature_license_name");
    }

    public static function get_license_type() {
        return WP_E_Sig()->setting->get_generic("esig_wp_esignature_license_type");
    }

    public static function get_expire_date() {
        return WP_E_Sig()->setting->get_generic("esig_wp_esignature_license_expires");
    }

    public static function check_license() {

        $api_params = array(
            'edd_action' => 'check_license',
            'item_id' => 2660,
            'item_name' => self::get_license_name(),
            'license' => self::get_license_key(),
            'url' => home_url(),
        );

        $request = wp_remote_post(self::APP_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
        
        if ( !is_wp_error( $request ) ) {
            $request = json_decode( wp_remote_retrieve_body( $request ) );
        }
        else{
            return false;
        }
       
        return $request ; 
    }
    
    public static function esig_super_admin($echo=false){
         $super_admin_id=  WP_E_Sig()->user->esig_get_super_admin_id(); 
         $user_details=  WP_E_Sig()->user->getUserByWPID($super_admin_id); 
         if($echo)
            echo $user_details->first_name;
         
         return $user_details->first_name;
    }
    
    public static function get_strip_license_key($status){
        
          if($status == "valid"){
              return str_repeat('*', (strlen(self::get_license_key()) - 4)) . substr(self::get_license_key(),-4,4);
          }
          else
          {
              return false;
          }
    }
    
    public static function is_readonly($status){
         if($status == "valid"){
             return "readonly" ;
         }
         else{
             return false ; 
         }
    }
    
    public static function get_renew_button(){
        
        return '<a href="https://www.approveme.me/checkout/?edd_license_key='. self::get_license_key() .'&&download_id=2660" class="esig-btn-pro esig-renew-button" target="_blank">Renew Your License</a>';
        
    }
    
    public static function get_license_form($result){
        
        $item_plugshortname=str_replace("WP E-Signature ", "",$result->item_name);
        $item_pluginname = 'esig_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower($item_plugshortname ) ) );
        
        $html ='' ; 
        
         $button_text ='' ;
        
         if($result->license == "valid"){
             
        $html .='<tr>
		<th><label for="license_key" id="license_key_label"> License Status</label></th>
		<td> <span class="license-active-status"> ACTIVE </span> &nbsp;&nbsp;&nbsp;–&nbsp;&nbsp; You are receiving updates. </td><tr>'; 
        
        //$html .='<tr><td colspan="2"><span class="license-active-msg"> active msg</span> </td></tr>' ; 
          $button_text =' <input type="submit" class="button-appme button" name="esig_wp_esignature_license_key_deactivate" value="Deactivate License">';
      
         }
         elseif($result->license =="expired"){
             
             $html .='<tr><td colspan="2"><div class="esig-add-on-block esig-pro-pack open">

                <h3>URGENT: Your License has Expired!</h3>
                <p style="display:block;"> A valid WP E-Signature license is required for access to critical updates and support. Without a license you are putting you and your signers at risk. To protect yourself and your documents please update your license.</p>
                '. self::get_renew_button() .'
            </div><div class="license-expired-msg"> <strong>Your are not receiving critical updates.  WP E-Signature license expired on '. date(get_option( 'date_format' ),  strtotime($result->expires)) .'.</strong></div></td></tr>' ; 
             
              $html .='<tr>
		<th><label for="license_key" id="license_key_label"> License Status</label></th>
		<td> <span class="license-inactive-status">'. strtoupper($result->license) .'</span> - You are <strong>not</strong> receiving updates and your signers are currently at risk!</td><tr>';

             
              
             $button_text =' <input type="submit" class="button-appme button" name="esig_wp_esignature_license_key_activate" value="Activate License">';
         }
         else{
             
              $html .='<tr><td colspan="2"><div class="license-inactive-msg"> Please enter your valid <a href="https://www.approveme.me">WP E-Signature</a> license below.  If you forgot your license you can login to <a href="https://www.approveme.me/profile">your account here</a> or <a href="https://www.approveme.me/#pricingPlans">purchase a license</a> here.</div></td></tr>' ; 
 
              $html .='<tr>
		<th><label for="license_key" id="license_key_label"> License Status</label></th>
		<td> <span class="license-inactive-status">'. strtoupper($result->license) .'</span> - You are not receiving updates and your signers are currently at risk!</td><tr>';              

        
                $button_text =' <input type="submit" class="button-appme button" name="esig_wp_esignature_license_key_activate" value="Activate License">';
         }
        
        
        $html .='<tr class="esig-settings-wrap">
		<th><label for="license_key" id="license_key_label"> License Key <span class="description"> (required)</span></label></th>
		<td><input type="text" name="esig_wp_esignature_license_key' . '" id="first_name" value="'. self::get_strip_license_key($result->license)  .'" class="regular-text" '. self::is_readonly($result->license) .' /> '. $button_text .'</tr>'; 
	
        if($result->license == "valid"){
            $html .= sprintf(__('<tr><td colspan="3">Your e-signature license will expire on %s </td></tr>','esign'),$result->expires);
        }
        
        echo $html;
    }

}
