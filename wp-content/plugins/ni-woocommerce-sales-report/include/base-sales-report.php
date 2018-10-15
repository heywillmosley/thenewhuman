<?php 
if ( ! defined( 'ABSPATH' ) ) { exit;}
include_once('report-function.php'); 
if( !class_exists( 'BaseSalesReport' ) ) {
class BaseSalesReport extends ReportFunction{
 	 public function __construct(){
	 
	 //$this->print_data( $_REQUEST["page"] );
	 	add_action( 'admin_menu',  array(&$this,'admin_menu' ));
		
		if (isset($_REQUEST["page"])){
			 $page =  	$this->get_request("page");
			if ($page =="ni-order-product" || $page =="ni-sales-report" ||  $page =="ni-category-report"|| $page  =="ni-top-product-report")
		
			add_action( 'admin_enqueue_scripts',  array(&$this,'admin_enqueue_scripts' ));
		}
		add_action( 'wp_ajax_sales_order',  array(&$this,'ajax_sales_order' )); /*used in form field name="action" value="my_action"*/
		add_action('admin_init', array( &$this, 'admin_init' ) );
		add_filter( 'plugin_row_meta',  array(&$this,'plugin_row_meta' ), 10, 2 );
		add_filter( 'admin_footer_text',  array(&$this,'admin_footer_text' ),101);
		//add_filter( 'gettext', array($this, 'get_text'),20,3);
	}
	function get_text($translated_text, $text, $domain){
		if($domain == 'nisalesreport'){
			return '['.$translated_text.']';
		}		
		return $translated_text;
	}
	function admin_footer_text($text){
		
		 if (isset($_REQUEST["page"])){
			 $page = $_REQUEST["page"]; 
			 	if ($page == "sales-report" || $page  =="order-product" || $page =="ni-sales-report-addons"){
			 	$text = sprintf( __( 'Thank you for using our plugins <a href="%s" target="_blank">naziinfotech</a>' ,'nisalesreport'), 
				__( 'http://naziinfotech.com/'  ,'nisalesreport') );
				$text = "<span id=\"footer-thankyou\">". $text ."</span>"	 ;
		 	}
		 }
		return $text ; 
	}
	function plugin_row_meta($links, $file){
		if ( $file == "ni-woocommerce-sales-report/ni-woocommerce-sales-report.php" ) {
			$row_meta = array(
			
			'ni_pro_version'=> '<a target="_blank" href="http://naziinfotech.com/product/ni-woocommerce-sales-report-pro">Buy Pro Version</a>',
			
			'ni_pro_review'=> '<a target="_blank" href="https://wordpress.org/support/plugin/ni-woocommerce-sales-report/reviews/">Write a Review</a>'	);
				

			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	function admin_enqueue_scripts($hook) {
   		 if (isset($_REQUEST["page"])){
			 $page = $_REQUEST["page"];
			if ($page == "ni-sales-report" || $page  =="ni-order-product" || $page =="ni-sales-report-addons" ||  
			$page =="ni-category-report" || $page  =="ni-top-product-report"){
		 	
				wp_enqueue_script( 'ni-sales-report-ajax-script', plugins_url( '../assets/js/script.js', __FILE__ ), array('jquery') );
		 		wp_enqueue_script( 'jquery-ui', plugins_url( '../assets/js/jquery-ui.js', __FILE__ ), array('jquery') );
		 		
			
				if ($page == "ni-sales-report")
				{
					wp_register_style( 'ni-sales-report-summary-css', plugins_url( '../assets/css/ni-sales-report-summary.css', __FILE__ ));
		 			wp_enqueue_style( 'ni-sales-report-summary-css' );
					wp_register_style( 'ni-font-awesome-css', plugins_url( '../assets/css/font-awesome.css', __FILE__ ));
		 			wp_enqueue_style( 'ni-font-awesome-css' );
					
					wp_register_script( 'ni-amcharts-script', plugins_url( '../assets/js/amcharts/amcharts.js', __FILE__ ) );
					wp_enqueue_script('ni-amcharts-script');
				
		
					wp_register_script( 'ni-light-script', plugins_url( '../assets/js/amcharts/light.js', __FILE__ ) );
					wp_enqueue_script('ni-light-script');
				
					wp_register_script( 'ni-pie-script', plugins_url( '../assets/js/amcharts/pie.js', __FILE__ ) );
					wp_enqueue_script('ni-pie-script');
				
					
				}
				elseif  ($page == "ni-top-product-report"){
					wp_register_style( 'sales-report-style', plugins_url( '../assets/css/sales-report-style.css', __FILE__ ));
		 			wp_enqueue_style( 'sales-report-style' );
				}
				elseif  ($page == "ni-category-report"){
					wp_enqueue_script( 'ajax-script-category-report', plugins_url( '../assets/js/category-report.js', __FILE__ ), array('jquery') );
					wp_register_style( 'sales-report-style', plugins_url( '../assets/css/sales-report-style.css', __FILE__ ));
		 			wp_enqueue_style( 'sales-report-style' );
				}
				else
				{
					wp_register_style( 'sales-report-style', plugins_url( '../assets/css/sales-report-style.css', __FILE__ ));
		 			wp_enqueue_style( 'sales-report-style' );
				}
		 		wp_localize_script( 'ni-sales-report-ajax-script','ni_sales_report_ajax_object',array('ni_sales_report_ajaxurl'=>admin_url('admin-ajax.php')));
			}
		 }
		
    }
	/*Ajax Call*/
	function ajax_sales_order()
	{
		$page= $this->get_request("page");
		if($page=="ni-order-product")
		{	include_once('order-item.php');
			$obj = new OrderItem();  
			$obj->ajax_call();
		}
		if($page=="ni-category-report")
		{	include_once('ni-category-report.php');
			$obj = new Ni_Category_Report();  
			$obj->get_ajax();
		}
		die;
	}
	function admin_menu(){
   		add_menu_page(__(  'Sales Report', 'nisalesreport')
		,__(  'Ni Sales Report', 'nisalesreport')
		,'manage_options'
		,'ni-sales-report'
		,array(&$this,'AddMenuPage')
		,'dashicons-media-document'
		,8);
    	add_submenu_page('ni-sales-report'
		,__( 'Dashboard', 'nisalesreport' )
		,__( 'Dashboard', 'nisalesreport' )
		,'manage_options'
		,'ni-sales-report' 
		,array(&$this,'AddMenuPage'));
    	add_submenu_page('ni-sales-report'
		,__( 'Order Product', 'nisalesreport' )
		,__( 'Order Product', 'nisalesreport' )
		, 'manage_options', 'ni-order-product' 
		, array(&$this,'AddMenuPage'));
		
		add_submenu_page('ni-sales-report'
		,__( 'Category Report', 'nisalesreport' )
		,__( 'Category Report', 'nisalesreport' )
		, 'manage_options', 'ni-category-report' 
		, array(&$this,'AddMenuPage'));
		
		add_submenu_page('ni-sales-report'
		,__( 'Top Product', 'nisalesreport' )
		,__( 'Top Product', 'nisalesreport' )
		, 'manage_options', 'ni-top-product-report' 
		, array(&$this,'AddMenuPage'));
		
		
		do_action('ni_sales_report_menu','ni-sales-report');
		
		add_submenu_page('ni-sales-report'
		,__( 'Other Plugins', 'nisalesreport' )
		,__( 'Other Plugins', 'nisalesreport' )
		, 'manage_options'
		, 'ni-sales-report-addons' , array(&$this,'AddMenuPage'));
		
		
		
		
	}
	function AddMenuPage()
	{
		$page= $this->get_request("page");
		/*Order Item*/
		if($page=="ni-order-product")
		{	include_once('order-item.php');
			$initialize = new OrderItem();  
			$initialize->create_form();
		}
		/*Order Item*/
		if($page=="ni-sales-report")
		{	include_once('order-summary.php');
			$initialize = new Summary();  
			$initialize->init();
		}
		
		/*Order Item*/
		if($page=="ni-sales-report-addons")
		{	include_once('ni-sales-report-addons.php');
			$initialize = new ni_sales_report_addons(); 
			$initialize->page_init(); 
		}
		if($page=="ni-category-report")
		{	include_once('ni-category-report.php');
			$initialize = new Ni_Category_Report(); 
			$initialize->page_init(); 
		}
		if ($page=="ni-top-product-report"){
			include_once('ni-top-product-report.php');
			$initialize = new Ni_Top_Product_Report(); 
			$initialize->page_init(); 
		}
	}
	function admin_init()
	{
		if(isset($_REQUEST['btn_print'])){
			include_once('order-item.php');
			$obj = new OrderItem();
			$obj->get_print_content();
			die;
		}	
	}
	public function activation() {
      // To override
    }	
	 // Called when the plugin is deactivated
    public function deactivation() {
      // To override
    }
	 // Called when the plugin is loaded
    public function loaded() {
      // To override
    }
}
}
?>