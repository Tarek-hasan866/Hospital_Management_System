<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;

use Elementor;

?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <?php $kivicare_option = get_option('kivicare-options'); ?>
  <link rel="profile" href="<?php echo is_ssl() ? 'https:' : 'http:' ?>//gmpg.org/xfn/11">
  <?php
  if (!function_exists('has_site_icon') || !wp_site_icon()) {
  ?>
    <link rel="shortcut icon" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/redux/favicon.png'); ?>" />
  <?php
  }
  ?>
  <?php wp_head(); ?>
</head>
<?php
//--- class setting for vertical menu start---// 
$default_site_content = '';

$has_sticky = '';

if (class_exists('ReduxFramework')) {
  $default_site_content = 'kivicare';
  if (isset($kivicare_option['display_sticky_header']) && $kivicare_option['display_sticky_header'] == 'yes') {
    $has_sticky = ' has-sticky';
  }
} else {
  $has_sticky = ' has-sticky';
}
// position
$header_position = '';
if (class_exists('ReduxFramework') && isset($kivicare_option['header_postion']) && $kivicare_option['header_postion'] == 'over') {
  $header_position = ' header-over';
}
?>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <?php
  if (class_exists('ReduxFramework')) {
  ?>
    <div id="has-side-bar" class="kivicare-menu-side-bar">
      <?php
      get_template_part('template-parts/header/header', 'sidearea');
      ?>
    </div>
  <?php } ?>
  <?php
  if (isset($kivicare_option['display_loader'])) {
    $options = $kivicare_option['display_loader'];

    if ($options == "yes") {
  ?>
      <!-- loading -->
      <div id="loading">
        <div id="loading-center">
          <?php
          if (!empty($kivicare_option['loader_gif']['url'])) {
            $bgurl = $kivicare_option['loader_gif']['url'];
          ?>
            <img src="<?php echo esc_url($bgurl); ?>" alt="<?php esc_attr_e('loader', 'kivicare'); ?>">
          <?php
          } else {
            $bgurl = get_template_directory_uri() . '/assets/images/redux/loader.gif';
          ?>
            <img src="<?php echo esc_url($bgurl); ?>" alt="<?php esc_attr_e('loader', 'kivicare'); ?>">
          <?php
          }
          ?>
        </div>
      </div>
      <!-- loading End -->
  <?php
    }
  }
  ?>
  <div id="page" class="site <?php echo esc_attr($default_site_content);
                              echo esc_attr($header_position); ?>">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'kivicare'); ?></a>
    <?php
    // container
    $kivicare_container = '';
    if (isset($kivicare_option['header_container']) && $kivicare_option['header_container'] == 'container') {
      $kivicare_container = 'container';
    } else {
      $kivicare_container = 'container-fluid';
    }

    ?>

    <header class="header-default<?php echo esc_attr($has_sticky); ?>" id="default-header">
      <div class="<?php echo esc_attr($kivicare_container); ?>">
        <div class="row">
          <div class="col-md-12">
            <?php
            if (class_exists('ReduxFramework')) {
              if (isset($kivicare_option['header_layout']) && $kivicare_option['header_layout'] == 'custom') {
                $header = $kivicare_option['menu_style'];
                $my_layout = get_page_by_path($header, '', 'iqonic_hf_layout');
                $header_response =  Elementor\Plugin::instance()->frontend->get_builder_content_for_display($my_layout->ID);
                echo wp_kses_post($header_response);
              } else {
                get_template_part('template-parts/header/navigation');
              }
            } else {
              get_template_part('template-parts/header/navigation');
            }
            ?>
          </div>
        </div>
      </div>
    </header><!-- #masthead -->
    <div class="kivicare-mobile-menu">
      <?php get_template_part('template-parts/header/navigation', 'mobile'); ?>
    </div>
    <?php get_template_part('template-parts/breadcrumb/breadcrumb'); ?>