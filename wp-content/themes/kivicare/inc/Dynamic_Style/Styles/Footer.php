<?php

/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\Footer class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class Footer extends Component
{

	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'kivicare_footer_dynamic_style'), 20);
	}

	public function kivicare_footer_dynamic_style()
	{

		$page_id = get_queried_object_id();
		$kivicare_options = get_option('kivicare-options');
		$footer_css = '';
		if (isset($kivicare_options['footer_top'])) {

			if ($kivicare_options['footer_top'] == 'no') {
				$footer_css = '.footer-top { 
					display : none !important;
				}';
			}
		}

		if (isset($kivicare_options['change_footer_color'])) {

			if (isset($kivicare_options['footer_bg_color']) && $kivicare_options['change_footer_color'] == '0') {
				$footer_bg_color = $kivicare_options['footer_bg_color'];
				$footer_css .= ".footer {
					background-color: $footer_bg_color !important;
				}";
			}
		}
	

		wp_add_inline_style('kivicare-global', $footer_css);
	}
}
