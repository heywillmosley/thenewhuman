<?php

// Defines
define('FL_CHILD_THEME_DIR', get_stylesheet_directory());
define('FL_CHILD_THEME_URL', get_stylesheet_directory_uri());

// Classes
require_once 'classes/FLChildTheme.php';


// Actions
add_action('fl_head', 'FLChildTheme::stylesheet');

// Custom Login URL
function my_login_page( $login_url, $redirect ) {
    return site_url( '/account/?redirect_to=' . $redirect );
}
add_filter( 'login_url', 'my_login_page', 10, 2 );

/**
 * Filter Force Login to allow exceptions for specific URLs.
 *
 * @return array An array of URLs. Must be absolute.
 */
function my_forcelogin_whitelist( $whitelist ) {
  $whitelist[] = site_url( '/' );
  $whitelist[] = site_url( '/terms/' );
  return $whitelist;
}
add_filter('v_forcelogin_whitelist', 'my_forcelogin_whitelist', 10, 1);

$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$course_slug = "overview-of-iabc-certification";

if ( $actual_link == "https://fun.thenewhuman.com/courses/$course_slug/course-completion/" ||
    $actual_link == "https://iabc.thenewhuman.com/courses/$course_slug/course-completion/" ||
    $actual_link == "https://getiabc/courses/$course_slug/course-completion/" ||
    $actual_link == "http://fun.thenewhuman.com/courses/$course_slug/course-completion/" ||
    $actual_link == "http://iabc.thenewhuman.com/courses/$course_slug/course-completion/" ||
    $actual_link == "http://getiabc/courses/$course_slug/course-completion/") {
    
    // Change user role upon course completion
    add_action('coursepress_student_course_completed' , 'change_role_certified', 10, 2);
    function change_role_certified( $student_id, $course_id ) {

	 $current_user = wp_get_current_user();
	 $current_user->remove_role('newhuman_member');
	 $current_user->add_role('certified_iabc');

    } // add_action

} // if actual_link


if ( is_user_logged_in() ) {
    add_filter('body_class','add_role_to_body');
    add_filter('admin_body_class','add_role_to_body');
}
function add_role_to_body($classes) {
    $current_user = new WP_User(get_current_user_id());
    $user_role = array_shift($current_user->roles);
    if (is_admin()) {
        $classes .= 'role-'. $user_role;
    } else {
        $classes[] = 'role-'. $user_role;
    }
    return $classes;
}

//bp_adminbar_notifications_menu();


@ini_set( 'upload_max_size' , '64M' );
@ini_set( 'post_max_size', '64M');
@ini_set( 'max_execution_time', '300' );