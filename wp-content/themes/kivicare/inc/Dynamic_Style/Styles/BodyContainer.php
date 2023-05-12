<?php
/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\BodyContainer class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class BodyContainer extends Component
{

	public function __construct()
	{
		if (class_exists('ReduxFramework')) {
			add_action('wp_enqueue_scripts', array( $this,'kivicare_container_width'), 21);
		}
	}

	public function kivicare_container_width()
	{
		$kivicare_options = get_option('kivicare-options');

		$box_container_width = "";

		if (isset($kivicare_options['opt-slider-label']) && !empty($kivicare_options['opt-slider-label'])) {

			$container_width = $kivicare_options['opt-slider-label'];

			$box_container_width = "body.iq-container-width .container,
        							body.iq-container-width .elementor-section.elementor-section-boxed>
        							.elementor-container { max-width: " . $container_width . "px; } ";
		}


		wp_add_inline_style('kivicare-style',
			$box_container_width
		);
	}
}
