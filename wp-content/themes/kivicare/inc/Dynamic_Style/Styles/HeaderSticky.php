<?php

/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\HeaderSticky class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class HeaderSticky extends Component
{
	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'kivicare_header_sticky_background_style'));
		add_action('wp_enqueue_scripts', array($this, 'kivicare_sticky_sub_menu_color_options'), 20);
		add_action('wp_enqueue_scripts', array($this, 'kivicare_sticky_menu_color_options'), 20);
	}

	public function kivicare_header_sticky_background_style()
	{
		$kivicare_option = get_option('kivicare-options');
		$inline_css = '';


		if (isset($kivicare_option['display_sticky_header'])) {
			if (isset($kivicare_option['sticky_header_bg']) && $kivicare_option['sticky_header_bg'] != 'default') {
				$type = $kivicare_option['sticky_header_bg'];

				if ($type == 'color') {
					if (!empty($kivicare_option['sticky_header_bg_color'])) {
						$inline_css .= 'header#default-header.menu-sticky{
							background : ' . $kivicare_option['sticky_header_bg_color'] . '!important;
						}';
					}
				}
				if ($type == 'image') {
					if (!empty($kivicare_option['sticky_header_bg_img']['url'])) {
						$inline_css .= 'header#default-header.menu-sticky{
							background : url(' . $kivicare_option['sticky_header_bg_img']['url'] . ') !important;
						}';
					}
				}
				if ($type == 'transparent') {
					$inline_css .= 'header#default-header.menu-sticky{
						background : transparent !important;
					}';
				}
			}
		}

		wp_add_inline_style('kivicare-global', $inline_css);
	}



	public function kivicare_sticky_menu_color_options()
	{
		$kivicare_option = get_option('kivicare-options');
		$inline_css = '';
		if (isset($kivicare_option['sticky_menu_color_type']) && $kivicare_option['sticky_menu_color_type'] == 'custom') {
			if (isset($kivicare_option['sticky_menu_color']) && !empty($kivicare_option['sticky_menu_color'])) {
				$inline_css .= 'header.header-down .sf-menu > li > a, header.header-up .sf-menu > li > a{
						color : ' . $kivicare_option['sticky_menu_color'] . '!important;
					}';
			}

			if (isset($kivicare_option['sticky_menu_hover_color']) && !empty($kivicare_option['sticky_menu_hover_color'])) {
				$inline_css .= 'header.header-down .sf-menu li:hover > a,header.header-down .sf-menu li.current-menu-ancestor > a,header.header-down .sf-menu  li.current-menu-item > a, header.header-up .sf-menu li:hover > a,header.header-up .sf-menu li.current-menu-ancestor > a,header.header-up .sf-menu  li.current-menu-item > a{
						color : ' . $kivicare_option['sticky_menu_hover_color'] . '!important;
					}';
			}
		}
		wp_add_inline_style('kivicare-global', $inline_css);
	}

	public function kivicare_sticky_sub_menu_color_options()
	{
		$kivicare_option = get_option('kivicare-options');
		$inline_css = '';

		if (isset($kivicare_option['sticky_header_submenu_color_type']) && $kivicare_option['sticky_header_submenu_color_type'] == 'custom') {
			if (isset($kivicare_option['sticky_kivicare_header_submenu_color']) && !empty($kivicare_option['sticky_kivicare_header_submenu_color'])) {
				$inline_css .= 'header.header-down .sf-menu ul.sub-menu a, header.header-up .sf-menu ul.sub-menu a{
                color : ' . $kivicare_option['sticky_kivicare_header_submenu_color'] . ' !important;
            }';
			}

			if (isset($kivicare_option['sticky_kivicare_header_submenu_hover_color']) && !empty($kivicare_option['sticky_kivicare_header_submenu_hover_color'])) {
				$inline_css .= 'header.header-down .sf-menu li.sfHover>a,header.header-down .sf-menu li:hover>a,header.header-down .sf-menu li.current-menu-ancestor>a,header.header-down .sf-menu li.current-menu-item>a,header.header-down .sf-menu ul>li.menu-item.current-menu-parent>a,header.header-down .sf-menu ul li.current-menu-parent>a,header.header-down .sf-menu ul li .sub-menu li.current-menu-item>a,
				header.header-up .sf-menu li.sfHover>a,header.header-up .sf-menu li:hover>a,header.header-up .sf-menu li.current-menu-ancestor>a,header.header-up .sf-menu li.current-menu-item>a,header.header-up .sf-menu ul>li.menu-item.current-menu-parent>a,header.header-up .sf-menu ul li.current-menu-parent>a,header.header-up .sf-menu ul li .sub-menu li.current-menu-item>a{
                color : ' . $kivicare_option['sticky_kivicare_header_submenu_hover_color'] . ' !important;
            }';
			}

			if (isset($kivicare_option['sticky_kivicare_header_submenu_background_color']) && !empty($kivicare_option['sticky_kivicare_header_submenu_background_color'])) {
				$inline_css .= 'header.header-up .sf-menu ul.sub-menu li, header.header-down .sf-menu ul.sub-menu li {
                background : ' . $kivicare_option['sticky_kivicare_header_submenu_background_color'] . ' !important;
            }';
			}

			if (isset($kivicare_option['sticky_header_submenu_background_hover_color']) && !empty($kivicare_option['sticky_header_submenu_background_hover_color'])) {
				$inline_css .= 'header.header-up .sf-menu ul.sub-menu li:hover,header.header-up .sf-menu ul.sub-menu li.current-menu-item ,header.header-up .sf-menu ul.sub-menu li:hover,header.header-up .sf-menu ul.sub-menu li.current-menu-item,
				header.header-down .sf-menu ul.sub-menu li:hover,header.header-down .sf-menu ul.sub-menu li.current-menu-item ,header.header-down .sf-menu ul.sub-menu li:hover,header.header-down .sf-menu ul.sub-menu li.current-menu-item{
                background : ' . $kivicare_option['sticky_header_submenu_background_hover_color'] . ' !important;
            }';
			}

		}
		wp_add_inline_style('kivicare-global', $inline_css);
	}
}
