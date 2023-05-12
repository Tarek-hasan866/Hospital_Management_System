<?php

/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\Header class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;


class Header extends Component
{

	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'kivicare_header_background_style'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_menu_color_options'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_sub_menu_color_options'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_burger_menu_color_options'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_action_btn_color_options'), 20);
	}

	public function kivicare_header_background_style()
	{
		$kivicare_option = get_option('kivicare-options');
		$dynamic_css = '';

		if (isset($kivicare_option['kivicare_header_background_type'])) {
			if (isset($kivicare_option['kivicare_header_background_type']) && $kivicare_option['kivicare_header_background_type'] != 'default') {
				$type = $kivicare_option['kivicare_header_background_type'];
				if ($type == 'color') {
					if (!empty($kivicare_option['kivicare_header_background_color'])) {
						$dynamic_css = 'header#default-header{
							background : ' . $kivicare_option['kivicare_header_background_color'] . '!important;
						}';
					}
				}
				if ($type == 'image') {
					if (!empty($kivicare_option['kivicare_header_background_image']['url'])) {
						$dynamic_css = 'header#default-header{
							background : url(' . $kivicare_option['kivicare_header_background_image']['url'] . ') !important;
						}';
					}
				}
				if ($type == 'transparent') {
					$dynamic_css = 'header#default-header{
						background : transparent !important;
					}';
				}
			}
		}
		wp_add_inline_style('kivicare-global', $dynamic_css);
	}

	public function kivicare_menu_color_options()
	{

		$kivicare_option =  get_option('kivicare-options');
		$inline_css = '';

		if (!empty($kivicare_option['menu_color']) && $kivicare_option['menu_color'] == "custom") {

			if (isset($kivicare_option['header_menu_color']) && !empty($kivicare_option['header_menu_color'])) {
				$inline_css .= '.sf-menu > li > a{
						color : ' . $kivicare_option['header_menu_color'] . '!important;
					}';
			}

			if (isset($kivicare_option['hover_menu_color']) && !empty($kivicare_option['hover_menu_color'])) {
				$inline_css .= '.sf-menu li:hover > a,.sf-menu li.current-menu-ancestor > a,.sf-menu  li.current-menu-item > a{
						color : ' . $kivicare_option['hover_menu_color'] . ' !important;
					}';
			}
		}



		wp_add_inline_style('kivicare-global', $inline_css);
	}

	public function kivicare_sub_menu_color_options()
	{
		$kivicare_option = get_option('kivicare-options');
		$inline_css = '';
		if (isset($kivicare_option['header_submenu_color_type']) && $kivicare_option['header_submenu_color_type'] == 'custom') {
			if (isset($kivicare_option['submenu_color']) && !empty($kivicare_option['submenu_color'])) {
				$inline_css .= '.sf-menu ul.sub-menu a{
                   		color : ' . $kivicare_option['submenu_color'] . ' !important; }';
			}

			if (isset($kivicare_option['hover_submenu_color']) && !empty($kivicare_option['hover_submenu_color'])) {
				$inline_css .= '.sf-menu li.sfHover>a, .sf-menu li:hover>a,.sf-menu li.current-menu-ancestor>a, .sf-menu li.current-menu-item>a, .sf-menu ul>li.menu-item.current-menu-parent>a,.sf-menu ul li.current-menu-parent>a, .sf-menu ul li .sub-menu li.current-menu-item>a
                					{  color : ' . $kivicare_option['hover_submenu_color'] . ' !important;  }';
			}

			if (isset($kivicare_option['submenu_background_color']) && !empty($kivicare_option['submenu_background_color'])) {
				$inline_css .= '.sf-menu ul.sub-menu li{
                   background : ' . $kivicare_option['submenu_background_color'] . ' !important;  }';
			}

			if (isset($kivicare_option['hover_submenu_bg_color']) && !empty($kivicare_option['hover_submenu_bg_color'])) {
				$inline_css .= '.sf-menu ul.sub-menu li:hover,.sf-menu ul.sub-menu li.current-menu-item{
                   background : ' . $kivicare_option['hover_submenu_bg_color'] . ' !important;   }';
			}
		}
		wp_add_inline_style('kivicare-global', $inline_css);
	}

	public function kivicare_burger_menu_color_options()
	{
		$kivicare_option = get_option('kivicare-options');
		$inline_css = '';

		if (isset($kivicare_option['burger_menu_button_type']) && $kivicare_option['burger_menu_button_type'] == 'custom') {

			if (isset($kivicare_option['burger_menu_icon']) && !empty($kivicare_option['burger_menu_icon'])) {
				$inline_css .= ' .menu-btn .line {
                    background-color : ' . $kivicare_option['burger_menu_icon'] . ' !important;
                }';
			}

			if (isset($kivicare_option['burger_menu_popup_bg']) && !empty($kivicare_option['burger_menu_popup_bg'])) {
				$inline_css .= ' .kivicare-mobile-menu {
                    background : ' . $kivicare_option['burger_menu_popup_bg'] . ' !important;
                }';
			}
			

			if (isset($kivicare_option['burger_menu_color']) && !empty($kivicare_option['burger_menu_color'])) {
				$inline_css .= '.kivicare-mobile-menu .navbar-nav > li > a, .kivicare-mobile-menu .navbar-nav li > .toggledrop svg{ 
					color : ' . $kivicare_option['burger_menu_color'] . ' !important;
				}';
			}


			if (isset($kivicare_option['burger_hover_menu_color']) && !empty($kivicare_option['burger_hover_menu_color'])) {
				$inline_css .= '.kivicare-mobile-menu .navbar-nav li.current-menu-item > .toggledrop svg, .kivicare-mobile-menu .navbar-nav li.current-menu-item > a, .kivicare-mobile-menu .navbar-nav li .sub-menu li:hover > a, .kivicare-mobile-menu .navbar-nav li:hover > .toggledrop svg, .kivicare-mobile-menu .navbar-nav li:hover > a, .kivicare-mobile-menu ul > li.current-menu-ancestor > .toggledrop svg, .kivicare-mobile-menu ul > li.current-menu-ancestor > a, .kivicare-mobile-menu ul li .sub-menu li.current-menu-item > a, .kivicare-mobile-menu ul li .sub-menu li.menu-item.current-menu-ancestor > a{
			        color : ' . $kivicare_option['burger_hover_menu_color'] . ' !important;
				}';
			}

			if (isset($kivicare_option['burger_submenu_color']) && !empty($kivicare_option['burger_submenu_color'])) {
				$inline_css .= '.kivicare-mobile-menu .navbar-nav li .sub-menu li a , .kivicare-mobile-menu .navbar-nav li .sub-menu li svg{
			        color : ' . $kivicare_option['burger_submenu_color'] . ' !important;
				}';
			}
		}
		wp_add_inline_style('kivicare-global', $inline_css);
	}

	public function kivicare_action_btn_color_options()
	{
		$kivicare_option = get_option('kivicare-options');
		$inline_css = '';

		if (isset($kivicare_option['button_color']) && $kivicare_option['button_color'] == 'custom') {

			if (isset($kivicare_option['button_bg_color']) && !empty($kivicare_option['button_bg_color'])) {
				$inline_css .= '
            header .kivicare-shop-btn-holder  #btn-search svg,header .search_count #btn-search{
                color : ' . $kivicare_option['button_bg_color'] . ' !important;
            }';
			}
		}

		if (!empty($inline_css)) {
			wp_add_inline_style('kivicare-global', $inline_css);
		}
	}
}
