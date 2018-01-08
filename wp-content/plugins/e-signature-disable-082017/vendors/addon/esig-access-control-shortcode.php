<?php

/**
 *
 * @package ESIG_ACCESS_CONTROL_Shortcode 
 * @author  Abu Shoaib <abushoaib73@gmail.com>
 */
if (!class_exists('ESIG_ACCESS_CONTROL_Shortcode')) :

    class ESIG_ACCESS_CONTROL_Shortcode {

        public function __construct() {

            add_action('wp_enqueue_scripts', array($this, 'enqueue_admin_styles'));
            add_shortcode("esig-doc-dashboard", array($this, "esig_doc_dashboard"));
            add_action('esig_document_complate', array($this, 'esig_sad_document_complate'), 9, 1);
        }

        public function enqueue_admin_styles() {

            wp_register_style('esig-access-control-bootstrap-styles', ESIGN_ASSETS_DIR_URI . '/css/bootstrap.min.css', array(),false);
            wp_register_style('esig-access-control-css-styles', ESIGN_ASSETS_DIR_URI . '/css/esig-access-control.css', array(),false);
            wp_register_style('esig-icon-css-styles', ESIGN_ASSETS_DIR_URI . '/css/esig-icon.css', array(),false);
            wp_register_script("esig-access-control-bootstrap-js", ESIGN_ASSETS_DIR_URI . '/js/bootstrap/bootstrap.min.js', array('jquery'), '',true);
        }

        public function esig_sad_document_complate($args) {

            $old_document_id = $args['sad_doc_id'];

            $new_document_id = $args['invitation']->document_id;

            $wp_user_id = get_current_user_id();

            $api = new WP_E_Api();

            $old_access_control_settings = $api->meta->get($old_document_id, "esig_wpaccess_control");
            if (!empty($old_access_control_settings)) {

                $api->meta->add($new_document_id, "esig_wpaccess_control", $old_access_control_settings);
                //$api->meta->add($old_document_id, "esig_dashboard_signed", $wp_user_id);
                self::store_signed_data($old_document_id);
                self::store_signed_data($new_document_id);
                // add_user_meta($wp_user_id,"esign_document",);
            }
        }

        public function enqueue_admin_scripts() {

            $screen = get_current_screen();
            $admin_screens = array(
                'admin_page_esign-add-document',
                'admin_page_esign-edit-document',
                'e-signature_page_esign-view-document'
            );


            if (in_array($screen->id, $admin_screens)) {
                wp_enqueue_script(
                        $this->plugin_slug . '-admin-script', plugins_url('assets/js/esig-access-control.js', __FILE__), array('jquery'), "1.0.0", 'all'
                );
            }
        }

        /*         * *
         * getting all access control documents 
         */

        public static function get_ac_documents() {

            $api = new WP_E_Api();

            return $api->meta->getall_bykey("esig_wpaccess_control");
        }
        
        public static function enqueue_access_script(){
            wp_enqueue_script('jquery');
            wp_enqueue_style('esig-access-control-bootstrapm-styles');
            wp_enqueue_style('esig-access-control-css-styles');
            wp_enqueue_style('esig-icon-css-styles');
            wp_enqueue_script("esig-access-control-bootstrap-js");
            
        }

        public function esig_doc_dashboard($atts) {

            if (!function_exists('WP_E_Sig')) {
                return;
            }
            if (!is_user_logged_in()) {
                return;
            }
            // extructing shortcode here 
            extract(shortcode_atts(array(
                'status' => 'all',
                            ), $atts, 'esig-doc-dashboard'));
            // getting user id from user data 
            $wp_user_id = get_current_user_id();


            $ac_settings = self::get_ac_documents();

            // setting html here 
            $html = '';

            foreach ($ac_settings as $settings) {
                $document_id = $settings->document_id;
                $meta = json_decode($settings->meta_value);

                if (!self::esig_access_control_enabled($meta)) {

                    continue;
                }

                if (!self::esig_is_user_access($wp_user_id, $meta, $document_id)) {
                    continue;
                }
                
                
                $html .=self::dashboard_output($status, $document_id, $meta);
            }
            
                // load access control scripts 
               self::enqueue_access_script();
            return $html;
        }

        public static function dashboard_output($status, $document_id, $meta) {

            if ($status == "required") {

                return self::required_doc_output($document_id, $meta);
            } elseif ($status == "signed") {
                return self::signed_doc_output($document_id, $meta);
            } else {
                return self::all_doc_output($document_id, $meta);
            }
        }

        public static function required_doc_output($document_id, $meta) {

            if (!self::is_required_doc($meta)) {
                return;
            }

            if (self::this_document_signed($document_id)) {
                return;
            }

            return self::esig_doc_dashboard11($document_id, $meta);
        }

        public static function signed_doc_output($document_id, $meta) {

            if (!self::is_signed_doc($document_id)) {
                return;
            }

            if (!self::this_document_signed($document_id)) {
                return;
            }

            return self::esig_doc_dashboard11($document_id, $meta);
        }

        public static function all_doc_output($document_id, $meta) {

            if (self::is_all_sad_signed($document_id)) {
                return;
            }
            return self::esig_doc_dashboard11($document_id, $meta);
        }

        public static function store_signed_data($document_id) {
            $wp_user_id = get_current_user_id();
            add_user_meta($wp_user_id, "esig-" . $document_id . "-signed", 1);
        }

        public static function this_document_signed($document_id) {

            $api = new WP_E_Api();

            $wp_user_id = get_current_user_id();

            $document_type = $api->document->getDocumenttype($document_id);

            if ($document_type == "stand_alone") {
                $signed = get_user_meta($wp_user_id, "esig-" . $document_id . "-signed", true);
                if ($signed) {
                    return true;
                } else {
                    return false;
                }
            } elseif ($document_type == "normal") {

                if ($api->signature->userHasSignedDocument(self::get_esign_user_id(), $document_id)) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        public static function get_page_link($api, $document) {

            if (self::is_signed_doc($document->document_id)) {

                $pageID = $api->setting->get_generic('default_display_page');

                $invite_hash = $api->invite->get_Invite_Hash(self::get_esign_user_id(), $document->document_id);

                $url = add_query_arg(array('invite' => $invite_hash, 'csum' => $document->document_checksum), get_permalink($pageID));

                return $url;
            }

            if ($document->document_type == "stand_alone") {

                $sad_class = new esig_sad_document();

                $sad_pageID = $sad_class->get_sad_page_id($document->document_id);
                $url = get_permalink($sad_pageID);

                return $url;
            }
        }

        public static function get_button_text($api, $document) {

            //if ($document->document_type == "normal") {

            if ($api->signature->userHasSignedDocument(self::get_esign_user_id(), $document->document_id)) {
                $button_text = '<div class="esig-ac-signed-button">
                              <span class="pull-left"><a href="#" onclick="esig_print(\'' . self::get_page_link($api, $document) . '\')"><span id="icon" class="icon-print-icon esig-ac-icon"></span></a></span>
                              <span class="pull-center"><a href="' . admin_url() . 'admin.php?esigtodo=esigpdf&did=' . $document->document_checksum . '" " ><span id="icon" class="icon-download-icon esig-ac-icon"></span></a></span>
                              <span class="pull-right"><a href="' . self::get_page_link($api, $document) . '" target="_blank"><span id="icon" class="icon-zoom-icons esig-ac-icon"></a></span></span></div>';
            } else {

                $button_text = '<a class="esig-ac-sign-now" style="text-decoration: none !important;color:white !important;" href="' . self::get_page_link($api, $document) . '"><div class="esig-ac-button">' . __('COMPLETE & ESIGN NOW', 'esign') . '</div></a>';
                //$button_text = '' . __('COMPLETE & ESIGN NOW', 'esign') . '';
            }
            return $button_text;
            //}
        }

        public static function esig_doc_dashboard11($document_id, $access_control) {

            $api = new WP_E_Api();

            $html = '<script type="text/javascript" src="' . ESIGN_ASSETS_DIR_URI . '/js/esig-access-control-shortcode.js" > </script> ';


            $document = $api->document->getDocument($document_id);

            $document_title = (isset($document->document_title)) ? $document->document_title : null;

            // $role_array = (isset($access_control->esig_access_control_role)) ? $access_control->esig_access_control_role : null;
            $esig_document_description = (isset($access_control->esig_document_description)) ? $access_control->esig_document_description : null;


            $noimage = ESIGN_ASSETS_DIR_URI . '/images/noimage.jpg';
            $thumbnail_url = (isset($access_control->esig_image_thumbnail_src)) ? $access_control->esig_image_thumbnail_src : null;

            if ($thumbnail_url) {
                $display_img = $thumbnail_url;
            } else {
                $display_img = $noimage;
            }

            $html .= '<div class = "esig-access-control-wrap">
                                <div class = "esig-thumbnail">
                                  <img src="' . $display_img . '" class="esig_access_img_thumb">
                                          <div class="esig-ac-title">' . $document_title . '</div>

                                          <div class="esig-ac-description">' . $esig_document_description . ' </div>

                                         
                                </div>
                                
                                ' . self::get_button_text($api, $document) . '
								
                        </div>';

            return $html;
        }

        public static function is_all_sad_signed($document_id) {

            $api = new WP_E_Api();

            $docutmet_status = $api->document->getStatus($document_id);

            if ($docutmet_status == "stand_alone") {

                if (self::this_document_signed($document_id)) {
                    return true;
                }
            }
            return false;
        }

        public static function is_signed_doc($document_id) {

            $api = new WP_E_Api();

            $docutmet_status = $api->document->getStatus($document_id);

            if ($docutmet_status == "signed" || $docutmet_status == "awaiting") {
                return true;
            }

            return false;
        }

        public static function is_required_doc($meta) {

            if ($meta->esig_document_permission == "required") {
                return true;
            }
            return false;
        }

        public static function is_optional_doc($meta) {

            if ($meta->esig_document_permission == "optional") {
                return true;
            }
            return false;
        }

        public static function esig_access_control_enabled($meta) {



            if (!is_object($meta)) {
                return false;
            }

            $esig_required_wpmember = (isset($meta->esig_required_wpmember)) ? $meta->esig_required_wpmember : null;

            if ($esig_required_wpmember) {
                return true;
            } else {

                return false;
            }
        }

        public static function get_esign_user_id() {

            $api = new WP_E_Api();
            $wp_user_id = get_current_user_id();
            $user_data = get_userdata($wp_user_id);
            $email_address = $user_data->user_email;
            $esign_user_id = $api->user->getUserID($email_address);
            return $esign_user_id;
        }

        /*         * *
         *  Checking current user role access . 
         *  @return bolean 
         *  @Since 1.3.1
         */

        public static function esig_is_user_access($wp_user_id, $meta, $document_id = false) {

            $user_data = get_userdata($wp_user_id);
            $current_role = implode(', ', $user_data->roles);
            // $meta = json_decode($meta);
            $roles = $meta->esig_access_control_role;

            if (!is_array($roles)) {
                return false;
            }

            if (!in_array($current_role, $roles)) {
                return false;
            }


            if (!self::is_document_access($wp_user_id, $document_id)) {
                return false;
            }


            return true;
        }

        public static function is_document_access($wp_user_id, $document_id) {

            $document_type = WP_E_Sig()->document->getDocumenttype($document_id);

            $document_status = WP_E_Sig()->document->getStatus($document_id);

            if ($document_status == "signed") {
                return false;
            }

            if ($document_type == "normal") {

                $user_data = get_userdata($wp_user_id);
                $user_email = $user_data->user_email;
                $esign_user_id = WP_E_Sig()->user->getUserID($user_email);

                if (WP_E_Sig()->signer->exists($esign_user_id, $document_id)) {
                    return true;
                }
            } elseif ($document_type == "stand_alone") {
                return true;
            }
            return false;
        }

        public function get_sad_documents($status) {
            global $wpdb;
            $table_prefix = $wpdb->prefix . "esign_";
            $table = $table_prefix . "documents";
            return $wpdb->get_results(
                            $wpdb->prepare(
                                    "SELECT * FROM " . $table . " WHERE document_status=%s ORDER BY document_id DESC", $status
                            )
            );
        }

    }

    endif;

new ESIG_ACCESS_CONTROL_Shortcode();
