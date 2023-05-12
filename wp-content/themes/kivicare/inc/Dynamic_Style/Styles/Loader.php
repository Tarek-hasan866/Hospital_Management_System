<?php
/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\Loader class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class Loader extends Component
{

	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'kivicare_loader_options'), 20);
	}

	public function kivicare_loader_options(){
        $kivicare_options = get_option('kivicare-options');
        $loader_css = '';
            if(isset($kivicare_options['loader_bg_color'])){
                $loader_var = $kivicare_options['loader_bg_color'];
                if( !empty($loader_var) && $loader_var != '#ffffff') {
                    $loader_css .= "
                    #loading {
                        background : $loader_var !important;
                    }"; 
                }
            }            
            wp_add_inline_style( 'kivicare-global', $loader_css );
    }
}
