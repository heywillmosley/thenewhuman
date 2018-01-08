<?php 

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

?>
<div class="esig-container">
	
	<div class="navbar-header agree-container">
		<span class="agree-text" id="an-admin"> <?php _e('You\'re an Admin.', 'esig' );?>
			
			
			<?php 
			
			if (array_key_exists('mode', $data) && $data['mode']==1)
			{
			echo '<span class="esig-preview-mode">(';
			 _e('Preview Mode','esig');
			 echo ')</span>';
			}
			 ?>
			
		</span>
	</div>

	<div class="nav navbar-nav navbar-right footer-btn">
		<?php if (array_key_exists('print_button', $data)) { echo $data['print_button']; } ?>
			<?php if (array_key_exists('pdf_button', $data)) { echo $data['pdf_button'];} ?>
	</div>
</div>