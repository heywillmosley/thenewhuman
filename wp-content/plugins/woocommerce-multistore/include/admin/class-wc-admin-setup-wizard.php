<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their WooCommerce Multistore.
 *
 * @author      Tonny
 * @category    Admin
 * @package     Multistore/Admin
 * @version     2.4.0
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Setup_Wizard class.
 */
class WC_Admin_Setup_Wizard {

	/** @var string Currenct Step */
	private $step   = '';

	/** @var array Steps for the setup wizard */
	private $steps  = array();


	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		if ( apply_filters( 'woocommerce_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'woonet-setup', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || 'woonet-setup' !== $_GET['page'] ) {
			return;
		}
		$this->steps = array(
			'introduction' => array(
				'name'    =>  __( 'Introduction', 'woonet' ),
				'view'    => array( $this, 'wc_setup_introduction' ),
				'handler' => ''
			),
			'pages' => array(
				'name'    =>  __( 'Network Sites', 'woonet' ),
				'view'    => array( $this, 'wc_setup_network_sites' ),
				'handler' => array( $this, 'wc_setup_network_sites_save' )
			),
			'locale' => array(
				'name'    =>  __( 'Update', 'woonet' ),
				'view'    => array( $this, 'wc_setup_update' ),
				'handler' => array( $this, 'wc_setup_update_save' )
			),
			'next_steps' => array(
				'name'    =>  __( 'Ready!', 'woonet' ),
				'view'    => array( $this, 'wc_setup_ready' ),
				'handler' => ''
			)
		);
		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
        wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-progressbar' ), '2.70', true );
		wp_register_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
		wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION );
        wp_register_script( 'woosl-setup', WOO_MSTORE_URL . '/assets/js/woosl-setup.js', array( 'jquery', 'select2', 'wc-enhanced-select' ), '1.0' );
	
		wp_enqueue_style('admin-ui-css',
                'http://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css',
                false,
                '1.1.6',
                false);
        wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woonet-setup', WOO_MSTORE_URL . '/assets/css/woosl-setup.css', array( 'dashicons', 'install', 'admin-ui-css' ), WC_VERSION );
                

