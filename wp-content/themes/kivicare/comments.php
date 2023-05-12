<?php

/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;

$post_section = kivicare()->post_style();
/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if (post_password_required()) {
	return;
}

kivicare()->print_styles('kivicare-comments');

?>
<div id="comments" class="comments-area">
	<?php
	// You can start editing here -- including this comment!
	if (have_comments()) {
	?>
		<h3 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			echo esc_html($comments_number);
			if ($comments_number == 1) {
				_e(' Comment', 'kivicare');
			} else {
				_e(' Comments', 'kivicare');
			}
			?>
		</h3>
		<?php the_comments_navigation(); ?>

		<?php kivicare()->the_comments(); ?>

		<?php
		if (!comments_open()) {
		?>
			<p class="no-comments"><?php esc_html_e('Comments are closed.', 'kivicare'); ?></p>
	<?php
		}
	}
	$comment_btn = kivicare()->kivicare_get_comment_btn();
	$args = array(
		'label_submit' => esc_html__('Post Comment', 'kivicare'),
		'comment_notes_before' => esc_html__('Your email address will not be published. Required fields are marked *', 'kivicare') . '',
		'comment_field' => '<div class="comment-form-comment"><textarea id="comment" name="comment" aria-required="true" placeholder="Comment"></textarea></div>',
		'fields' => array(
			'author' => '<div class="row"><div class="col-lg-12"><div class="comment-form-author"><input id="author" name="author" aria-required="true" placeholder="Name*" /></div></div>',
			'email' => '<div class="col-lg-12"><div class="comment-form-email"><input id="email" name="email" placeholder="Email*" /></div></div>',
			'url' => '<div class="col-lg-12"><div class="comment-form-url"><input id="url" name="url" placeholder="Website" /></div></div></div>',
		),
		'submit_button'	=> $comment_btn,
	);
	comment_form($args);
	?>
</div><!-- #comments -->