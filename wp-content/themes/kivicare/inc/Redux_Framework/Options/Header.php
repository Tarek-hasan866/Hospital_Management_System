<?php

/**
 * Kivicare\Kivicare\Redux_Framework\Options\General class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Redux_Framework\Options;

use Redux;
use Kivicare\Kivicare\Redux_Framework\Component;

class Header extends Component
{

	public function __construct()
	{
		$this->set_widget_option();
	}
	public function get_hf_layout()
	{
		$args = array(
			'post_type'         => 'iqonic_hf_layout',
			'post_status'       => 'publish',
			'posts_per_page'    => -1,
			'meta_key'          => '_layout_meta_key',
			'meta_value'        => 'header',
		);
		global $post;
		$wp_query = get_posts($args);
		$iqonic_header_list = [];

		if ($wp_query) {
			foreach ($wp_query as $header) {
				$iqonic_header_list[$header->post_name] = $header->post_title;
			}
			return $iqonic_header_list;
		}
	}
	protected function set_widget_option()
	{
		Redux::set_section($this->opt_name, array(
			'title' => esc_html__('Header', 'kivicare'),
			'id' => 'header',
			'icon' => 'el el-arrow-up',
			'customizer_width' => '500px',
		));

		Redux::set_section($this->opt_name, array(
			'title' => esc_html__('Header Layout', 'kivicare'),
			'id' => 'header_variation',
			'subsection' => true,
			'desc' => esc_html__('This section contains options for Menu .', 'kivicare'),
			'fields' => array(

				array(
					'id' => 'header_layout',
					'type' => 'button_set',
					'title' => esc_html__('Header Layout', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'custom' => esc_html__('Custom', 'kivicare'),
					),
					'default' => 'default'
				),

				array(
					'id' => 'header_container',
					'type' => 'button_set',
					'title' => esc_html__('Header container', 'kivicare'),
					'options' => array(
						'container-fluid' => esc_html__('full width', 'kivicare'),
						'container' => esc_html__('Container', 'kivicare'),
					),
					'default' => 'container-fluid'
				),


				array(
					'id' => 'header_postion',
					'type' => 'button_set',
					'title' => esc_html__('Header Position', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'over' => esc_html__('Over', 'kivicare'),
					),
					'default' => 'default'
				),


				// --------main header background options start----------//

				array(
					'id' => 'kivicare_header_background_type',
					'type' => 'button_set',
					'title' => esc_html__('Background', 'kivicare'),
					'subtitle' => esc_html__('Select the variation for header background', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'color' => esc_html__('Color', 'kivicare'),
						'image' => esc_html__('Image', 'kivicare'),
						'transparent' => esc_html__('Transparent', 'kivicare')
					),
					'default' => esc_html__('default', 'kivicare')
				),

				array(
					'id' => 'kivicare_header_background_color',
					'type' => 'color',
					'desc' => esc_html__('Set Background Color', 'kivicare'),
					'required' => array('kivicare_header_background_type', '=', 'color'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'kivicare_header_background_image',
					'type' => 'media',
					'url' => false,
					'desc' => esc_html__('Upload Image', 'kivicare'),
					'required' => array('kivicare_header_background_type', '=', 'image'),
					'read-only' => false,
					'subtitle' => esc_html__('Upload background image for header.', 'kivicare'),
				),

				// --------main header Background options end----------//

				// --------main header Menu options start----------//
				array(
					'id' => 'menu_color',
					'type' => 'button_set',
					'title' => esc_html__('Menu Color Options', 'kivicare'),
					'subtitle' => esc_html__('Select Menu color .', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'custom' => esc_html__('Custom', 'kivicare'),
					),
					'default' => esc_html__('default', 'kivicare')
				),
				array(
					'id' => 'header_menu_color',
					'type' => 'color',
					'required' => array('menu_color', '=', 'custom'),
					'desc' => esc_html__('Menu Color', 'kivicare'),
					'mode' => 'background',
					'transparent' => false
				),


				array(
					'id' => 'hover_menu_color',
					'type' => 'color',
					'required' => array('menu_color', '=', 'custom'),
					'desc' => esc_html__('Menu Hover Color', 'kivicare'),
					'mode' => 'background',
					'transparent' => false
				),

				//----sub menu options start---//
				array(
					'id' => 'header_submenu_color_type',
					'type' => 'button_set',
					'title' => esc_html__('Submenu Color Options', 'kivicare'),
					'subtitle' => esc_html__('Select submenu color.', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'custom' => esc_html__('Custom', 'kivicare'),
					),
					'default' => esc_html__('default', 'kivicare')
				),

				array(
					'id' => 'submenu_color',
					'type' => 'color',
					'desc' => esc_html__('Submenu Color', 'kivicare'),
					'required' => array('header_submenu_color_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),


				array(
					'id' => 'hover_submenu_color',
					'type' => 'color',
					'desc' => esc_html__('Submenu Hover Color', 'kivicare'),
					'required' => array('header_submenu_color_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'submenu_background_color',
					'type' => 'color',
					'desc' => esc_html__('Submenu Background Color', 'kivicare'),
					'required' => array('header_submenu_color_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'hover_submenu_bg_color',
					'type' => 'color',
					'desc' => esc_html__('Submenu Background Hover Color', 'kivicare'),
					'required' => array('header_submenu_color_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),
				//----sub menu options end----//


				// --------main header Menu options end----------//

				// --------main header responsive Menu Button Options start----------//
				array(
					'id' => 'burger_menu_button_type',
					'type' => 'button_set',
					'title' => esc_html__('Burger Menu', 'kivicare'),
					'subtitle' => esc_html__('Select color for burger Menu', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'custom' => esc_html__('Custom', 'kivicare')
					),
					'default' => 'default'
				),

				array(
					'id' => 'burger_menu_icon',
					'type' => 'color',
					'desc' => esc_html__('Toggle Icon color', 'kivicare'),
					'required' => array('burger_menu_button_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'burger_menu_popup_bg',
					'type' => 'color',
					'desc' => esc_html__('Model Background color', 'kivicare'),
					'required' => array('burger_menu_button_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'burger_menu_color',
					'type' => 'color',
					'desc' => esc_html__('Menu color', 'kivicare'),
					'required' => array('burger_menu_button_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'burger_hover_menu_color',
					'type' => 'color',
					'desc' => esc_html__('Menu hover color', 'kivicare'),
					'required' => array('burger_menu_button_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'burger_submenu_color',
					'type' => 'color',
					'desc' => esc_html__('Sub Menu Color', 'kivicare'),
					'required' => array('burger_menu_button_type', '=', 'custom'),
					'mode' => 'background',
					'transparent' => false
				),


				// --------main header responsive Menu Button Options end----------//

				// --------main header Search Options start----------//
				array(
					'id' => 'header_display_button',
					'type' => 'button_set',
					'title' => esc_html__('Display Search Icon', 'kivicare'),
					'subtitle' => esc_html__('Turn on to display the Search in header.', 'kivicare'),
					'options' => array(
						'yes' => esc_html__('On', 'kivicare'),
						'no' => esc_html__('Off', 'kivicare')
					),
					'default' => esc_html__('yes', 'kivicare')
				),

				array(
					'id' => 'header_search_text',
					'type' => 'text',
					'title' => esc_html__('Search Text', 'kivicare'),
					'required' => array('header_display_button', '=', 'yes'),
					'validate' => 'text',
					'default' => esc_html__('Search', 'kivicare'),
					'subtitle' => esc_html__('Enter Header Search Text .', 'kivicare'),
				),

				array(
					'id' => 'button_color',
					'type' => 'button_set',
					'required' => array('header_display_button', '=', 'yes'),
					'title' => esc_html__('Search Icon', 'kivicare'),
					'subtitle' => esc_html__('Turn on to display the Search color in header.', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'custom' => esc_html__('Custom', 'kivicare')
					),
					'default' => esc_html__('default', 'kivicare')
				),

				array(
					'id' => 'button_bg_color',
					'type' => 'color',
					'desc' => esc_html__('Icon color', 'kivicare'),
					'required' => array('button_color', '=', 'custom'),
					'desc' => esc_html__('Select for button color options.', 'kivicare'),
					'mode' => 'background',
					'transparent' => false
				),

				// --------main header Search Options end----------//
			)
		));

		//-----Sticky Header Options Start---//
		Redux::set_section($this->opt_name, array(
			'title' => esc_html__('Sticky Header', 'kivicare'),
			'id' => 'kivicare_sticky-header-variation',
			'subsection' => true,
			'desc' => esc_html__('This section contains options for sticky header menu and background color.', 'kivicare'),
			'fields' => array(

				array(
					'id' => 'display_sticky_header',
					'type' => 'button_set',
					'title' => esc_html__('Sticky Header', 'kivicare'),
					'subtitle' => esc_html__('Turn on to display sticky header.', 'kivicare'),
					'options' => array(
						'yes' => esc_html__('On', 'kivicare'),
						'no' => esc_html__('Off', 'kivicare')
					),
					'default' => esc_html__('yes', 'kivicare')
				),
				// --------sticky header background options start----------//
				array(
					'id' => 'sticky_header_bg',
					'type' => 'button_set',
					'required' => array('display_sticky_header', '=', 'yes'),
					'title' => esc_html__('Background', 'kivicare'),
					'subtitle' => esc_html__('Select the variation for sticky header background', 'kivicare'),
					'options' => array(
						'default' => esc_html__('Default', 'kivicare'),
						'color' => esc_html__('Color', 'kivicare'),
						'image' => esc_html__('Image', 'kivicare'),
						'transparent' => esc_html__('Transparent', 'kivicare')
					),
					'default' => esc_html__('default', 'kivicare')
				),

				array(
					'id' => 'sticky_header_bg_color',
					'type' => 'color',
					'desc' => esc_html__('Set Background Color', 'kivicare'),
					'required' => array('sticky_header_bg', '=', 'color'),
					'mode' => 'background',
					'transparent' => false
				),

				array(
					'id' => 'sticky_header_bg_img',
					'type' => 'media',
					'url' => false,
					'desc' => esc_html__('Upload Image', 'kivicare'),
					'required' => array('sticky_header_bg', '=', 'image'),
					'read-only' => false,
					'subtitle' => esc_html__('Upload background image for sticky header.', 'kivicare'),
				),
				// --------sticky header Background options end----------//
				// --------sticky header Menu options start----------//

				array(
					'id'        => 'sticky_menu_color_type',
					'type'      => 'button_set',
					'required'  => array('display_sticky_header', '=', 'yes'),
					'title'     => esc_html__('Menu Color Options', 'kivicare'),
					'subtitle' => esc_html__('Select Menu color for sticky.', 'kivicare'),
					'options'   => array(
						'default' => esc_html__('Default', 'kivicare'),
						'custom' => esc_html__('Custom', 'kivicare'),
					),
					'default'   => esc_html__('default', 'kivicare')
				),
				array(
					'id'            => 'sticky_menu_color',
					'type'          => 'color',
					'required'  => array('sticky_menu_color_type', '=', 'custom'),
					'desc'     => esc_html__('Menu color', 'kivicare'),
					'mode'          => 'background',
					'transparent'   => false
				),

				array(
					'id'            => 'sticky_menu_hover_color',
					'type'          => 'color',
					'required'  => array('sticky_menu_color_type', '=', 'custom'),
					'desc'     => esc_html__('Menu hover color', 'kivicare'),
					'mode'          => 'background',
					'transparent'   => false
				),

				//----sticky sub menu options start---//
				array(
					'id'        => 'sticky_header_submenu_color_type',
					'type'      => 'button_set',
					'title'     => esc_html__('Submenu Color Options', 'kivicare'),
					'subtitle' => esc_html__('Select submenu color for sticky.', 'kivicare'),
					'required'  => array('display_sticky_header', '=', 'yes'),
					'options'   => array(
						'default' => esc_html__('Default', 'kivicare'),
						'custom' => esc_html__('Custom', 'kivicare'),
					),
					'default'   => esc_html__('default', 'kivicare')
				),

				array(
					'id'            => 'sticky_kivicare_header_submenu_color',
					'type'          => 'color',
					'desc'     => esc_html__('Submenu Color', 'kivicare'),
					'required'  => array('sticky_header_submenu_color_type', '=', 'custom'),
					'mode'          => 'background',
					'transparent'   => false
				),


				array(
					'id'            => 'sticky_kivicare_header_submenu_hover_color',
					'type'          => 'color',
					'desc'     => esc_html__('Submenu Hover Color', 'kivicare'),
					'required'  => array('sticky_header_submenu_color_type', '=', 'custom'),
					'mode'          => 'background',
					'transparent'   => false
				),

				array(
					'id'            => 'sticky_kivicare_header_submenu_background_color',
					'type'          => 'color',
					'desc'     => esc_html__('Submenu Background Color', 'kivicare'),
					'required'  => array('sticky_header_submenu_color_type', '=', 'custom'),
					'mode'          => 'background',
					'transparent'   => false
				),

				array(
					'id'            => 'sticky_header_submenu_background_hover_color',
					'type'          => 'color',
					'desc'     => esc_html__('Submenu Background Hover Color', 'kivicare'),
					'required'  => array('sticky_header_submenu_color_type', '=', 'custom'),
					'mode'          => 'background',
					'transparent'   => false
				),
				// --------sticky header Menu options start----------//sss
			)
		));
	}
}
