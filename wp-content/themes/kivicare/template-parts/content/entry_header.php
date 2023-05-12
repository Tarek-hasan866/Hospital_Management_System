<?php
/**
 * Template part for displaying a post's header
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;
$kivicare_options = get_option('kivicare-options');
?>


	<?php
	if ( ! is_search() ) {
		if(isset($kivicare_options['display_featured_image']))
		{
		$options = $kivicare_options['display_featured_image'];
		if($options == "yes"){
				get_template_part( 'template-parts/content/entry_thumbnail', get_post_type() );
			} 
		}
		else{
			get_template_part( 'template-parts/content/entry_thumbnail', get_post_type() );
		}
	} ?>
	<div class="kivicare-blog-detail">
	<?php 
	get_template_part('template-parts/content/entry_meta', get_post_type());
	if( ! is_single() ) {
	get_template_part( 'template-parts/content/entry_title', get_post_type() );
	}
	?>
<!-- .entry-header -->
