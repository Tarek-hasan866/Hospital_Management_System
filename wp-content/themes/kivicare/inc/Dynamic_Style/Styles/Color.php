<?php

/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\Banner class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class Color extends Component
{

	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'kivicare_color_options'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_banner_title_color'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_layout_color'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_loader_color'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_bg_color'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_opacity_color'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_header_radio'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_footer_color'), 20);
	}

	public function kivicare_color_options()
	{

		$kivicare_option = get_option('kivicare-options');
		if (class_exists('ReduxFramework')) {
			$color_var = ':root { ';
			
			if (isset($kivicare_option['custom_color_switch']) && $kivicare_option['custom_color_switch'] == 'yes' && isset($kivicare_option['primary_color']) && !empty($kivicare_option['primary_color'])) {
				$color = $kivicare_option['primary_color'];
				$color_var .= '--color-theme-primary: ' . $color . ' !important;';
			}

			if (isset($kivicare_option['custom_color_switch']) && $kivicare_option['custom_color_switch'] == 'yes' && isset($kivicare_option['secondary_color']) && !empty($kivicare_option['secondary_color'])) {
				$color = $kivicare_option['secondary_color'];
				$color_var .= '--color-theme-secondary: ' . $color . ' !important;';
			}
			
			if (isset($kivicare_option['custom_color_switch']) && $kivicare_option['custom_color_switch'] == 'yes' && isset($kivicare_option['text_color']) && !empty($kivicare_option['text_color'])) {
				$color = $kivicare_option['text_color'];
				$color_var .= '--global-font-color: ' . $color . ' !important;';
			}

			if (isset($kivicare_option['custom_color_switch']) && $kivicare_option['custom_color_switch'] == 'yes' && isset($kivicare_option['title_color']) && !empty($kivicare_option['title_color'])) {
				$color = $kivicare_option['title_color'];
				$color_var .= ' --global-font-title: ' . $color . ' !important;';
			}

			$color_var .= '}';
			wp_add_inline_style('kivicare-global', $color_var);
		}
	}

	public function kivicare_banner_title_color()
	{
		//Set Body Color
		$kivicare_option = get_option('kivicare-options');

		if (!empty($kivicare_option['bg_title_color'])) {
			$bg_title_color = $kivicare_option['bg_title_color'];
		}

		$bn_title_color = "";

		if (!empty($bg_title_color)) {
			$bn_title_color .= "
        .kivicare-breadcrumb-one .title{
            color: $bg_title_color !important;
        }";
		}
		wp_add_inline_style('kivicare-global', $bn_title_color);
	}

	public function kivicare_layout_color()
	{
		//Set Body Color
		$kivicare_option = get_option('kivicare-options');

		if (!empty($kivicare_option['kivicare_layout_color'])) {
			$kivicare_layout_color = $kivicare_option['kivicare_layout_color'];
		}
		$body_accent_color = "";

		if (isset($body_back_color) && !empty($body_back_color)) {
			$body_accent_color .= "
        body {
            background-color: $body_back_color !important;
        }";
		} else if (!empty($kivicare_option['layout_set']) && $kivicare_option['layout_set'] == "1" && $key_body_bg_col['body_variation'] != 'default') {
			if (!empty($kivicare_layout_color) && $kivicare_layout_color != '#ffffff') {
				$body_accent_color .= "
            body {
                background-color: $kivicare_layout_color !important;
            }";
			}
		} else {
			$body_accent_color = "";
		}

		wp_add_inline_style('kivicare-style', $body_accent_color);
	}

	public function kivicare_loader_color()
	{
		//Set Loader Background Color
		$kivicare_option = get_option('kivicare-options');

		if (!empty($kivicare_option['loader_color'])) {
			$loader_color = $kivicare_option['loader_color'];
		}
		$ld_color = "";

		if (!empty($loader_color) && $loader_color != '#ffffff') {
			$ld_color .= "#loading {
								background : $loader_color !important;
							}";
		}
		wp_add_inline_style('kivicare-style', $ld_color);
	}

	public function kivicare_bg_color()
	{
		//Set Background Color
		$kivicare_option = get_option('kivicare-options');

		if (!empty($kivicare_option['bg_color'])) {
			$bg_color = $kivicare_option['bg_color'];
		}
		$background_color = "";

		if (!empty($kivicare_option['bg_type']) && $kivicare_option['bg_type'] == "1") {
			if (!empty($bg_color) && $bg_color != '#ffffff') {
				$background_color .= "
            .kivicare-bg-over {
                background : $bg_color !important;
            }";
			}
		}
		wp_add_inline_style('kivicare-style', $background_color);
	}

	public function kivicare_opacity_color()
	{
		//Set Background Opacity Color
		$kivicare_option = get_option('kivicare-options');

		if (!empty($kivicare_option['bg_opacity']) && $kivicare_option['bg_opacity'] == "3") {
			$bg_opacity = $kivicare_option['opacity_color']['rgba'];
		}
		$op_color = "";

		if (!empty($kivicare_option['bg_opacity']) && $kivicare_option['bg_opacity'] == "3") {
			if (!empty($bg_opacity) && $bg_opacity != '#ffffff') {
				$op_color .= "
        .breadcrumb-video::before,.breadcrumb-bg::before, .breadcrumb-ui::before {
            background : $bg_opacity !important;
        }";
			}
		}
		wp_add_inline_style('kivicare-style', $op_color);
	}

	public function kivicare_header_radio()
	{
		//Set Text Logo Color
		$kivicare_option = get_option('kivicare-options');

		if (!empty($kivicare_option['header_color'])) {
			$logo = $kivicare_option['header_color'];
		}
		$logo_color = "";

		if (!empty($kivicare_option['header_radio']) && $kivicare_option['header_radio'] == "1") {
			if (!empty($logo) && $logo != '#ffffff') {
				$logo_color .= "
            .logo-text {
                color : $logo !important;
            }";
			}
		}
		wp_add_inline_style('kivicare-style', $logo_color);
	}

	public function kivicare_footer_color()
	{
		//Set Footer Background Color
		$kivicare_option = get_option('kivicare-options');

		if (!empty($kivicare_option['change_footer_color']) && $kivicare_option['change_footer_color'] == "0") {
			$f_color = $kivicare_option['footer_color'];
		}
		$footer_color = "";
		if (!empty($kivicare_option['change_footer_color']) && $kivicare_option['change_footer_color'] == "0") {
			if (!empty($f_color) && $f_color != '#ffffff') {
				$footer_color .= "
            .kivicare-over-dark-90 {
                background : $f_color !important;
            }";
			}
		} else {
			$footer_color = '';
		}

		wp_add_inline_style('kivicare-style', $footer_color);
	}
}
