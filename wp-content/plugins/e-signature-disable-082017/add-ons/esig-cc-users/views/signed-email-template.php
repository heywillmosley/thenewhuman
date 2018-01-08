
<div marginwidth="0" marginheight="0">
    <table  border="0" cellpadding="0"
            cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td height="20"><br>
                </td>
            </tr>

            <tr>
                <td align="center">
                    <table bgcolor="e4e8eb" border="0" cellpadding="0"
                           cellspacing="0" width="600" align="center">
                        <tbody>
                            <tr>
                                <td height="30"><br>
                                </td>
                            </tr>

                            <tr>
                                <td align="center">
                                    <table bgcolor="cdd0d3" border="0"
                                           cellpadding="0" cellspacing="0" width="580"
                                           align="center">
                                        <tbody>
                                            <tr>
                                                <td align="center">
                                                    <table bgcolor="ffffff" border="0"
                                                           cellpadding="0" cellspacing="0"
                                                           width="578" align="center">
                                                        <tbody>
                                                            <tr>
                                                                <td align="center">
                                                                    <table border="0"
                                                                           cellpadding="0"
                                                                           cellspacing="0" width="540"
                                                                           align="center">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <table border="0"
                                                                                           cellpadding="0"
                                                                                           cellspacing="0"
                                                                                           width="540"
                                                                                           align="left">
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td height="10"><br>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td
                                                                                                    style="font-size:14px;font-family:Helvetica,Arial,sans-serif;line-height:24px"
                                                                                                    align="left">
                                                                                                    <p><?php echo sprintf(__("<b> %s - which was requested by %s has just been signed by %s. <br><br> "
                                                                                                            . "You were CC'd on this document.  Please find the attached document below", "esig"),$data->doc->document_title,$data->owner_email,$data->signers->signer_name); ?><br>
                                                                                                    
                                                                                                            
                                                                                                            
                                                                                                         <!--[if mso]>
  <v:rect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= $data->signed_link ;?>" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="10%" stroke="f" fillcolor="<?php echo apply_filters('esig-invite-button-background-color', '#0083c5'); ?>">
    <w:anchorlock/>
    <center style="color:#ffffff;font-family:sans-serif;font-size:14px;">
      <?php _e('View Signed Document','esig'); ?>
    </center>
  </v:rect>
  <![endif]-->
  <![if !mso]>
  <table cellspacing="0" cellpadding="0"> <tr> 
  <td align="center" width="200" height="50" bgcolor="<?php echo apply_filters('esig-invite-button-background-color', '#0083c5'); ?>" style="color: #ffffff; display: block;">
    <a href="<?= $data->signed_link ;?>" style="font-size:14px;font-family:sans-serif; text-decoration: none; line-height:50px; width:100%; display:inline-block">
    <span style="color: #ffffff;">
      <?php _e('View Signed Document','esig'); ?>
    </span>
    </a>
  </td> 
  </tr> </table> 
  <![endif]>
                    
                 <br><br>                                                                                   <hr><br><br> 

<?php _e('Thanks!','esig'); ?><br>                                                                                      <?= $data->owner_name; ?><br>
                                                                                                    <?= $data->organization_name; ?></p>
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td height="15"><br>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>

                            </tr>
                            <tr>
                                <td align="center">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td height="40"><br>
                </td>
            </tr>
        </tbody>
    </table>
</div>