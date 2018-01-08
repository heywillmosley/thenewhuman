<?php 

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

?>

<div class="alert <?php echo $data['alert-type'] ; ?>">
<p class="message"><?php echo $data['alert-msg'] ; ?></p>

</div>

