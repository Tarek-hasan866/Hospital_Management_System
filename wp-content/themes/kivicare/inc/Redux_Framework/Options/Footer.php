<?php

/**
 * Kivicare\Kivicare\Redux_Framework\Options\Footer class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Redux_Framework\Options;

use Redux;
use Kivicare\Kivicare\Redux_Framework\Component;

class Footer extends Component
{

	public function __construct()
	{
		$this->set_widget_option();
	}

	protected function set_widget_option()
	{
		Redux::set_section($this->opt_name, array(
			'title' => esc_html__('Footer', 'kivicare'),
			'id' => 'footer',
			'icon' => 'el el-arrow-down',
			'customizer_width' => '500px',
		));

		Redux::set_section($this->opt_name, array(
			'title' => esc_html__('Footer Image', 'kivicare'),
			'id' => 'footer-logo',
			'subsection' => true,
			'desc' => esc_html__('This section contains options for footer.', 'kivicare'),
			'fields' => array(

				array(
					'id' => 'display_footer_bg_image',
					'type' => 'button_set',
					'title' => esc_html__('Display Footer Background Image', 'kivicare'),
					'subtitle' => esc_html__('Display Footer Background Image On All page', 'kivicare'),
					'options' => array(
						'yes' => esc_html__('Yes', 'kivicare'),
						'no' => esc_html__('No', 'kivicare')
					),
					'default' => esc_html__('no', 'kivicare')
				),
				array(
					'id'       => 'logo_footer',
					'type'     => 'media',
					'url'      => false,
					'title'    => esc_html__('Footer Logo', 'kivicare'),
					'read-only' => false,
					'subtitle' => esc_html__('Upload Footer Logo for your Website.', 'kivicare'),
					'default'  => array('url' => get_template_directory_uri() . '/assets/images/logo.svg'),
				),

				array(
					'id' => 'footer_bg_image',
					'type' => 'media',
					'url' => false,
					'title' => esc_html__('Footer Background Image', 'kivicare'),
					'required' => array('display_footer_bg_image', '=', 'yes'),
					'read-only' => false,
					'subtitle' => esc_html__('Upload Footer image for your Website.', 'kivicare'),
					'default' => array('url' => get_template_directory_uri() . '/assets/images/redux/footer-img.jpg'),
				),

				array(
					'id' => 'change_footer_color',
					'type' => 'button_set',
					'title' => esc_html__('Change Footer Color', 'kivicare'),
					'subtitle' => esc_html__('Turn on to Change Footer Background Color', 'kivicare'),
					'options' => array(
						'0' => esc_html__('Yes', 'kivicare'),
						'1' => esc_html__('No', 'kivicare')
					),
					'default' => esc_html__('0', 'kivicare')
				),

				array(
					'id' => 'footer_bg_color',
					'type' => 'color',
					'subtitle' => esc_html__('Choose Footer Background Color', 'kivicare'),
					'required' => array('change_footer_color', '=', '0'),
					'mode' => 'background',
					'transparent' => false
				),

			)
		));

		Redux::set_section($this->opt_name, array(
			'title' => esc_html__('Footer Option', 'kivicare'),
			'id' => 'footer_section',
			'subsection' => true,
			'desc' => esc_html__('This section contains options for footer.', 'kivicare'),
			'fields' => array(

				array(
					'id' => 'footer_top',
					'type' => 'button_set',
					'title' => esc_html__('Display Footer Top', 'kivicare'),
					'subtitle' => esc_html__('Display Footer Top On All page', 'kivicare'),
					'options' => array(
						'yes' => esc_html__('Yes', 'kivicare'),
						'no' => esc_html__('No', 'kivicare')
					),
					'default' => esc_html__('yes', 'kivicare')
				),

				array(
					'id' => 'kivicare_footer_width',
					'type' => 'image_select',
					'title' => esc_html__('Footer Layout Type', 'kivicare'),
					'required' => array('footer_top', '=', 'yes'),
					'subtitle' => wp_kses(__('<br />Choose among these structures (1column, 2column and 3column) for your footer section.<br />To fill these column sections you should go to appearance > widget.<br />And add widgets as per your needs.', 'kivicare'), array('br' => array())),
					'options' => array(
						'1' => array('title' => esc_html__('Footer Layout 1', 'kivicare'), 'img' => get_template_directory_uri() . '/assets/images/redux/footer_first.png'),
						'2' => array('title' => esc_html__('Footer Layout 2', 'kivicare'), 'img' => get_template_directory_uri() . '/assets/images/redux/footer_second.png'),
						'3' => array('title' => esc_html__('Footer Layout 3', 'kivicare'), 'img' => get_template_directory_uri() . '/assets/images/redux/footer_third.png'),
						'4' => array('title' => esc_html__('Footer Layout 4', 'kivicare'), 'img' => get_template_directory_uri() . '/assets/images/redux/footer_four.png'),
						'5' => array('title' => esc_html__('Footer Layout 5', 'kivicare'), 'img' => get_template_directory_uri() . '/assets/images/redux/footer_five.png'),
					),
					'default' => '4',
				),

				array(
					'id' => 'footer_one',
					'type' => 'select',
					'title' => esc_html__('Select 1 Footer Alignment', 'kivicare'),
					'required' => array('footer_top', '=', 'yes'),
					'options' => array(
						'1' => 'Left',
						'2' => 'Right',
						'3' => 'Center',
					),
					'default' => '1',
				),

				array(
					'id' => 'footer_two',
					'type' => 'select',
					'title' => esc_html__('Select 2 Footer Alignment', 'kivicare'),
					'required' => array('footer_top', '=', 'yes'),
					'options' => array(
						'1' => 'Left',
						'2' => 'Right',
						'3' => 'Center',
					),
					'default' => '1',
				),

				array(
					'id' => 'footer_three',
					'type' => 'select',
					'title' => esc_html__('Select 3 Footer Alignment', 'kivicare'),
					'required' => array('footer_top', '=', 'yes'),
					'options' => array(
						'1' => 'Left',
						'2' => 'Right',
						'3' => 'Center',
					),
					'default' => '1',
				),

				array(
					'id' => 'footer_four',
					'type' => 'select',
					'title' => esc_html__('Select 4 Footer Alignment', 'kivicare'),
					'required' => array('footer_top', '=', 'yes'),
					'options' => array(
						'1' => 'Left',
						'2' => 'Right',
						'3' => 'Center',
					),
					'default' => '1',
				),
				
				array(
					'id' => 'footer_five',
					'type' => 'select',
					'title' => esc_html__('Select 5 Footer Alignment', 'kivicare'),
					'required' => array('footer_top', '=', 'yes'),
					'options' => array(
						'1' => 'Left',
						'2' => 'Right',
						'3' => 'Center',
					),
					'default' => '1',
				),
			)
		));

		Redux::set_section($this->opt_name, array(
			'title' => esc_html__('Footer Copyright', 'kivicare'),
			'id' => 'footer_copyright',
			'subsection' => true,
			'fields' => array(

				array(
					'id' => 'display_copyright',
					'type' => 'button_set',
					'title' => esc_html__('Display Copyrights', 'kivicare'),
					'options' => array(
						'yes' => esc_html__('Yes', 'kivicare'),
						'no' => esc_html__('No', 'kivicare')
					),
					'default' => esc_html__('yes', 'kivicare')
				),
				array(
					'id' => 'footer_copyright_align',
					'type' => 'select',
					'title' => esc_html__('Copyrights Alignment', 'kivicare'),
					'required' => array('display_copyright', '=', 'yes'),
					'options' => array(
						'left' => 'Left',
						'right' => 'Right',
						'center' => 'Center',
					),
					'default' => 'center',
				),

				array(
					'id' => 'footer_copyright',
					'type' => 'editor',
					'required' => array('display_copyright', '=', 'yes'),
					'title' => esc_html__('Copyrights Text', 'kivicare'),
					'default' => esc_html__('© 2021 kivicare. All Rights Reserved.', 'kivicare'),
				),
			)
		));


	}
}
