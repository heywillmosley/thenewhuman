<?php
if ( ! defined( 'ABSPATH' ) ) { exit;}
include_once('report-function.php');
if( !class_exists( 'Ni_Top_Product_Report' ) ) { 
	class Ni_Top_Product_Report extends ReportFunction{
		public function __construct(){
		}
		function page_init(){
			$product_type =	isset($_REQUEST["top_product"])?$_REQUEST["top_product"] : 'today_top_product';
			$page =	isset($_REQUEST["page"])?$_REQUEST["page"] : '';
			$page_titles 				= array(
					'today_top_product'			=> __('Today Top Product',		'nisalesreportpro')
					,'yesterday_top_product'		 	=> __('Yesterday Top Product',	'nisalesreportpro')
					,'last_7_days_top_product'		=> __('Last 7 Days Top Product',	'nisalesreportpro')				
			);
			?>
            <h2 class="nav-tab-wrapper woo-nav-tab-wrapper hide_for_print">
			<div class="responsive-menu"><a href="#" id="menu-icon"></a></div>
			<?php            	
			   foreach ( $page_titles as $key => $value ) {
					echo '<a href="'.admin_url( 'admin.php?page='.$page.'&top_product=' . urlencode( $key ) ).'" class="nav-tab ';
					if ( $product_type == $key ) echo 'nav-tab-active';
					echo '">' . esc_html( $value ) . '</a>';
			   }
			?>
			</h2>
			<div style="margin:5px;">
            	<?php $this->get_top_product(); ?>
            </div>
			<?php
			
			
		}
		function get_top_product_columns(){
			$column = array();
			$column["order_item_name"] = __('Product Name', 'nisalesreport');
			$column["qty"] = __('Product Quantity', 'nisalesreport');
			$column["line_total"] = __('Line Total', 'nisalesreport');
			return $column;
		}
		function get_top_product_query(){
			global $wpdb;	
			 $product_type =	isset($_REQUEST["top_product"])?$_REQUEST["top_product"] : 'today_top_product';
			 $today 				 = date_i18n("Y-m-d");
		     $yesterday			 	 = date_i18n("Y-m-d",strtotime("-1 days"));
			 $last_7_days 		 	 = date_i18n('Y-m-d', strtotime('-7 days'));
			
			$query = " SELECT ";
 			//$query .= "	posts.ID as order_id";
			//$query .= "		,posts.post_status as order_status";
			//$query .= "		order_items.order_item_id as order_item_id";
			//$query .= "		, date_format( posts.post_date, '%Y-%m-%d') as order_date ";
			$query .= "		order_items.order_item_name";
			$query .= "		,product_id.meta_value as  product_id";
			$query .= "		,variation_id.meta_value as  variation_id";
			$query .= "		,SUM(line_total.meta_value) as  line_total";
			$query .= "		,SUM(qty.meta_value) as  qty";
			$query .= "		FROM {$wpdb->prefix}posts as posts	";		
				
				$query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_items as order_items ON order_items.order_id=posts.ID ";
				
				$query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as product_id ON product_id.order_item_id=order_items.order_item_id ";
				$query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as variation_id ON variation_id.order_item_id=order_items.order_item_id ";
				
				$query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as line_total ON line_total.order_item_id=order_items.order_item_id ";
				
				$query .= "  LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as qty ON qty.order_item_id=order_items.order_item_id ";
				
				$query .= "  WHERE 1 = 1";  
				$query .= " AND	posts.post_type ='shop_order' ";
				$query .= "	AND order_items.order_item_type ='line_item' ";
				
				$query .= "	AND product_id.meta_key ='_product_id' ";
				$query .= "	AND variation_id.meta_key ='_variation_id' ";
				$query .= "	AND line_total.meta_key ='_line_total' ";
				$query .= "	AND qty.meta_key ='_qty' ";
				
				if ("today_top_product" == $product_type ){
					$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$today}' AND '{$today}'";
					$query .= " GROUP BY 	 product_id.meta_value, variation_id.meta_value	 ";
				}
				if ("yesterday_top_product" == $product_type ){
					$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$yesterday}' AND '{$yesterday}'";
					$query .= " GROUP BY product_id.meta_value, variation_id.meta_value	 ";
				}
				if ("last_7_days_top_product" == $product_type ){
					$query .= " AND   date_format( posts.post_date, '%Y-%m-%d') BETWEEN '{$last_7_days}' AND '{$today}'";
					$query .= " GROUP BY 	product_id.meta_value, variation_id.meta_value	 ";
				}
				$query .= "order by SUM(line_total.meta_value) DESC ";	
				$query .= " LIMIT 10";
				//echo $query ;
				
				$results = $wpdb->get_results( $query);	
				
				//$this->print_data($results);
				return $results;
		}
		function get_top_product(){
			$columns = $this->get_top_product_columns();
			$rows = $this->get_top_product_query();
			?>
           
            
            <table class="wooreport_default_table">
            	<thead>
                	<tr>
						<?php foreach($columns as $col_key=>$col_value): ?>
                            <th><?php echo  $col_value; ?></th>
                        <?php endforeach; ?>
                        
                    </tr>
                </thead>
            	<tbody>
                 <?php if (count($rows)==0): ?>
                 	<tr>
                    	<td colspan="3" style="text-align:left; font-size:16px"><?php _e('No product found', 'nisalesreport'); ?></td>
                    </tr>
                    </tbody>
                 <?php endif; ?>
                 <?php foreach($rows  as $row_key=>$row_value): ?>
                 
                 	<tr>
                    	<?php foreach($columns  as $col_key=>$col_value): ?>
                        	<?php switch($col_key): case 1: break; ?>
                            	<?php default; ?>
                             	<?php $td_value = isset($row_value->$col_key)?$row_value->$col_key:""; ?>
                            <?php endswitch; ?>
                            <td><?php echo $td_value ;  ?></td>
                        <?php endforeach; ?>
                    </tr>	
                 <?php endforeach; ?>
                 </tbody>
            </table>
            <?php
		}
	}
}
