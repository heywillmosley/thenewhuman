<?php
 /*
	Plugin Name: Bulk delete comments
	Description: Allows to delete all comments with single click. you can select all unapproved and pending comments or comments based on category / post.
	Author: Shah Alom
	Version: 1.2
*/
  
  add_action('admin_menu','dac_menu');
  
  function dac_menu()
  {
	add_menu_page('Bulk Delete Comments', 'Bulk Delete Comments', 'manage_options', 'delete_all_comments', 'dac_interace_page', 'dashicons-trash',null );
  }
  
  function dac_interace_page()
  {
	$error=0;
	if(current_user_can('edit_posts'))
	{
		global $wpdb;
		$notificationMessage="";
		
	if(isset($_POST['dac_comments']) && !empty($_POST['dac_comments']))
   {	
	 if (isset( $_POST['dac_comments']) &&  wp_verify_nonce( $_POST['dac_comments'], 'dac')) 
	{
		if(isset($_POST['dallc']) && !empty($_POST['dallc']))
		{
			$results = $wpdb->get_results( 
						$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}comments",'') 
					 );
			
		 if(!empty($results))
		{
			$query=$wpdb->prepare("delete from {$wpdb->prefix}comments",'');
			$response=$wpdb->query($query);
			if($response)
			{
				$query=$wpdb->prepare("update {$wpdb->prefix}posts set comment_count=%d",0);
				$response=$wpdb->query($query);
				$notificationMessage="All The Comments Deleted Successfully";
			}
			else
			{
				$error=1;
				$notificationMessage="Sorry! Something Went Wrong";
			}
		}
		else
		{
			$error=1;
			$notificationMessage="There are no Comments to be deleted";
		}
		
		}
		else if(isset($_POST['dsc']) && !empty($_POST['dsc']))
		{
			$results = $wpdb->get_results( 
						$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}comments where comment_approved=%s",'spam') 
					 );
				 
					
		if(!empty($results))
		{
			$query=$wpdb->prepare("delete from {$wpdb->prefix}comments where comment_approved=%s",'spam');
			$response=$wpdb->query($query);
			if($response)
			{
				$notificationMessage="Spam Comments Delete Successfully";	
			}
			else
			{
				$error=1;
				$notificationMessage="Sorry! Something Went Wrong";
			}
		}
		else
		{
			$error=1;
			$notificationMessage="There is no spam Comments to be deleted";
		}
		}
		else if(isset($_POST['dac']) && !empty($_POST['dac']))
		{
			$results = $wpdb->get_results( 
						$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}comments where comment_approved=%s",'0') 
					 );
				 
					
		if(!empty($results))
		{
			$query=$wpdb->prepare("delete from {$wpdb->prefix}comments where comment_approved=%s",'0');
			$response=$wpdb->query($query);
			if($response)
			{
				
				$notificationMessage="Unapproved Comments Delete Successfully";	
			}
			else
			{
				$error=1;
				$notificationMessage="Sorry! Something Went Wrong";
			}
		}
		else
		{
				$error=1;
				$notificationMessage="There is no unapproved Comments to be deleted";
		}
			
		}
		else if(isset($_POST['dtc']) && !empty($_POST['dtc']))
		{
			$results = $wpdb->get_results( 
						$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}comments where comment_approved=%s",'trash') 
					 );
				 
					
		if(!empty($results))
		{
			$query=$wpdb->prepare("delete from {$wpdb->prefix}comments where comment_approved=%s",'trash');
			$response=$wpdb->query($query);
			if($response)
			{
				$notificationMessage="Trash Comments Delete Successfully";	
			}
			else
			{
				$error=1;
				$notificationMessage="Sorry! Something Went Wrong";
			}
		}
		else
		{
			$error=1;
			$notificationMessage="There is no trash Comments to be deleted";
		}
			
		}
		else if(isset($_POST['ducp']) && !empty($_POST['ducp']))
		{
			$postId=sanitize_text_field(intval($_POST['postid']));
			$results = $wpdb->get_results( 
						$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}comments where comment_approved=%s and comment_post_ID=%d",'0',$postId) 
					 ) ;
				 
					
			if(!empty($results))
			{
				$query=$wpdb->prepare("delete from {$wpdb->prefix}comments where comment_approved=%s and comment_post_ID=%d",'0',$postId);
				
				$response=$wpdb->query($query);
				if($response)
				{
					$notificationMessage="Unapproved Comments for selected post deleted Successfully";	
				}
				else
				{
					$error=1;
					$notificationMessage="Sorry! Something Went Wrong";
				}
			}
			else
			{
					$error=1;
					$notificationMessage="There is no unapproved Comments for selected post";
			}
		
		}
		else if(isset($_POST['dapc']) && !empty($_POST['dapc']))
		{
			$postId=sanitize_text_field(intval($_POST['postid1']));
			if($postId)
			{
				$results = $wpdb->get_results( 
							$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}comments where comment_post_ID=%d",$postId) 
						 );
				
				 if(!empty($results))
				{
					$query=$wpdb->prepare("delete from {$wpdb->prefix}comments",'');
					$response=$wpdb->query($query);
					if($response)
					{
						$query=$wpdb->prepare("update {$wpdb->prefix}posts set comment_count=%d where ID=%d",0,$postId);
						$response=$wpdb->query($query);
						$notificationMessage="All The Comments Deleted For selected post Successfully";
					}
					else
					{
						$error=1;
						$notificationMessage="Sorry! Something Went Wrong";
					}
				}
				else
				{
					$error=1;
					$notificationMessage="There are no Comments for selected post";
				}
			}
			else
			{
				die("Invalid Data!!! Unable To Process");
			}
		}
		else if(isset($_POST['ducc']) && !empty($_POST['ducc']))
		{
			$catId=sanitize_text_field(intval($_POST['catid']));
			if($catId)
			{
				$results = $wpdb->get_results( 
							$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}term_relationships where term_taxonomy_id=%d",$catId) 
						 );
				$postIDs=array();
				foreach($results as $key=>$val)
				{
					$postIDs[]=$val->object_id;
				}
				$how_many=count($postIDs);
				$placeholders = array_fill(0, $how_many, '%d');
				$format = implode(', ', $placeholders);
				$comments=$wpdb->get_results( 
							$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}comments where comment_post_ID IN($format) and comment_approved='0'",$postIDs));                 ;
				
				if(!empty($comments))
				{
					 $query=$wpdb->prepare("delete FROM {$wpdb->prefix}comments where comment_post_ID IN($format) and comment_approved='0'",$postIDs);
					 $response=$wpdb->query($query);
					if($response)
					{
						$notificationMessage="Unapproved Comments for selected Category deleted Successfully";	
					}	
				}
				else
				{
					$error=1;
					$notificationMessage="There are no Unapproved comment for selected category";
				}
		}
		else
		{
				die("Invalid Data!!! Unable To Process");
		}
	}
	}
	else
	{
		die("Sorry! Your Noonce didn't verify");
	}
	}
	
	
	$statData = $wpdb->get_results( 
						$wpdb->prepare("select count(*) as total_comments, SUM(comment_approved='spam') as spamcount,  SUM(comment_approved='0') as unpcount,SUM(comment_approved='trash') as trashcount from wp_comments","") 
					 );

	
	
					 
	$postData = $wpdb->get_results( 
						$wpdb->prepare("SELECT *  FROM {$wpdb->prefix}posts",'') 
					 );
		$postSelect='<select name="postid">';
		$postSelect1='<select name="postid1">';
		foreach($postData as $key=>$val)
		{
			$postSelect.='<option value="'.$val->ID.'">'.$val->post_title.'</option>';
			$postSelect1.='<option value="'.$val->ID.'">'.$val->post_title.'</option>';
		}
		$postSelect.='</select>';
		$postSelect1.='</select>';	
		$dacCategories=get_categories();
		$catSelect='<select name="catid">';
		foreach($dacCategories as $key=>$val)
		{
			$catSelect.='<option value="'.$val->term_id.'">'.$val->name.'</option>';
		}
		$catSelect.='</select>';
		
		$html.='<div  id="poststuff">
    <div>
        <h2>Bulk Delete Comments</h2><br />';
	if(isset($_POST) && !empty($_POST))
	{
		$class="updated";
		if($error==1)
			$class="notice notice-error";
		
		
		$html.='<div id="message" class="'.$class.' fade" style="margin:0px 20px 10px 2px;"><p><strong>'.$notificationMessage.'</strong></p></div>';
	}
	$html.='<div class="postbox">
			<h3 class="hndle">
			 <span>Delete Comments By Following Options</span>
			</h3>
			<div class="inside">
    <form action="" method="post" onsubmit="return confirm(\'Are you Sure to delete it\')">
	<table>
		<tr>
		<tbody>
			<td width="300"><label>Delete All Comments</label></td><td width="300">Number of Comments: '.$statData[0]->total_comments.'</td><td><input type="submit" name="dallc" value="Delete" class="button button-primary"/></td></tr>
		<tr><td><label>Delete Spam Comments</label></td><td>Number of Spam Comments: '.$statData[0]->spamcount.'</td><td><input type="submit" name="dsc" value="Delete" class="button button-primary"/></td></tr>
		<tr><td><label>Delete Unapproved Comments</label></td><td>Number of Unapproved Comments: '.$statData[0]->unpcount.'</td><td><input type="submit" name="dac" value="Delete" class="button button-primary"/></td></tr>
		<tr><td><label>Delete Trash Comments</label></td><td>Number of Trash Comments: '.$statData[0]->trashcount.'</td><td><input type="submit" name="dtc" value="Delete" class="button button-primary"/></td></tr>
		
		</tbody></table>'.wp_nonce_field( 'dac', 'dac_comments').'
			</form></div></div></div><p class="update-nag" style="margin:0px 20px 10px 2px;">Warning: Once Comments Deleted  Can\'t be restored.</p>';
			
		$html.='<div class="postbox">
			<h3 class="hndle">
			 <span>Delete Comments By Post</span>
			</h3>
			<div class="inside">
    <form action="" method="post" onsubmit="return confirm(\'Are you Sure to delete it\')">
	<table>
		
		<tr><td width="300"><label>Delete UnApproved Comments By Post/Category</label></td><td width="300">'.$postSelect.'</td><td><input type="submit" name="ducp" value="Delete" class="button button-primary"/></td></tr>	
		<tr><td><label>Delete All Comments By Post</label></td><td>'.$postSelect1.'</td><td><input type="submit" name="dapc" value="Delete" class="button button-primary"/></td></tr>
		<tr><td><label>Delete UnApproved Comments By Category</label></td><td>'.$catSelect.'</td><td><input type="submit" name="ducc" value="Delete" class="button button-primary"/></td></tr>	
	</table>'.wp_nonce_field( 'dac', 'dac_comments').'
			</form></div></div></div></div>';
		
		_e($html);
	}
	else
	{
		die("You don't have permission to access this page");
	}
  }
  
  
?>