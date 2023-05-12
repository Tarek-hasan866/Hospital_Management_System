<?php
/**
 * The template for displaying all pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;
$unique_id = esc_html( uniqid( 'search-form-' ) ); ?>
<form method="get" class="search-form search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="form-search">
		<input type="search" id="<?php echo esc_attr($unique_id); ?>" class="search-field search__input" name="s" placeholder="Search website" />
		<button type="submit" class="search-submit" ><i class="fa fa-search" aria-hidden="true"></i><span class="screen-reader-text"><?php echo esc_html_x( 'Search', 'submit button', 'kivicare' ); ?></span></button> 
	</div>
</form>