<?php

/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\General class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class General extends Component
{
	public function __construct()
	{

		add_action('wp_enqueue_scripts', array($this, 'kivicare_create_general_style'), 20);
	}

	public function kivicare_create_general_style()
	{

		$kivicare_option = get_option('kivicare-options');
		$general_var = ':root { ';

		if (isset($kivicare_option['grid_container']) && !empty($kivicare_option['grid_container'])) {
			$general = $kivicare_option['grid_container']['width'];
			$general_var .= ' --content-width: ' . $general . ' !important;';
		}
		$general_var .= '}';
		if (isset($kivicare_option['body_set_option']) && $kivicare_option['body_set_option'] == 1) {
			if (
				isset($kivicare_option['body_color'])  && !empty($kivicare_option['body_color'])
			) {
				$general = $kivicare_option['body_color'];
				$general_var .= ' body { background : ' . $general . ' !important; }';
			}
		}
		if (isset($kivicare_option['body_set_option']) && $kivicare_option['body_set_option'] == 3) {
			if (isset($kivicare_option['body_image']['url']) && !empty($kivicare_option['body_image']['url'])) {
				$general = $kivicare_option['body_image']['url'];
				$general_var .= '
					body { background-image: url(' . $general . ') !important; }';
			}
		}

		if (isset($kivicare_option['back_to_top_btn']) && $kivicare_option['back_to_top_btn'] == 'no') {
			if (isset($kivicare_option['back_to_top_btn']) && !empty($kivicare_option['back_to_top_btn'])) {
				$general_var .= '
					#back-to-top { display: none !important; }';
			}
		}

		wp_add_inline_style('kivicare-global', $general_var);
	}
}
