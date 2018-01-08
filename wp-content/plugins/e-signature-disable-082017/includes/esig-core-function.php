<?php


/**
 * Set e-signature cookie 
 * @param type $name
 * @param type $value
 * @param type $expire
 * @param type $secure
 */

function esig_setcookie($name, $value, $expire = 0, $secure = false) {
    if (!headers_sent()) {
        setcookie($name, $value,  time() + $expire, COOKIEPATH, COOKIE_DOMAIN, $secure);
    } elseif (defined('WP_DEBUG') && WP_DEBUG) {
        headers_sent($file, $line);
        trigger_error("{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE);
    }
}

/**
 * Unset esignature cookie 
 * @param type $name
 * @param type $secure
 */

function esig_unsetcookie($name,$secure=false){
    // Clear cookie
    setcookie($name, '', time() - YEAR_IN_SECONDS);
}

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.4.0
 * @return string $ip User's IP address
 */
function esig_get_ip() {

	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
        
	return apply_filters( 'esig_get_ip', $ip );
}


function ESIG_GET($key){
    
    if(isset($_GET[$key])){
        return wp_unslash($_GET[$key]) ; 
    }
    return false;
}

function ESIG_SEARCH_GET($key){
  
   return isset($_REQUEST[$key]) ?  wp_unslash( $_REQUEST[$key]  ) : '';
}
function ESIG_POST($key){
    
    if(isset($_POST[$key])){
        return wp_unslash($_POST[$key]) ; 
    }
    return false; 
}

function esig_unslash($result){
    return esc_attr(wp_unslash($result));
}