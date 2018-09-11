<div class="wrap">
    <h1 class="wp-heading-inline">Ticket Themes</h1>
</div>
<?php add_thickbox(); ?> 

<div id="fooevents-theme-viewer-upload-wrapper">
        <p class="install-help"></p>
    <div class="fooevents-theme-viewer-upload-inner">
        <form name="fooevents-theme-viewer-upload-form" action="edit.php?post_type=event_magic_tickets&page=fooevents-ticket-themes" method="POST" enctype="multipart/form-data">
            <div>
            <h3>Upload your Ticket Theme ZIP file:</h3>
            <input type="file" name="fooevents-theme-viewer-upload-file" /><br /> 
            <input type="submit" value="Upload Ticket Theme" class="button button-primary" />
            </div>
        </form>
    </div> 
</div> 

<div id="fooevents-theme-viewer-wrapper">
<?php foreach($themes as $theme_name => $theme) :?>
    <div class="fooevents-theme-viewer-theme">
    <div class="fooevents-theme-viewer-theme-inner">
        <div id="fooevents-theme-viewer-preview-options-<?php echo $theme['file_name']; ?>" style="display:none;" class="fooevents-theme-viewer-preview-popup" >
            <div class="fooevents-theme-viewer-preview-controls">
                <form name="fooevents-theme-viewer-preview-form-<?php echo $theme['file_name']; ?>" action="edit.php?post_type=event_magic_tickets&page=fooevents-ticket-themes" method="POST">
                <h3>Send preview to:</h3> 
                <input type="text" value="<?php echo $user_email; ?>" name="fooevents-theme-viewer-preview-input" class="regular-text" />
                <input type="hidden" value="<?php echo $theme['path']; ?>" name="fooevents-theme-viewer-preview-path" />
                <input type="hidden" value="<?php echo $theme_name; ?>" name="fooevents-theme-viewer-preview-theme-name" />
                <input type="submit" value="Send Ticket Preview" name="fooevents-theme-viewer-preview-submit" class="button button-primary" />
                </form>
            </div>
            <img src="<?php echo $theme['preview']; ?>" class="fooevents-theme-viewer-image-preview" />
       </div>
        <div class="fooevents-theme-viewer-preview-wrapper">
            <a href="#TB_inline?width=800&height=550&inlineId=fooevents-theme-viewer-preview-options-<?php echo $theme['file_name']; ?>" class="thickbox">
            <img src="<?php echo $theme['preview']; ?>" class="fooevents-theme-viewer-image">
            </a>
        </div>
        <div class="fooevents-theme-viewer-name">
            <h3><a href="#TB_inline?width=800&height=550&inlineId=fooevents-theme-viewer-preview-options-<?php echo $theme['file_name']; ?>" class="thickbox"><?php echo $theme_name; ?></a></h3>
        </div>
    </div>
    </div>
<?php endforeach; ?>
</div>