<?php

/**
 * Template part for displaying a post's taxonomy terms
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;

$taxonomies = wp_list_filter(
	get_object_taxonomies($post, 'objects'),
	array(
		'public' => true,
	)
);

?>
<ul class="kivicare-blogtag list-inline">
	<li class="kivicare-comment-count">
		<?php
		$comments_number = get_comments_number();
		echo esc_html($comments_number);
		if ($comments_number == 1) {
			_e(' Comment', 'kivicare');
		} else {
			_e(' Comments', 'kivicare');
		}
		?>
	</li>
	<?php
	$post_tag = get_the_tags();
	if ($post_tag) { ?>

		<?php foreach ($post_tag as $cat) { ?>
			<li><a href="<?php get_category_link($cat->cat_ID) ?>"><?php echo esc_html($cat->name); ?></a></li>
		<?php } ?>
	<?php }
	?>
</ul>