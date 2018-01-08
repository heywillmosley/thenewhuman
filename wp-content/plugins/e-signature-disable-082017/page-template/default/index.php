<!DOCTYPE html>

<html lang="en">

    <head>

        <meta charset="utf-8">

        <meta name="robots" content="noindex,nofollow">

        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="description" content="">

        <meta name="author" content="">

<!--<link rel='stylesheet' id='esig-overall-style-css'  href='<?php echo plugin_dir_url(__FILE__); ?>/style.css' type='text/css' media='all' />-->

        <link rel='stylesheet' id='dashicons-css'  href='<?php echo site_url(); ?>/wp-includes/css/dashicons.min.css?ver=3.9.1' type='text/css' media='all' />


        <title><?php _e('WP E-Signature by Approve Me - Sign Documents Using WordPress - ', 'esig'); ?></title>
        <!--noptimize-->
        <?php
       WP_E_Shortcode::esig_head();
        ?>
        <!--/noptimize-->


    </head>



    <body <?php body_class(array('esig-template-page ')); ?> oncontextmenu="return false;">

        <div id="page_loader" style="display:none;">

            <div id="d1"></div>

            <div id="d2"></div>

            <div id="d3"></div>

            <div id="d4"></div>

            <div id="d5"></div>

        </div>

        <div class="signer-header" role="navigation" >

            <div class="container" >

                <div class="navbar-header">

                    <a href="<?php echo bloginfo('url'); ?>" target="_blank" class="navbar-brand" style="color:#fff;"> 

<?php
echo stripslashes(WP_E_Sig()->setting->get_company_name());
//echo stripslashes($api->setting->get_generic("company_logo"));
?> </a>

                </div>



                <div class="nav navbar-nav navbar-right doclogo-right">

                    <span class="hint--bottom  hint--rounded hint--bounce" data-hint="Click here to learn more about the security and protection of the document you are signing.">

                        <a class="disabled" href="https://www.approveme.me/security-ueta-e-sign-protection/" target="_blank"><img src="<?php echo(ESIGN_ASSETS_DIR_URI) ?>/images/verified-approveme.svg" alt="" width="140px"></a>

                    </span>

                </div>

            </div>

        </div>

        <div class="container first-page doc_page">

<?php
// Start the Loop.

while (have_posts()) : the_post();



    //the_content();

echo do_shortcode(wpautop($post->post_content)); 

endwhile;

?>	



        </div>




                            
                            <!-- -->
        <!--

        <div class="container doc_page">

        

        </div>

        -->
<?php
$display_class = (wp_is_mobile()) ? 'style="display:none;"' : '';
?>

        <div id="esig-footer" <?php echo $display_class; ?> class="container footer-agree">

            <div class="esig-container">


                <div class="navbar-header agree-container">

                    <span id="esig-iam"> </span><span class="agree-text"><?php _e('I agree to be legally bound by this agreement and eSignature', 'esig'); ?> <a href="#" data-toggle="modal" data-target=".esig-terms-modal-lg" id="esig-terms" class="doc-terms"><?php _e('Terms of Use.', 'esig'); ?></a></span>

                </div>



                <div class="nav navbar-nav navbar-right footer-btn">

<?php
$defalut_page_id = WP_E_Sig()->setting->get_generic('default_display_page');

$page_id = get_the_ID();

if ($defalut_page_id == $page_id) {

    $doc_id = isset($_GET['csum']) ? WP_E_Sig()->document->document_id_by_csum($_GET['csum']) : null;

    if (array_key_exists('document_id', $_GET)) {
        $doc_id = $_GET['document_id'];
    }

    $print_option = WP_E_Sig()->setting->get_generic('esig_print_option' . $doc_id);

    if (empty($print_option))
        $print_option = WP_E_Sig()->setting->get_generic('esig_print_option');



    if ($print_option == 4) {
        ?>



                            <a href="javascript:window.print()" class="agree-button" id="esig-print-button" title=""><?php _e('Print Document', 'esig'); ?></a>



                        <?php
                        }
                    } else {



                        $stand_table = WP_E_Sig()->document->table_prefix . 'documents_stand_alone_docs';

                        $page_id = get_the_ID();
                        global $wpdb ;
                        $doc_id = $wpdb->get_var("SELECT document_id FROM " . $stand_table . " WHERE page_id=$page_id");
                        $print_option = WP_E_Sig()->setting->get_generic('esig_print_option' . $doc_id);
                        if (empty($print_option))
                                $print_option = WP_E_Sig()->setting->get_generic('esig_print_option');



                        if ($print_option == 4) {
                            ?>
                            <a href="javascript:window.print()" class="agree-button" id="esig-print-button" title=""><?php _e('Print Document', 'esig'); ?></a>
                        <?php
                        }
                    }
                    ?>


                    <span id="esign_click_submit">
                        <a href="#" class="agree-button disabled" id="esig-agree-button"  title="Agree and submit your signature."><span id="esig-agreed"><?php _e('Agree & Sign', 'esig'); ?></span></a>
                    </span>



                </div>

            </div>

        </div>
        <!--esigature mobile footer when signed -->

                    <?php
                    if (wp_is_mobile()):
                        ?>
            <div id="esig-mobile-footer" class="footer-agree">

                <div class="navbar-header agree-container">


                    <span class="agree-text"> <a href="<?php echo home_url( '/'); ?>" data-ajax="false" class="esig-sitename"><?php _e('Back to Main Site', 'esig'); ?></a></span>

                </div>


            </div>

<?php endif; ?>

<!--noptimize-->
        <?php WP_E_Shortcode::esig_footer(); ?>

<!--/noptimize-->

    </body>

</html>