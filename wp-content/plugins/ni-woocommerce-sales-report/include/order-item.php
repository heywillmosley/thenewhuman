<?php
if ( ! defined( 'ABSPATH' ) ) { exit;}
include_once('report-function.php');  
if( !class_exists( 'OrderItem' ) ) {
	class OrderItem extends ReportFunction{
  	public function __construct(){
	}
	
	function ajax_call()
	{
		$ajax_function= $this->get_request("ajax_function");
		if($ajax_function=="order_item")
		{ ?>
          <div class="wrap">
          	<?php $this->display_order_item();?>
          </div>
          <?php	
		}
	}
	/*On Page Start Create The Form*/
	function create_form(){
	$today = date_i18n("Y-m-d");
	?>
    <div class="wooreport_container">
       <div class="wooreport_content">
          <div class="wooreport_search_form">
              <form id="frmOrderItem" method="post" >
                <div class="wooreport_search_title"><?php _e('Order Product Sales Report', 'nisalesreport'); ?></div>
                <div  class="wooreport_search_row">
                  
                    <div  class="wooreport_field_wrapper">
                      <label for="billing_last_name"><?php _e('Select order period', 'nisalesreport'); ?>: </label>
                      <select name="select_order"  id="select_order"  style="width:250px; border:1px solid #00796B">
                          <option value="today"><?php _e('Today', 'nisalesreport'); ?></option>
                          <option value="yesterday"><?php _e('Yesterday', 'nisalesreport'); ?></option>
                          <option value="last_7_days"><?php _e('Last 7 days', 'nisalesreport'); ?></option>
                          <option value="last_10_days"><?php _e('Last 10 days', 'nisalesreport'); ?></option>
                          <option value="last_30_days"><?php _e('Last 30 days', 'nisalesreport'); ?></option>
                          <option value="last_60_days"><?php _e('Last 60 days', 'nisalesreport'); ?></option>
                          <option value="this_year"><?php _e('This year', 'nisalesreport'); ?></option>
                    </select>
                   </div>
                   <div  class="wooreport_field_wrapper">
                      <label for="order_id"><?php _e('Order No.', 'nisalesreport'); ?></label>
                      <input id="order_no" name="order_no" type="text">
                   </div>
                   <div style="clear:both"></div>
                </div>
                <div style="clear:both"></div>
                <div  class="wooreport_search_row">
                   <div  class="wooreport_field_wrapper">
                      <label for="billing_first_name"><?php _e('Billing First Name', 'nisalesreport'); ?>:</label>
                      <input id="billing_first_name" name="billing_first_name" type="text" >
                   </div>
                   <div  class="wooreport_field_wrapper">
                      <label for="billing_email"><?php _e('Billing Email', 'nisalesreport'); ?>:</label>
                      <input id="billing_email" name="billing_email" type="text">
                   </div>
                  
                   <div style="clear:both"></div>
                </div>
                <div style="clear:both"></div>
                <div  class="wooreport_search_row">
                   <div style="padding:5px; padding-right:23px;">
                      <input type="submit" value="<?php _e('Search', 'nisalesreport'); ?>" class="wooreport_button ni_print" />
                      <div style="clear:both"></div>
                   </div>
                   <div style="clear:both"></div>
                </div>
                <input type="hidden"  name="action" id="action" value="sales_order"/>
                 <input type="hidden"  name="ajax_function" id="ajax_function" value="order_item"/>
                 <input type="hidden" name="page" id="page" value="<?php echo isset($_REQUEST["page"])?$_REQUEST["page"]:''; ?>" />
             </form>
          </div>
          <div style="margin-top:20px;">
             <div class="_ajax_response"></div>
          </div>
       </div>
    </div> 
	<div class="ajax_content"></div>
      
		<?php	
	}
	function display_order_item($content="DEFAULT"){  
		//echo $content;
		$item_total = 0;
		$tax_total  = 0;
		$qty		=0;
		$order_item=$this->get_order_item();
		
		$order_item =  apply_filters('ni_sales_report_order_product_report_rows', $order_item );	
		
		$columns = $this->get_sales_report_columns();
		
		//$billing_first_name  = $this->get_request("billing_first_name",'',true);
		//$billing_email		 = $this->get_request("billing_email",'',true);
		
		//$this->print_data($order_item);
		$columns_total = array();
		if(count($order_item)> 0){
			?>
            <?php if ($content=="DEFAULT"): ?>
            <div style="text-align:right;margin-bottom:10px">
            <form id="ni_frm_sales_order" action="" method="post">
               <input type="submit" value="Print"  class="print_hide  ni_print wooreport_button" name="btn_print" id="btn_print" />
               <input type="hidden" name="select_order" value="<?php echo $this->get_request("select_order");  ?>" />
              <input type="hidden" name="order_no" value="<?php echo $this->get_request("order_no");  ?>" />
               <input type="hidden" name="billing_first_name" value="<?php echo  $this->get_request("billing_first_name",'',true);  ?>" />
              <input type="hidden" name="billing_email" value="<?php echo $this->get_request("billing_email",'',true);  ?>" />
                
                
            </form>
            </div>
            <?php endif; ?>
            <?php //echo admin_url("post.php")."?action=edit&post=375"; ?>
           
			 <table class="wooreport_default_table">
            	<thead>
                	<tr>
                        <?php foreach($columns  as $key=>$value): ?>
                        	<th><?php echo $value; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
           <?php
			foreach($order_item as $k => $v){
				
				//$this->print_data($v);
				$td_class = "";
				//$item_total += isset($v->line_total)?$v->line_total:0;
				//$tax_total 	+= isset($v->line_tax)?$v->line_tax:0;
			   // $qty 		+= isset($v->qty)?$v->qty:0;
			
				if (isset($columns_total["qty"])){
					
					$columns_total["qty"] += isset($v->qty)?$v->qty:0;
				}else{
					$columns_total["qty"] = isset($v->qty)?$v->qty:0;
				}
				if (isset($columns_total["line_total"])){
					$columns_total["line_total"] +=isset( $v->line_total)?$v->line_total:0;
				}else{
					$columns_total["line_total"] = isset($v->line_total)?$v->line_total:0;
				}
				if (isset($columns_total["line_tax"])){
					$columns_total["line_tax"] += isset($v->line_tax)?$v->line_tax:0;
				}else{
					$columns_total["line_tax"] = isset($v->line_tax)?$v->line_tax:0;
				}
				
				//$this->print_data($columns_total);
			}
			
			?>
            <?php foreach($order_item  as $row_key=>$row_value): ?>
            	<?php 
				$ahref_order_id = isset($row_value->order_id)?$row_value->order_id:0;
				$admin_url = admin_url("post.php")."?action=edit&post=".$ahref_order_id;
				?>
                <tr>
                    <?php foreach($columns  as $col_key=>$col_value): ?>
                        <?php switch($col_key): case 1: break; ?>
                        
                            <?php case "price": ?>
                            <?php $td_class = "style=\"text-align:right\""; ?>
                            <?php $td_vale = wc_price($row_value->line_total/$row_value->qty);   ?>
                            <?php break; ?>
                            
                            <?php case "order_status": ?>
                            <?php $td_vale =  ucfirst ( str_replace("wc-","", $row_value->order_status));   ?>
                            <?php break; ?>
                            
                            <?php case "billing_country": ?>
                            <?php $td_vale = $this->get_country_name($row_value->billing_country) ;  ?>
                            <?php break; ?>
                            
                            <?php case "order_id": ?>
                            <?php $td_vale = "<a href=\"". $admin_url ."\" target=\"_blank\">". $row_value->order_id. "</a>"   ?>
                            <?php break; ?>
                            
                            <?php case "line_tax": ?>
                            <?php case "line_total": ?>
                            <?php $td_class = "style=\"text-align:right\""; ?>
                            <?php $td_vale =  wc_price(isset($row_value->$col_key)?$row_value->$col_key:"0"); ?>
                            <?php break; ?>
                            
                            <?php default; ?>
                             <?php $td_vale = isset($row_value->$col_key)?$row_value->$col_key:""; ?>
                        <?php endswitch; ?>
                         <?php $td_class = ""; ?>
                     	<td <?php echo $td_class; ?>><?php echo $td_vale ;  ?></td>
                    <?php endforeach; ?>
                   
                </tr>
            <?php endforeach; ?>	
            </tbody>
              </table>
            	<div style="clear:both; padding-bottom:50px"></div>
                <table class="wooreport_default_table">
                <thead>
                    <tr>
                        <th><?php _e('Quantity Total', 'nisalesreport'); ?></th> 
                        <th><?php _e('Line Tax Total', 'nisalesreport'); ?></th> 
                        <th><?php _e('Line Total', 'nisalesreport'); ?></th>     
                    </tr>
                </thead>
                <tbody>
                	<tr>
                    	<td><?php echo isset($columns_total["qty"])?$columns_total["qty"]:0 ?></td>
                        <td><?php echo wc_price(isset($columns_total["line_tax"])?$columns_total["line_tax"]:0); ?></td>
                        <td><?php echo  wc_price(isset($columns_total["line_total"])?$columns_total["line_total"]:0); ?></td>
                    </tr>
                </tbody>
                </table>
           	<?php 
		}
	}
	function get_sales_report_columns(){
		$columns  =array();
		$columns["order_id"] =  __('#ID', 'nisalesreport');
		$columns["order_date"]		   =  __('Order Date', 'nisalesreport');
		$columns["billing_first_name"] =  __('First Name', 'nisalesreport');
		$columns["billing_email"] =  __('Email', 'nisalesreport');
		$columns["billing_country"] =  __('Country', 'nisalesreport');
		$columns["order_currency"] =  __('Currency', 'nisalesreport');
		$columns["payment_method_title"] =  __('Payment', 'nisalesreport');
		$columns["order_status"] =  __('Status', 'nisalesreport');
		$columns["order_item_name"] =  __('Product', 'nisalesreport');
		$columns["qty"] =  __('Quantity', 'nisalesreport');
		$columns["price"] =  __('Price', 'nisalesreport');
		$columns["line_tax"] =  __('Line Tax', 'nisalesreport');
		$columns["line_total"] =  __('Line Total', 'nisalesreport');
		
		return  apply_filters('ni_sales_report_order_product_report_columns', $columns );	
	}
	function get_order_item()
	{	$order_data =$this->get_query_data("DEFAULT");
		if(count($order_data)> 0){
			foreach($order_data as $k => $v){
				
				/*Order Data*/
				$order_id =$v->order_id;
				$order_detail = $this->get_order_detail($order_id);
				foreach($order_detail as $dkey => $dvalue)
				{
						$order_data[$k]->$dkey =$dvalue;
					
				}
				/*Order Item Detail*/
				$order_item_id = $v->order_item_id;
				$order_item_detail= $this->get_order_item_detail($order_item_id );
				foreach ($order_item_detail as $mKey => $mValue){
						$new_mKey = $str= ltrim ($mValue->meta_key, '_');
						$order_data[$k]->$new_mKey = $mValue->meta_value;		
				}
			}
		}
		else
		{
			_e("no record found","nisalesreport") ;
		}
		return $order_data;
	}
	function get_query_data($type="DEFAULT")
	{
		global $wpdb;	
		$today 				 = date_i18n("Y-m-d");
	    $select_order 		 = $this->get_request("select_order","today");
		$order_no			 = $this->get_request("order_no");
		$order_no 			 = $this->get_request("order_no");
		$billing_first_name  = $this->get_request("billing_first_name",'',true);
		$billing_email		 = $this->get_request("billing_email",'',true);
		//echo json_encode($_REQUEST);
		
		$query = "SELECT
				posts.ID as order_id
				,posts.post_status as order_status
				,woocommerce_order_items.order_item_id as order_item_id
				, date_format( posts.post_date, '%Y-%m-%d') as order_date 
				,woocommerce_order_items.order_item_name
				FROM {$wpdb->prefix}posts as posts	";		
				$query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items ON woocommerce_order_items.order_id=posts.ID ";
				
				if (strlen($billing_first_name)>0 && $billing_first_name!="" ){
					$query .= "  LEFT JOIN  {$wpdb->prefix}postmeta as billing_first_name ON billing_first_name.post_id=posts.ID ";
				}
				if (strlen($billing_email)>0 && $billing_email!="" ){
						$query .= " LEFT JOIN {$wpdb->prefix}postmeta as billing_email ON billing_email.post_id = posts.ID ";
				}
				
				$query .= "  WHERE 1 = 1";  
				$query .= " AND	posts.post_type ='shop_order' ";
				$query .= "	AND woocommerce_order_items.order_item_type ='line_item' ";
				if (strlen($billing_first_name)>0 && $billing_first_name!="" ){
					$query .= " AND	billing_first_name.meta_key ='_billing_first_name' ";
					$query .= " AND billing_first_name.meta_value LIKE '%{$billing_first_name}%'";	
				}
				if (strlen($billing_email)>0 && $billing_email!="" ){
					$query .= " AND billing_email.meta_key = '_billing_email'";	 
					$query .= " AND billing_email.meta_value LIKE '%{$billing_email}%'";	
				}
				$query .= "		AND posts.post_status IN ('wc-pending','wc-processing','wc-on-hold', 'wc-completed' ,'wc-cancelled' ,  'wc-refunded' ,'wc-failed')";
						
				if ($order_no){
					$query .= " AND   posts.ID = '{$order_no}'";
				}		
				//$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$start_date}' AND '{$end_date}'";		
					//AND DATE_ADD(CURDATE(), INTERVAL 1 day)	
				 switch ($select_order) {
					case "today":
						$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";
						break;
					case "yesterday":
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') = date_format( DATE_SUB(CURDATE(), INTERVAL 1 DAY), '%Y-%m-%d')";
						break;
					case "last_7_days":
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '%Y-%m-%d') AND   '{$today}' ";
						break;
					case "last_10_days":
						$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '%Y-%m-%d') AND   '{$today}' ";
						break;	
					case "last_30_days":
							$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 30 DAY), '%Y-%m-%d') AND   '{$today}' ";
					 case "last_60_days":
							$query .= " AND  date_format( posts.post_date, '%Y-%m-%d') BETWEEN date_format(DATE_SUB(CURDATE(), INTERVAL 60 DAY), '%Y-%m-%d') AND   '{$today}' ";		
						break;	
					case "this_year":
						$query .= " AND  YEAR(date_format( posts.post_date, '%Y-%m-%d')) = YEAR(date_format(CURDATE(), '%Y-%m-%d'))";			
						break;		
					default:
						$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";
				}
			$query .= "order by posts.post_date DESC";	
				//AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '2014-10-21' AND '2014-10-22'
		 if ($type=="ARRAY_A") /*Export*/{
		 	$results = $wpdb->get_results( $query, ARRAY_A );
		 }
		 if($type=="DEFAULT") /*default*/{
		 	$results = $wpdb->get_results( $query);	
		 }
		 if($type=="COUNT") /*Count only*/	{
		 
		 	$results = $wpdb->get_var($query);
		 }
			//echo $query;
			//echo mysql_error();
		return $results;	
	}
	function get_order_item_detail($order_item_id)
	{
		global $wpdb;
		$sql = "SELECT
				* FROM {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta			
				WHERE order_item_id = {$order_item_id}
				";
				
		$results = $wpdb->get_results($sql);
		return $results;			
	}
	function get_order_detail($order_id)
	{
		$order_detail	= get_post_meta($order_id);
		$order_detail_array = array();
		foreach($order_detail as $k => $v)
		{
			$k =substr($k,1);
			$order_detail_array[$k] =$v[0];
		}
		return 	$order_detail_array;
	}
	function get_print_content(){
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Print</title>
		<link rel='stylesheet' id='sales-report-style-css'  href='<?php echo  plugins_url( '../assets/css/sales-report-style.css', __FILE__ ); ?>' type='text/css' media='all' />
		</head>
		
		<body>
			<?php 
				 $this->display_order_item("PRINT");
			?>
		  <div class="print_hide" style="text-align:right; margin-top:15px"><input type="button" value="Back" onClick="window.history.go(-1)"> <input type="button" value="Print this page" onClick="window.print()">	</div>
		 
		</body>
		</html>

	<?php
	}
}
}
?>