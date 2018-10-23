<?php
/*
===================================
� Copyright Webgility LLC 2007-2015
----------------------------------------
This file and the source code contained herein are the property of Webgility LLC
and are protected by United States copyright law. All usage is restricted as per 
the terms & conditions of Webgility License Agreement. You may not alter or remove 
any trademark, copyright or other notice from copies of the content.

The code contained herein may not be reproduced, copied, modified or redistributed in any form
without the express written consent by an officer of Webgility LLC.

File last updated		: 23/23/2011
Webgility  Desktop Version 	: v3.6
Webgility  Mobile Versions    : 1.3
Webgility  Compitable Version : v3.0
woocommerce plug in version : 1.6.1

*/

# Added for removing memory exhausted  problem of service file

if(((int)str_replace("M","",ini_get("memory_limit")))<128)
    ini_set("memory_limit","128M");


ini_set("display_errors","Off");
error_reporting(E_ALL && ~E_NOTICE && '~E_STRICT');
//error_reporting(E_ALL);
include_once('webgility-config.php');

if(!isset($_REQUEST['request'])) {
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="imagetoolbar" content="no" />
<title>Webgility Store Module</title> 
<meta name="DATE" content="08/03/2007" /> 
<link rel="icon" href="http://www.webgility.com/favicon.png" type="x-icon" /> 
<style>

.red_tr {
	background-color:#F7B7B7;
	color:#000000;
	font-weight:100;
	padding:5px 5px 5px 5px;
	margin-top:10px;
	text-align:left;
}
.red_tr a {
	color:#F7B7B7;
}
.green_tr {
	padding:5px 5px 5px 5px;
	text-align:left;
	font-size: 12px;
}
.green_tr a {
	color:#000000;
}
.red_btn_submit {
	background-color:#FF6600;
	color:#FFFFFF;
	font-weight:100;
	padding:2px 2px 2px 2px;
}
.error {
	color:#CC0000;
	font-weight:100;
	padding:2px;
}
.success {
	color:#009933;
	font-weight:100;
	padding:2px;
}

#footer {
	width:100%;
	text-align:center;
	color:#CCCCCC;
}
#wrap {
	text-align:center;
	padding:10px;
	font-size:16px;
	color:#000;
	font-weight: bold;
}
#centerdiv {
	width:700px;
	overflow:hidden;
	text-align:center;
	position:static;
	vertical-align:middle;
}
#information {
	text-align:left;
	background-color: #CCC;
	padding: 5px;
}
</style>

		</head>
		<body id="innerpage-bg" >
		<div id="wrap">Webgility Store Module (v<?php echo $storeMduleVersion; ?>)</div>
		<div id="content" align="center">

		<div id="centerdiv">
		<div class="green_tr">Compatible with WooCommerce version: <?php echo $cartCompitableVersion;?></div>
		<?php if(extension_loaded("curl") && extension_loaded("json") && extension_loaded("mcrypt") && phpversion()>5) {?>
										<div id="information">Extensions required</div>
										<?php } else {?>	
										<div id="information">Extensions required</div>
										<?php }?>
										
										<?php if(extension_loaded("curl")){?>
										<div class="green_tr">PHP Curl &nbsp;:&nbsp;<span style=" color:#006600; padding:55px;" > Ok </span></div>
										<?php }else{?>
										<div class="red_tr">PHP Curl &nbsp;:&nbsp; Required. </div>
										<?php } ?>
										
										<?php if(extension_loaded("json")){?>
										<div class="green_tr">PHP Json &nbsp;:&nbsp; <span style=" color:#006600; padding:50px;" > Ok </span> </div>
										<?php }else{?>
										<div class="red_tr">PHP Json &nbsp;:&nbsp; Required. </div>
										<?php } ?>
										<?php if(extension_loaded("mcrypt")){?>
										<div class="green_tr">PHP Mcrypt &nbsp;:&nbsp; <span style=" color:#006600; padding:40px;" > Ok </span> </div>
										<?php }else{?>
										<div class="red_tr">PHP Mcrypt &nbsp;:&nbsp; Required. </div>
										<?php } ?>
										
										<?php if(extension_loaded("openssl")){?>
										<div class="green_tr">PHP Openssl &nbsp;:&nbsp; <span style=" color:#006600; padding:40px;" > Ok </span> </div>
										<?php }else{?>
										<div class="red_tr">PHP Openssl &nbsp;:&nbsp; Required. </div>
										<?php } ?>
										<?php if(extension_loaded("iconv")){?>
										<div class="green_tr">PHP Iconv &nbsp;:&nbsp;<span style=" color:#006600; padding:55px;" > Ok </span></div>
										<?php }else{?>
										<div class="red_tr">PHP Iconv &nbsp;:&nbsp; Required. </div>
										<?php } ?>
										
										<?php if(phpversion()>5){?>
										<div class="green_tr">PHP Version <?php echo phpversion();?> &nbsp;:&nbsp; <span style=" color:#006600"> Ok </span> </div>
										<?php }else{?>
										<div class="green_tr">PHP Version <?php echo phpversion();?> &nbsp;:&nbsp; Must be greater than PHP 5.0 </div>
										<?php } ?>
										
										 
								
									<div id="information">Environment details</div>
									<div class="green_tr">Memory Limit: <?php echo "(" . ini_get("memory_limit") .")";?> (Recommend at least 128MB)</div>	  
									<div>&nbsp;</div>
						
			
			</div>
		</div>
	</div>
	<div id="footer">
		<p>copyright 2007-2015 &copy; webgility Inc. all rights reserved.</p>
	</div>

			</body>
			</html>
		<?php
		}
		else{ 
				require_once('../../../wp-load.php');
				global $woocommerce ;
				$version = $woocommerce->version;
				if($version>'2.2.0')
				{
					require_once('lib/D.woocommerce.php');
				}
				else
				{
					require_once('lib/D.woocommerce1.php');
				} 

		}
?>