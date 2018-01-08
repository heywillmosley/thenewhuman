<link rel="stylesheet" href="https://unpkg.com/purecss@0.6.2/build/pure-min.css" integrity="sha384-UQiGfs9ICog+LwheBSRCt1o5cbyKIHbwjWscjemyBMT9YCUMZffs6UqUTd0hObXD" crossorigin="anonymous">
<?php
/*
Template Name: Vendor Shop
*/
?>

<?php get_header(); ?>

<?php



?>


<div class="container">
	<div class="row">
		
		<?php FLTheme::sidebar('left'); ?>
		
		<div class="fl-content <?php FLTheme::content_class(); ?>">
			
			<?php
			// Get the current blog id
			$original_blog_id = get_current_blog_id(); 

			// All the blog_id's to loop through
			//switch_to_blog( 1 ); 
			?>
      <h2>Profusion</h2>
			 <div class="pure-g">
         <ul class="products">
         
          <?php
            $args = array(
              'post_type' => 'product',
              'posts_per_page' => 30,
              'product_cat' => "protogenx",
              );
            $loop = new WP_Query( $args );
            if ( $loop->have_posts() ) {
              while ( $loop->have_posts() ) : $loop->the_post();
                echo '<div class="pure-u-1-4">';
                wc_get_template_part( 'content', 'product' );
                echo '</div>';
              endwhile;
            } else {
              echo __( 'No products found' );
            }
            wp_reset_postdata();
          ?>
           </ul><!-- end products -->
        </div><!--pure-g-->
			<?php switch_to_blog( $original_blog_id ); // Switch back to the current blog  ?>
      
		</div><!-- end <div class="fl-content -->
		
		<?php FLTheme::sidebar('right'); ?>
		
	</div>
</div>

<?php get_footer(); ?>