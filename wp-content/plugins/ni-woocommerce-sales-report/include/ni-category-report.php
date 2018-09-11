<?php
if ( ! defined( 'ABSPATH' ) ) { exit;}
include_once('report-function.php'); 
class Ni_Category_Report extends ReportFunction{
	public function __construct(){
	}
	function page_init(){
	?>
    <div class="wooreport_container">
       <div class="wooreport_content">
          <div class="wooreport_search_form">
             <form method="post" name="frmCategoryReport" id="frmCategoryReport">
                <div class="wooreport_search_title"><?php _e('Category Report', 'nisalesreport'); ?></div>
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
                   <div  class="wooreport_field_wrapper" style="display:none">
                      <label for="order_id"><?php _e('Order No.', 'nisalesreport'); ?></label>
                      <input id="order_no" name="order_no" type="text">
                   </div>
                    <div  class="wooreport_search_row">
                   <div style="padding:5px; padding-right:23px;">
                      <input type="submit" value="<?php _e('Search', 'nisalesreport'); ?>" class="wooreport_button ni_print" />
                      <div style="clear:both"></div>
                   </div>
                   <div style="clear:both"></div>
                </div>
                   <div style="clear:both"></div>
                </div>
                	<input type="hidden" name="action" value="sales_order">
        			<input type="hidden" name="ajax_function" value="category_report">
        			<input type="hidden" name="page" id="page" value="<?php echo isset($_REQUEST["page"])?$_REQUEST["page"]:''; ?>" />
             </form>
          </div>
          <div style="margin-top:20px;">
             <div class="_ajax_response"></div>
          </div>
       </div>
    </div>
    <div style="padding-bottom:20px;"></div>
    <div class="ajax_content"></div>
    <?php	
	}
	function get_query(){
		global $wpdb;	
		$today = date_i18n("Y-m-d");
		
	    $select_order = $this->get_request("select_order","today");
		
		
		$query = "SELECT ";
		$query .= "	date_format( posts.post_date, '%Y-%m-%d') as order_date  ";
		$query .= "	,product_id.meta_value as product_id";
		$query .= "	,SUM(line_total.meta_value) as line_total";
		//$query .= "	,order_items.order_item_name as product_name";
		$query .= "	,terms.name as product_category";
		$query .= " FROM {$wpdb->prefix}posts as posts ";
		$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
		$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as product_id ON product_id.order_item_id=order_items.order_item_id ";
		
		$query .= " LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as line_total ON line_total.order_item_id=order_items.order_item_id ";
		
		
		
		/*Cat*/
		$query .= " LEFT JOIN  {$wpdb->prefix}term_relationships as relationships ON relationships.object_id=product_id.meta_value";
		$query .= " LEFT JOIN  {$wpdb->prefix}term_taxonomy as taxonomy ON taxonomy.term_taxonomy_id=relationships.term_taxonomy_id";
		$query .= " LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=taxonomy.term_id";
		/*End Cat*/
		
		
		
		$query .= " WHERE 1=1 ";
		
		$query .= " AND posts.post_type ='shop_order'  ";
		$query .= " AND order_items.order_item_type ='line_item'  ";
		$query .= " AND product_id.meta_key ='_product_id'";
		
		$query .= " AND line_total.meta_key ='_line_total'";
		
		
		$query .= " AND taxonomy.taxonomy ='product_cat'";
		
		//$query .= " AND date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";	
		
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
		
		
		$query .= " GROUP BY terms.slug";	
		
		$row = $wpdb->get_results( $query);
		//$this->print_data($row);
		return $row;	
	}
	function get_ajax(){
		$this->get_table();
	}
	function get_table(){
		$row =  $this->get_query();
		?>
        <div class="data-table">
         <table class="wooreport_default_table">
        	<thead>
                <tr>
                    <th><?php _e("Category Name","nisalesreport") ?></th>
                    <th style="text-align:right"><?php _e("Category Total","nisalesreport") ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($row) == 0): ?>
            	<tr>
                	<td colspan="2"><?php _e("no record found","nisalesreport") ?></td>
                </tr>
            <?php return; ?>    
            <?php endif; ?>
            <?php
            foreach($row as $key=>$value){
			?>
            	<tr>
                	<td><?php echo  $value->product_category;?></td>
                    <td style="text-align:right"><?php echo wc_price( $value->line_total);?></td>
                </tr>
            <?php
            }
            ?>
        	</tbody>
        </table>
        </div>
        <?php
	}
}
?>