		wp_register_script( 'woonet-setup', WC()->plugin_url() . '/assets/js/admin/wc-setup.min.js', array( 'jquery', 'wc-enhanced-select', 'jquery-blockui', 'woosl-setup' ), WC_VERSION );
		wp_localize_script( 'woonet-setup', 'wc_setup_params', array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'locale_info' => json_encode( include( WC()->plugin_path() . '/i18n/locale-info.php' ) )
		) );

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'] );
		}

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	public function get_next_step_link() {
		$keys = array_keys( $this->steps );
		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ] );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {
		?>
		<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php _e( 'WooCommerce &rsaquo; Setup Wizard', 'woonet' ); ?></title>
			<?php wp_print_scripts( 'woonet-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wc-setup wp-core-ui">
			<h1 id="wc-logo"><img src="<?php echo WOO_MSTORE_URL ?>/assets/images/woocommerce_logo.png" alt="WooCommerce" /></h1>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {
		?>
			<?php if ( 'next_steps' === $this->step ) : ?>
				<a class="wc-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'woonet' ); ?></a>
			<?php endif; ?>
			</body>
		</html>
		<?php
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {
		$ouput_steps = $this->steps;
		array_shift( $ouput_steps );
		?>
		<ol class="wc-setup-steps">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
				<li class="<?php
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
					}
				?>"><?php echo esc_html( $step['name'] ); ?></li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {
		echo '<div class="wc-setup-content">';
		call_user_func( $this->steps[ $this->step ]['view'] );
		echo '</div>';
	}

    
    function get_network_sites_using_woocommerce()
        {
            $sites_using_woocommerce    =   array();
            
            $network_sites  =   get_sites(array('limit'  =>  999));
            foreach($network_sites as $network_site)
                {
                    switch_to_blog( $network_site->blog_id );   
                    
                    if(is_plugin_active( 'woocommerce/woocommerce.php' ))
                        {
                            $sites_using_woocommerce[]  =   $network_site->blog_id;
                        }
                                     
                    restore_current_blog();                    
                }
                
            return $sites_using_woocommerce;
        }
    
	/**
	 * Introduction step.
	 */
	public function wc_setup_introduction() {
		?>
		<h1><?php _e( 'Welcome to WooCommerce Multistore!', 'woonet' ); ?></h1>
		<p><?php _e( 'Thank you for choosing WooCommerce Multistore to power your online WooCommerce store! This quick setup wizard will make sure that all of your multisite stores are ready for use with this plugin.', 'woonet' ); ?></p>
		<p><?php _e( "No time right now? If you don't want to go through the wizard, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!", 'woonet' ); ?></p>
		<p class="wc-setup-actions step">
			<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'woonet' ); ?></a>
			<a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'woonet' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Page setup.
	 */
	public function wc_setup_network_sites() 
        {
		    $sites_using_woocommerce    =   $this->get_network_sites_using_woocommerce();
            
            ?>
		    <h1><?php _e( 'Network Sites', 'woonet' ); ?></h1>
		    <form method="post">
			    <p><?php  _e( 'The folowing Sites has been found to use WooCommerce store framework and will be updated', 'woonet' ); ?></p>
			    <table class="wc-setup-pages" cellspacing="0">
				    <tbody>
					    <?php
                        
                            foreach($sites_using_woocommerce    as  $site_id)
                                {
                                    $blog_details   =   get_blog_details($site_id);
                                    
                                    switch_to_blog( $site_id );
                                    $count_posts = wp_count_posts('product');
                                    restore_current_blog();
                                    
                                    ?>
                                    <tr>
                                        <td class="page-name"><?php echo $blog_details->blogname ?></td>
                                        <td>
                                            Products: <?php echo$count_posts->publish ?><br />
                                            Url: <?php echo $blog_details->siteurl ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                
                        ?>
				    </tbody>
			    </table>

			    <p><?php _e( 'Once updated, the data become available to be used with WooCommerce Multistore plugin.', 'woonet' ); ?></p>

			    <p class="wc-setup-actions step">
				    <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woonet' ); ?>" name="save_step" />
				    <?php wp_nonce_field( 'woonet-setup' ); ?>
			    </p>
		    </form>
		    <?php
	    }

	/**
	 * Save Page Settings.
	 */
	public function wc_setup_network_sites_save() {
		check_admin_referer( 'woonet-setup' );

		
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Locale settings.
	 */
	public function wc_setup_update() 
        {
		    
            $sites_using_woocommerce    =   $this->get_network_sites_using_woocommerce();
            $_to_process    =   array();
            foreach($sites_using_woocommerce    as  $site_id)
                {
                    $blog_details   =   get_blog_details($site_id);
                    
                    switch_to_blog( $site_id );
                    $count_posts = wp_count_posts('product');
                    restore_current_blog();
                    
                    $_to_process[]    =   array(
                                                    'id'        =>  $site_id,
                                                    'blogname'  =>  $blog_details->blogname  
                                                );

                }
            
            
		    ?>
		    <h1><?php _e( 'Update', 'woonet' ); ?> <img id="ajax_loading" src="<?php echo WOO_MSTORE_URL ?>/assets/images/ajax-loader.gif" alt="" /></h1>
		    <div id="wc-ajax-output">
                <div id="process_log"></div>
                <div id="progressbar"></div>
                <div id="error_log"></div>
            </div>
            <form method="post">
		    	    
			    <p class="wc-setup-actions step" style="display: none">
				    <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'woonet' ); ?>" name="save_step" />
				    <?php wp_nonce_field( 'woonet-setup' ); ?>
			    </p>
		    </form>
            
            <script type="text/javascript"><!--//--><![CDATA[//><!--
                
                jQuery(document).ready(function()
                    {
                        var sites_using_woocommerce =   <?php echo json_encode($_to_process) ?>

                        woosl_setup.start_process(sites_using_woocommerce);

                });
            //--><!]]></script>
            
            
		    <?php
	    }

	/**
	 * Save Locale Settings.
	 */
	public function wc_setup_update_save() {
		check_admin_referer( 'woonet-setup' );
   
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	
	/**
	 * Actions on the final step.
	 */
	private function wc_setup_ready_actions() 
        {
		    
            update_site_option('mstore_setup_wizard_completed', 'true');
                
	    }

	/**
	 * Final step.
	 */
	public function wc_setup_ready() {
		$this->wc_setup_ready_actions();
		
		?>

		<h1><?php _e( 'Your site is Ready for use with WooCommerce Multistore!', 'woonet' ); ?></h1>


		<div class="wc-setup-next-steps">
			<div class="wc-setup-next-steps-first">
				<h2><?php _e( 'Next Steps', 'woonet' ); ?></h2>
				<ul>
					<li class="setup-product"><a class="button button-primary button-large" href="<?php echo esc_url( network_site_url( 'wp-admin/network/admin.php?page=woonet-woocommerce-products' ) ); ?>"><?php _e( 'Multisite Products Dashboard!', 'woonet' ); ?></a></li>
				</ul>
			</div>
			<div class="wc-setup-next-steps-last">
				<h2><?php _e( 'Learn More', 'woonet' ); ?></h2>
				<ul>
					<li class="learn-more"><a target="_blank" href="http://woomultistore.com/documentation/"><?php _e( 'Read more about getting started', 'woonet' ); ?></a></li>
				</ul>
			</div>
		</div>
		<?php
	}
}

?>