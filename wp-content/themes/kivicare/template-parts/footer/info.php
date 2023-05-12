<?php

/**
 * Template part for displaying the footer info
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;

if (class_exists('ReduxFramework')) {

	$kivicare_options = get_option('kivicare-options');
?>
	<div class="copyright-footer">
		<div class="container">
			<div class="row">
				<?php if (isset($kivicare_options['display_copyright']) && $kivicare_options['display_copyright'] == 'yes') {  ?>
					<div class="col-sm-12 m-0 text-<?php echo esc_attr($kivicare_options['footer_copyright_align']); ?>">
						<div class="pt-3 pb-3 text-center">
							<?php
							if (isset($kivicare_options['footer_copyright'])) {  ?>
								<span class="copyright"><?php echo html_entity_decode($kivicare_options['footer_copyright']); ?></span>
							<?php
							} else {	?>
								<span class="copyright"><a target="_blank" href="<?php echo esc_url(__('https://iqonic.design/', 'kivicare')); ?>"> <?php printf(esc_html__('© 2021', 'kivicare'), 'kivicare'); ?><strong><?php printf(esc_html__(' kivicare ', 'kivicare'), 'kivicare'); ?></strong><?php printf(esc_html__('. All Rights Reserved.', 'kivicare'), 'kivicare'); ?></a></span>
							<?php
							} ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div><!-- .site-info -->

<?php } else { ?>

	<div class="copyright-footer">
		<div class="container">
			<div class="row">
				<div class="col-sm-12">
					<div class="pt-3 pb-3 text-center">
						<span class="copyright"><a target="_blank" href="<?php echo esc_url(__('https://iqonic.design/', 'kivicare')); ?>"> <?php printf(esc_html__('© 2021', 'kivicare'), 'kivicare'); ?><strong><?php printf(esc_html__(' kivicare ', 'kivicare'), 'kivicare'); ?></strong><?php printf(esc_html__('. All Rights Reserved.', 'kivicare'), 'kivicare'); ?></a></span>
					</div>
				</div>
			</div>
		</div>
	</div><!-- .site-info -->
<?php }
