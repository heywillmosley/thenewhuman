<?php

/**
 *
 * @package ESIG_ACCESS_CONTROL_Admin
 * @author  Abu Shoaib <abushoaib73@gmail.com>
 */
if (!class_exists('ESIG_ACCESS_CONTROL_Admin')) :

    class ESIG_ACCESS_CONTROL_Admin {

        /**
         * Instance of this class.
         * @since    0.1
         * @var      object
         */
        protected static $instance = null;
        
        /**
         * Slug of the plugin screen.
         * @since    0.1
         * @var      string
         */
        protected $plugin_screen_hook_suffix = null;

        /**
         * Initialize the plugin by loading admin scripts & styles and adding a
         * settings page and menu.
         * @since     0.1
         */
        public function __construct() {

            
            
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('esig_document_before_save', array($this, 'esig_access_control_sidebar'), 10, 1);
            add_action('esig_document_before_edit_save', array($this, 'esig_access_control_sidebar'), 10, 1);
            //add_action('esig_document_before_save', array($this, 'esig_add_image_featured_sidebar'), 10, 1);
            //add_action('esig_document_before_edit_save', array($this, 'esig_add_image_featured_sidebar'), 10, 1);
            add_action('esig_document_after_save', array($this, 'esig_access_control_document_after_save'), 10, 1);

            // adding sad legal sad fname ;
            add_filter("esig_sad_legal_fname", array($this, "sad_legal_fname"));
            add_filter("esig_sad_legal_email_address", array($this, "sad_legal_email_address"));
        }

        public function sad_legal_fname() {

            if (is_user_logged_in()) {
                $user_ID = get_current_user_id();

                $user_info = get_userdata($user_ID);

                return $user_info->first_name . " " . $user_info->last_name;
            }

            return;
        }

        public function sad_legal_email_address() {

            if (is_user_logged_in()) {
                $user_ID = get_current_user_id();

                $user_info = get_userdata($user_ID);

                return $user_info->user_email;
            }

            return;
        }

        public function enqueue_admin_scripts() {
            $screen = get_current_screen();
            $admin_screens = array(
                'admin_page_esign-add-document',
                'admin_page_esign-edit-document',
                'e-signature_page_esign-view-document'
            );
            if (in_array($screen->id, $admin_screens)) {
                wp_enqueue_script('esig-access-control-admin-script', ESIGN_ASSETS_DIR_URI . '/js/esig-access-control.js', array('jquery'), "1.0.0", 'all'
                );
            }
        }

        public function esig_access_control_document_after_save($args) {
            
         
            $document_id = $args['document']->document_id;
            
            $api = new WP_E_Api();
            
            if(!isset($_POST['esig_required_wpmember'])){
                return ; 
            }

            //getting value from post  
            $esig_required_wpmember = isset($_POST['esig_required_wpmember']) ? $_POST['esig_required_wpmember'] : NULL;
            $esig_access_control_role = isset($_POST['esig_access_control_role']) ? $_POST['esig_access_control_role'] : NULL;
            $esig_document_permission = isset($_POST['esig_document_permission']) ? $_POST['esig_document_permission'] : NULL;
            $esig_document_description = isset($_POST['esig_document_description']) ? $_POST['esig_document_description'] : NULL;
            if (!$esig_document_description) {

                $esig_document_description = __('Welcome to our site, so we can better serve you please sign this agreement.', 'esig');
            }
            $esig_image_thumbnail_src = isset($_POST['esig_image_thumbnail_src']) ? $_POST['esig_image_thumbnail_src'] : NULL;

            $access_control = array(
                'esig_required_wpmember' => $esig_required_wpmember,
                'esig_access_control_role' => $esig_access_control_role,
                'esig_document_permission' => $esig_document_permission,
                'esig_document_description' => $esig_document_description,
                'esig_image_thumbnail_src' => $esig_image_thumbnail_src,
            );

            //save document with meta
            $api->meta->add($document_id, 'esig_wpaccess_control', json_encode($access_control));
        }

        public function esig_access_control_sidebar() {

            if (!function_exists('WP_E_Sig'))
                return;
            global $wpdb;
            $api = new WP_E_Api();

            $content = '';

            $file_name = ESIGN_ASSETS_DIR_URI . '/images/help.png';

            $title = ' <a href="#" class="tooltip">
                                                    <img src="' . $file_name . '" height="20px" align="left" />
                                          <span>
                                              ' . __('The Document Portal feature lets you assign Stand Alone Documents to a specific Wordpress user role (like: editor, subscriber, etc). When you insert the shortcode [esig-doc-dashboard status="required"] on any WordPress page your users will see their required docs.', 'esig') . '
                                          </span>
                                    </a> ' . __('Document Access Control', 'esig');

            $document_id = isset($_GET['document_id']) ? $_GET['document_id'] : null;
            if ($document_id) {
                $access_control = json_decode($api->meta->get($document_id, 'esig_wpaccess_control'));
            }

            $esig_required_wpmember_checked = (isset($access_control) && $access_control->esig_required_wpmember) ? "checked" : "";
            $sub_array = (isset($access_control) && $access_control->esig_access_control_role) ? $access_control->esig_access_control_role : array();
            $display = ($esig_required_wpmember_checked == "checked") ? "block" : "none";
            $content .= '
                                        <input type="checkbox" id="esig_required_wpmember" name="esig_required_wpmember" value="1" style="margin-left:19.5px;" ' . $esig_required_wpmember_checked . '>Required a Specific Wordpress member (or) user role to sign this document.<br>
                                        
                                        <div id="esig_wpaccess_control_role" name="esig_wpaccess_control_role" style="display:' . $display . ';" >
                                        <div id="esig-valid-message" style="display:none;"> ' . __("Oops! It looks like you haven't yet selected your user role. Please do it now, and try saving again.", "esig") . ' </div> <br>  
                                        <div style="margin-left:46px;" > ';

            foreach (get_editable_roles() as $role => $role_name) {
                $checked = (in_array($role, $sub_array)) ? "checked" : "";

                $content .= '<input id="esig_access_control_role" type="checkbox" name="esig_access_control_role[]" ' . $checked . ' value="' . $role . '" > ' . $role . '<br>';
            }
            $permission_array = array("required" => "This Document is required", "optional" => "This Document is Optional");
            $content .= ' </div>
                                                <br>  
                                              <select name="esig_document_permission" >
                                                     ';
            foreach ($permission_array as $key => $value) {
                if (isset($access_control) && $access_control->esig_document_permission) {
                    $selected = ($access_control->esig_document_permission == $key) ? "selected" : "";
                } else {
                    $selected = "";
                }

                $content .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
            }


            $content .= '</select><br><hr>';

            $content .= '<h4><span>Document Description</span></h4>';
            if ($document_id) {
                $access_control = json_decode($api->meta->get($document_id, 'esig_wpaccess_control'));
            }

            $noimage = ESIGN_ASSETS_DIR_URI . '/images/noimage.jpg';
            $esig_image_thumbnail_src = (isset($access_control) && $access_control->esig_image_thumbnail_src) ? $access_control->esig_image_thumbnail_src : "$noimage";

            $esig_document_description = (isset($access_control) && $access_control->esig_document_description) ? $access_control->esig_document_description : "";

            $count = (!empty($esig_document_description)) ? 75 - strlen($esig_document_description) : 75;

            $content .='<textarea  id="esig_document_description" name="esig_document_description" rows="4" cols="28" maxlength="75" placeholder="Welcome to our site, so we can better serve you please sign this agreement.">' . $esig_document_description . '</textarea></br>'
                    . '<div id="esig-char-limit">' . sprintf(__('The document description will be limited to 75 chars, <span id="esig-char-count"> %d </span> chars left', 'esig'), $count) . '</div>';
            $content.='<div id="esig-featured-image-container" class="hidden">
                                                                   <img src="' . $esig_image_thumbnail_src . '">
                                                                    </div><!-- #esig-featured-image-container -->


                                                                    <p class="hide-if-no-js" >
                                                                       <a title="Set Footer Image" href="javascript:;" id="esig-set-image-thumbnail">' . __('Set featured image', 'esign') . '</a>
                                                                    </p>

                                                                    <p class="hide-if-no-js">
                                                                            <a title="Remove Footer Image" href="javascript:;" id="esig-remove-image-thumbnail">' . __('Remove featured image', 'esign') . '</a><br><a href=" https://www.approveme.me/wordpress-document-portal/" class="button-secondary" target="_blank">Learn about this feature</a>
                                                                    </p><!-- .hide-if-no-js -->


                                                                    <p id="esig-featured-image-info">
                                                                            <input type="hidden" id="esig-image-thumbnail-src" name="esig_image_thumbnail_src" value="' . $esig_image_thumbnail_src . '">
                                                                    </p>';

            '</div>';

            $api->view->setSidebar($title, $content, "acesscontrol", "acesscontrolbody");
            echo $api->view->renderSidebar();
        }

        /**
         * Return an instance of this class.
         * @since     0.1
         * @return    object    A single instance of this class.
         */
        public static function get_instance() {

// If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

    }

    endif;

new ESIG_ACCESS_CONTROL_Admin();
