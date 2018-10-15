<?php if ( 'columns' == $settings->layout ) : ?>
<div class="fl-post-column">
<?php endif; ?>

<div <?php $module->render_post_class(); ?> itemscope itemtype="<?php FLPostGridModule::schema_itemtype(); ?>">
	<?php

	FLPostGridModule::schema_meta();
	do_action( 'fl_builder_post_grid_before_content', $settings, $module );
	echo do_shortcode( FLThemeBuilderFieldConnections::parse_shortcodes( $settings->custom_post_layout->html ) );
	do_action( 'fl_builder_post_grid_after_content', $settings, $module );
	?>
</div>

<?php if ( 'columns' == $settings->layout ) : ?>
</div>
<?php endif; ?>
