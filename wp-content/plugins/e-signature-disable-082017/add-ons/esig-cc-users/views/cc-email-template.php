


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
                                                                                                    <p><?php _e('Hi','esig');?>  <?= $data->user_info->first_name ; ?>,
  <p>                                                                                                   
<?php                                                                                                      
          echo sprintf(__('Just heads up that %s copied you on %s; which is currently sent out for signature.','esig'),$data->owner_email,$data->doc->document_title) ; ?></p>
            
  <p>    <?php  _e('The Document is being sent to the following singers:','esig'); ?></p>
          
            <?php 
            foreach($data->signers as $signers){
                 echo $signers->signer_name . " (" . $signers->signer_email . ")<br>" ; 
            } 
          ?>
            
        <p>    <?php _e("There's nothing you need to do.  We will email you the final version once everyone has signed.","esig") ; ?></p>

                                                                                                    <hr> 

                                                                                                    <?= $data->owner_name ?><br>
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

