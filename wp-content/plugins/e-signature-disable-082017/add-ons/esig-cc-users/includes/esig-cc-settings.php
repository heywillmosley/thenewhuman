<?php

class Cc_Settings {

    const USER_INFO_META_KEY = 'esig_cc_user_information';

    public static function save_cc_information($document_id, $value) {
        WP_E_Sig()->meta->add($document_id, self::USER_INFO_META_KEY, json_encode($value));
    }

    public static function get_cc_information($document_id, $array = true) {
        return json_decode(WP_E_Sig()->meta->get($document_id, self::USER_INFO_META_KEY), $array);
    }
    
    public static function delete_cc_information($document_id){
        WP_E_Sig()->meta->delete($document_id,  self::USER_INFO_META_KEY);
    }

    public static function is_cc_enabled($document_id) {
        
        $cc_info = self::get_cc_information($document_id);
        if ($cc_info) {
            return true;
        } else {
            return false;
        }
    }

    public static function get_owner_name($user_id) {
        $owner = WP_E_Sig()->user->getUserByWPID($user_id);
        return $owner->first_name . " " . $owner->last_name;
    }

    public static function get_organization_name($user_id) {
        return WP_E_Sig()->setting->get("company_logo", $user_id);
    }

    public static function get_owner_email($user_id) {
        $owner_data = WP_E_Sig()->user->getUserByWPID($user_id);
        return $owner_data->user_email;
    }
    
    public static function prepare_cc_user_information($document_id,$POST) {

        
        $esig_cc_recipient_fnames = isset($POST['cc_recipient_fnames']) ? $POST['cc_recipient_fnames'] : NULL;
        $esig_cc_recipient_emails = isset($POST['cc_recipient_emails']) ? $POST['cc_recipient_emails'] : NULL;

        $esig_cc_user_info = array();

        for ($i = 0; count($esig_cc_recipient_emails) > $i; $i++) {
            $esig_cc_user_info[] = array(
                'first_name' => $esig_cc_recipient_fnames[$i],
                'email_address' => $esig_cc_recipient_emails[$i]
            );
        }

        self::save_cc_information($document_id, $esig_cc_user_info);

        return true;
    }

}
