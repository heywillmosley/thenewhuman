<?php   
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
if ( ! class_exists( 'ic_commerce_product_thumb' ) ) {
	require_once('ic_commerce_golden_functions.php');
	class ic_commerce_product_thumb extends IC_Commerce_Golden_Functions{
		
		public $constants 	=	array();
	
		public function __construct($constants = array()){			
			
			add_action('ic_commerce_golden_init',array($this, 'ic_commerce_golden_init'),101,2);
			
		}
		
		function ic_commerce_golden_init($constants = ''){
			$this->constants = $constants;
			
			add_action('ic_commerce_variation_page_data_grid', 		array($this,'ic_commerce_variation_page_data_grid'), 	100);
			add_action('ic_commerce_variation_page_grid_columns', 	array($this,'ic_commerce_variation_page_grid_columns'), 100);
			
			
		}
		
		function ic_commerce_variation_page_data_grid($order_items = '', $columns = '', $zero = '', $show_variation = ''){
	
			$variation_images 		= array();	
			$placeholder_img_src 	= esc_attr(wc_placeholder_img_src());
			$images_urls 			= array();	
			
			foreach($order_items as $key => $order_item){
				$variation_id 		= $order_item->variation_id;
				
				$product_id 		= $order_item->product_id;
				
				$product_id2 		= $product_id.'-'.$variation_id;
				
				if(!isset($variation_images[$product_id2])){
					
					if($variation_id && has_post_thumbnail($variation_id) ) {
						$image_id 	= get_post_thumbnail_id( $variation_id );
					} elseif ( has_post_thumbnail( $product_id ) ) {
						$image_id 	= get_post_thumbnail_id( $product_id );
					} elseif(($product_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $product_id ) ) {
						$image_id 	= get_post_thumbnail_id( $product_id );
					} else {
						$image_id = 0;
					}
					
					if($image_id == 0){
						$image_url 				= $placeholder_img_src;
					}else{
						$images_urls[$image_id] = isset($images_url[$image_id]) ? $images_url[$image_id] : wp_get_attachment_image_url($image_id,'thumbnail');
						
						$image_url 				= $images_urls[$image_id];
					}
					
					$image_url 				= empty($image_url) ? $placeholder_img_src : $image_url;
					
					$thumbnail_image		= '<img style="width:48px;height:48px;" src="'.$image_url.'" />';
					
					$thumbnailimage			= $thumbnail_image;
					
					$variation_images[$product_id2]		= $thumbnailimage;
				}
				
				$order_items[$key]->thumbnail_image 	= $variation_images[$product_id2];
			}
			return $order_items;
		}
		
		function ic_commerce_variation_page_grid_columns($columns = ''){				
			$columns2 					= $columns;			
			$columns 					= array();
			$image 						= esc_attr(wc_placeholder_img_src());
			
			$show_product_image	= $this->get_setting('show_product_image',		$this->constants['plugin_options'], 0);
			
			//$thumbnail_image			= '<img style="width:48px;height:48px;" src="'.$image.'" />';
			$thumbnail_image			= 'Product Image';
			
			if($show_product_image == 1){
				$columns['thumbnail_image'] = $thumbnail_image;	
			}
			
			
			foreach($columns2 as $key => $label){
				$columns[$key] = $label;
			}
			
			return $columns;
		}
		
		
    }/*END CLASS*/
}