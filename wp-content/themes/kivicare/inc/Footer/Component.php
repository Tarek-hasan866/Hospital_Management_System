<?php

/**
 * Kivicare\Kivicare\Editor\Component class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Footer;

use Kivicare\Kivicare\Component_Interface;
use Kivicare\Kivicare\Templating_Component_Interface;

/**
 * Class for managing sidebars.
 *
 * Exposes template tags:
 * * `kivicare()->get_footer_option()`
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/
 */
class Component implements Component_Interface, Templating_Component_Interface
{

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string
	{
		return 'footer';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize()
	{
		add_action('widgets_init', array($this, 'action_register_footers'));
		add_action('after_setup_theme', array($this, 'action_register_redux_footers'));
	}

	/**
	 * Gets template tags to expose as methods on the Template_Tags class instance, accessible through `kivicare()`.
	 *
	 * @return array Associative array of $method_name => $callback_info pairs. Each $callback_info must either be
	 *               a callable or an array with key 'callable'. This approach is used to reserve the possibility of
	 *               adding support for further arguments in the future.
	 */
	public function template_tags(): array
	{
		return array(
			'get_footer_option' => array($this, 'get_footer_option')
		);
	}

	/**
	 * Registers the footer.
	 */
	public function action_register_footers()
	{
		$footer_option = [
			1 => 'footer_one',
			2 => 'footer_two',
			3 => 'footer_three'
		];

		$this->register_footers($footer_option);
	}

	public function action_register_redux_footers()
	{
		if (class_exists('Redux')) {
			if (empty($theme_option['footer_four'])) {

				$footer_option = [
					4 => 'footer_four',
					5 => 'footer_five'
				];

				$this->register_footers($footer_option);
			}
		}
	}

	public function register_footers($footer_option)
	{

		$theme_option = get_option('kivicare-options');

		$default = [
			'1' => esc_html__('text-left', 'kivicare'),
			'2' => esc_html__('text-right', 'kivicare'),
			'3' => esc_html__('text-center', 'kivicare'),
		];

		foreach ($footer_option as $key => $item) {
			$footer = '';
			if (!empty($theme_option[$item])) {
				$footer = $default[$theme_option[$item]];
			}
			register_sidebar(
				array(
					'name'          => esc_html__('Footer Area ' . $key, 'kivicare'),
					'class'         => 'nav-list',
					'id'            => 'footer_' . ($key) . '_sidebar',
					'before_widget' => '<div class="widget %2$s ' . esc_attr($footer) . '">',
					'after_widget'  => '</div>',
					'before_title'  => '<h5 class="footer-title mt-0"> <span>  ',
					'after_title'   => ' </span></h5>',
				)
			);
		}
	}

	public function get_footer_option(): array
	{
		$data = [];
		if (
			is_active_sidebar('footer_1_sidebar') || is_active_sidebar('footer_2_sidebar') ||
			is_active_sidebar('footer_3_sidebar') || is_active_sidebar('footer_4_sidebar') ||
			is_active_sidebar('footer_5_sidebar')
		) {
			if (class_exists('ReduxFramework')) {

				$kivicare_options = get_option('kivicare-options');
				$options = !empty($kivicare_options['kivicare_footer_width']) ? $kivicare_options['kivicare_footer_width'] : '';
				
				$data['standard_footer'] = '';
				switch ($options) {
					case 1:
						$data['value'] = ['col-12'];
						break;
					case 2:
						$data['value'] = ['col-lg-6 col-sm-6', 'col-lg-6 col-sm-6'];
						break;
					case 3:
						$data['value'] = ['col-lg-4 col-sm-6', 'col-lg-4 col-sm-6 mt-4 mt-lg-0 mt-md-0', 'col-lg-4 col-sm-6 mt-lg-0 mt-md-5 mt-4'];
						break;
					case 4:
						$data['value'] = ['col-lg-4 col-sm-6 mt-4 mt-lg-0 mt-md-0', 'col-lg-2  col-sm-6 mt-lg-0 mt-4', 'col-lg-3 col-sm-6 mt-lg-0 mt-4', 'col-lg-3 col-sm-6 mt-lg-0 mt-4'];
						break;
					case 5:
						$data['value'] = ['col-lg-3 col-sm-12', 'col-lg-3 col-sm-6 ', 'col-lg-2 col-sm-6 mt-4 mt-lg-0 mt-md-0', 'col-lg-2 col-sm-6 mt-lg-0 mt-4', 'col-lg-2 col-sm-6 mt-lg-0 mt-4'];
						break;
					default:
						$data['value'] = ['col-lg-3 col-sm-6 mt-4 mt-xl-0 mt-md-0', 'col-lg-3  col-sm-6 mt-lg-0 mt-4', 'col-lg-3 col-sm-6 mt-lg-0 mt-4', 'col-lg-3 col-sm-6 mt-lg-0 mt-4'];
				}
			} else {
				$data['value'] = ['col-lg-4 col-sm-6', 'col-lg-4 col-sm-6 mt-3 mt-lg-0', 'col-lg-4 col-sm-12 mt-3 mt-lg-0'];
			}
		}
		return $data;
	}
}
