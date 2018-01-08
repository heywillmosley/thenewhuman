<?php 

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

?>

<tr id="post-<?php echo $data['document_id']; ?>" class="post-<?php echo $data['document_id']; ?> type-post status-publish format-standard hentry category-uncategorized <?php echo $data['alternate_class']; ?> iedit author-self level-0 <?php if(array_key_exists('sent-error',$data)) echo $data['sent-error']; ?>" valign="top">
	<th scope="row" class="check-column">
		<label class="screen-reader-text" for="cb-select-11"><?php echo stripslashes_deep($data['document_title']); ?></label>
			<input id="cb-select-11" type="checkbox" name="esig_document_checked[]" value="<?php echo $data['document_id']; ?>">
			<div class="locked-indicator"></div>
	</th>
		
	<td class="post-title page-title column-title">
	
		<strong><a class="row-title" href="<?php echo $data['action_url']; ?>" title="<?php echo $data['action']; ?>"><?php echo stripslashes_deep($data['document_title']); ?></a></strong>
		<div class="locked-info">
			<span class="locked-avatar"></span> 
			<span class="locked-text"></span>
		</div>
		
		<div class="row-actions">
		<?php echo $data['row_actions']; ?>
		<?php echo $data['more_actions']; ?>
		</div>
	</td>

	<td><span title="<?php if (array_key_exists('signer_email', $data)) { echo $data['signer_email']; } ?>"><?php if (array_key_exists('signer_name', $data)) { echo stripslashes_deep($data['signer_name']); } ?></span></td>
	<td><?php if (array_key_exists('latest_activity', $data)) { echo $data['latest_activity']; } ?></td>
	<td><?php if (array_key_exists('invitation_date', $data)) { echo $data['invitation_date']; } ?></td>
</tr>