<?php
/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\Logo class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class Logo extends Component
{

	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'kivicare_logo_options'), 20);
	}

	public function kivicare_logo_options(){
        $kivicare_options = get_option('kivicare-options');
        $logo_var = '';
        if(isset($kivicare_options['header_radio']) && $kivicare_options['header_radio'] == 1){
            if(isset($kivicare_options['header_color'])){
                $logo = $kivicare_options['header_color'];
                    $logo_var .= "
                    .navbar-light .navbar-brand {
                        color : $logo !important;
                    }"; 
            }  
        }          
            wp_add_inline_style( 'kivicare-global', $logo_var );
    }
}
