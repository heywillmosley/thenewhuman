<?php

/**
 * Set e-signature cookie 
 * @param type $name
 * @param type $value
 * @param type $expire
 * @param type $secure
 */
if (!function_exists('esig_setcookie')) {

    function esig_setcookie($name, $value, $expire = 0, $secure = false) {
        if (!headers_sent()) {
            setcookie($name, $value, time() + $expire, COOKIEPATH, COOKIE_DOMAIN, $secure);
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            headers_sent($file, $line);
            trigger_error("{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE);
        }
    }

}

/**
 * Unset esignature cookie 
 * @param type $name
 * @param type $secure
 */
if (!function_exists('esig_unsetcookie')) {

    function esig_unsetcookie($name, $cookiepath = false, $secure = false) {
        // Clear cookie
        if ($cookiepath) {
            setcookie($name, null, time() - YEAR_IN_SECONDS, $cookiepath);
        } else {
            setcookie($name, null, time() - YEAR_IN_SECONDS);
        }
    }

}

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.4.0
 * @return string $ip User's IP address
 */
if (!function_exists('esig_get_ip')) {

    function esig_get_ip() {

        $ip = '127.0.0.1';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return apply_filters('esig_get_ip', $ip);
    }

}


if (!function_exists('ESIG_GET')) {

    function ESIG_GET($key, $array = false) {

        if ($array) {
            return filter_input(INPUT_GET, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        }

        if (filter_input(INPUT_GET, $key)) {
            return filter_input(INPUT_GET, $key);
        }

        return false;
    }

}

if (!function_exists('ESIG_POST')) {

    function ESIG_POST($key, $array = false) {
        if ($array) {
            return filter_input(INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        }
        if (filter_input(INPUT_POST, $key)) {
            return filter_input(INPUT_POST, $key);
        }
        return false;
    }

}

if (!function_exists('esigRequest')) {
    function esigRequest($key) {
        $data = filter_var($_REQUEST[$key]);
        return sanitize_text_field($data);
    }
}


if (!function_exists('esigget')) {

    function esigget($name, $array = null) {

        if (!isset($array)) {
            return ESIG_GET($name);
        }

        if (is_array($array)) {
            if (isset($array[$name])) {
                return wp_unslash($array[$name]);
            }
            return false;
        }

        if (is_object($array)) {
            if (isset($array->$name)) {
                return wp_unslash($array->$name);
            }
            return false;
        }

        return false;
    }

}



if (!function_exists('ESIG_SEARCH_GET')) {

    function ESIG_SEARCH_GET($key) {

        return isset($_REQUEST[$key]) ? wp_unslash($_REQUEST[$key]) : '';
    }

}

if (!function_exists('esigpost')) {

    function esigpost($key, $array = false) {
        return ESIG_POST($key, $array);
    }

}

if (!function_exists('esig_unslash')) {

    function esig_unslash($result) {
        return esc_attr(wp_unslash($result));
    }

}

if (!function_exists('has_esig_shortcode')) {

    function has_esig_shortcode($page_id) {

        $post = get_post($page_id);
        if (!isset($post)) {
            return false;
        }
        //$content = apply_filters('the_content', $post->post_content); 
        if (!has_shortcode($post->post_content, "wp_e_signature_sad") && !has_shortcode($post->post_content, "wp_e_signature")) {
            return false;
        }
        return true;
    }

}

if (!function_exists('esig_is_plugin_active')) {

    function esig_is_plugin_active($plugin) {
        $network_active = false;
        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins[$plugin])) {
                $network_active = true;
            }
        }
        return in_array($plugin, get_option('active_plugins')) || $network_active;
    }

}

if (!function_exists('esig_create_nonce')) {

    function esig_create_nonce($nonce) {
        return wp_create_nonce('esig-nonce-all' . $nonce);
    }

}

if (!function_exists('esig_verify_nonce')) {

    function esig_verify_nonce($value, $nonce) {
        if (wp_verify_nonce($value, 'esig-nonce-all' . $nonce)) {
            return true;
        }
        return false;
    }

}

if (!function_exists('esig_verify_not_spam')) {

    function esig_verify_not_spam() {
        $sp = esigpost('esig_sp_url');
        if (empty($sp)) {
            return true;
        }
        return false;
    }

}




if (!function_exists('esigGetVersion')) {

    function esigGetVersion() {
        if (!function_exists("get_plugin_data"))
            require ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_data = get_plugin_data(ESIGN_PLUGIN_FILE);
        $plugin_version = $plugin_data['Version'];
        return $plugin_version;
    }

}

if (!function_exists('esigStripTags')) {

    function esigStripTags($str, $tag) {

        $str = preg_replace('/<' . $tag . '[^>]*>/i', '', $str);

        $str = preg_replace('/<\/' . $tag . '>/i', '', $str);

        return $str;
    }

}

if (!function_exists('ESIG_COOKIE')) {

    function ESIG_COOKIE($key) {
        return filter_input(INPUT_COOKIE, $key);
    }

}

if (!function_exists('getAddonVersion')) {

    function getAddonVersion($path) {
        if (!function_exists("get_plugin_data"))
            require ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_data = get_plugin_data($path);
        $plugin_version = $plugin_data['Version'];
        return $plugin_version;
    }

}

if (!function_exists('esigHtml')) {

    function esigHtml($value) {
         $value = stripslashes($value);
         return esc_html($value);
    }

}


if (!function_exists('esig_is_func_disabled')) {
    function esig_is_func_disabled($function) {
        $disabled = explode(',', ini_get('disable_functions'));
        return in_array($function, $disabled);
    }
}