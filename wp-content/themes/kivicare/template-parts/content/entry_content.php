<?php
/**
 * Template part for displaying a post's content
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;

?>

	<?php
	if (is_single()) {
		the_content();
	} else {
		the_excerpt();
	}

	if (is_single()) { 
		$post_slug = ''; ?>
		<?php
			$post_tag = get_the_tags();
			if ($post_tag) { ?>
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
	</li> <?php 
					foreach ($post_tag as $cat) { ?>
						<li><a href="<?php get_category_link($cat->cat_ID) ?>"><?php echo esc_html($cat->name); ?></a></li> <?php 
					} ?>
			</ul> 
		<?php } 
		

	} ?>

</div><!-- .entry-content -->
