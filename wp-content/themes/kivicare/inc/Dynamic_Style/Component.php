<?php
/**
 * Kivicare\Kivicare\Editor\Component class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style;

use Kivicare\Kivicare\Component_Interface;
use Kivicare\Kivicare\Dynamic_Style\Styles;

/**
 * Class for integrating with the block editor.
 *
 * @link https://wordpress.org/gutenberg/handbook/extensibility/theme-support/
 */
class Component implements Component_Interface {

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug() : string {
		return 'dynamic_style';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'after_setup_theme', array( $this, 'action_add_dynamic_styles' ) );
	}

	public function action_add_dynamic_styles( ) {

		new Styles\Header();
		new Styles\HeaderSticky();
		new Styles\BodyContainer();
		new Styles\Footer();
		new Styles\Banner();
		new Styles\Color();
		new Styles\General();
		new Styles\Loader();
		new Styles\Logo();

	}

	public function kivicare_dynamic_style ( $kivicare_css_array ){
		foreach ( $kivicare_css_array as $css_part ) {
			if ( ! empty( $css_part[ 'value' ] ) ) {
				echo esc_attr($css_part[ 'elements' ]) . "{\n";
				echo esc_attr($css_part[ 'property' ]) . ":" . esc_attr($css_part[ 'value' ]) . ";\n";
				echo "}\n\n";
			}
		}
	}

	public function kivicare_add_inline ( $kivicare_css_array ){
		wp_add_inline_style('kivicare-style',$kivicare_css_array);
	}
}
