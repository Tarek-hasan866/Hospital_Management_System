<?php

/**
 * Kivicare\Kivicare\Dynamic_Style\Styles\Banner class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Dynamic_Style\Styles;

use Kivicare\Kivicare\Dynamic_Style\Component;
use function add_action;

class Banner extends Component
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'kivicare_banner_dynamic_style'), 20);
        add_action('wp_enqueue_scripts', array($this, 'kivicare_opacity_color'), 20);
        add_action('wp_enqueue_scripts', array($this, 'kivicare_banner_hide'), 20);
    }

    public function kivicare_banner_dynamic_style()
    {
        $page_id = get_queried_object_id();
        $kivicare_options = get_option('kivicare-options');
        $dynamic_css = '';

        if (isset($kivicare_options['display_banner'])) {
            if ($kivicare_options['display_banner'] == 'no') {
                $dynamic_css .=
                    '.kivicare-breadcrumb-one { display: none !important; }
                    .content-area .site-main {padding : 0 !important; }';
            }
        }

        if (isset($kivicare_options['display_title'])) {

            if ($kivicare_options['display_title'] == 'no') {
                $dynamic_css .=
                    '.kivicare-breadcrumb-one .title { display: none !important; }';
            }
        }

        if (isset($kivicare_options['display_breadcumb'])) {

            if ($kivicare_options['display_breadcumb'] == 'no') {
                $dynamic_css .=
                    '.kivicare-breadcrumb-one .breadcrumb { display: none !important; }';
            }
        }

        if (isset($kivicare_options['bg_title_color'])) {

            if ($kivicare_options['bg_title_color'] == 'yes') {
                $dynamic = $kivicare_options['bg_title_color'];
                $dynamic_css .=
                    '.kivicare-breadcrumb-one .title { color: ' . $dynamic . ' !important; }';
            }
        }
        if (isset($kivicare_options['bg_type'])) {
            $opt = $kivicare_options['bg_type'];
            if ($opt == '1') {
                if (isset($kivicare_options['bg_color']) && !empty($kivicare_options['bg_color'])) {
                    $dynamic = $kivicare_options['bg_color'];
                    $dynamic_css .=
                        '.kivicare-breadcrumb-one { background: ' . $dynamic . ' !important; }';
                }
            }
            if ($opt == '2') {
                if (isset($kivicare_options['banner_image']['url'])) {
                    $dynamic = $kivicare_options['banner_image']['url'];
                    $dynamic_css .=
                        '.kivicare-breadcrumb-one { background-image: url(' . $dynamic . ') !important; }';
                }
            }
        }

        wp_add_inline_style('kivicare-global', $dynamic_css);
    }
    public function kivicare_opacity_color()
    {
        //Set Background Opacity Color
        $kivicare_options = get_option('kivicare-options');

        if (!empty($kivicare_options['bg_opacity']) && $kivicare_options['bg_opacity'] == "3") {
            $bg_opacity = $kivicare_options['opacity_color']['rgba'];
        }
        $dynamic_css = '';

        if (!empty($kivicare_options['bg_opacity']) && $kivicare_options['bg_opacity'] == "3") {
            if (!empty($bg_opacity) && $bg_opacity != '#ffffff') {
                $dynamic_css .= "
            .breadcrumb-video::before,.breadcrumb-bg::before, .breadcrumb-ui::before {
                background : $bg_opacity !important;
            }";
            }
        }
        wp_add_inline_style('kivicare-global', $dynamic_css);
    }

    public function kivicare_banner_hide()
    { 
        $kivicare_options = get_option('kivicare-options');
        $banner_hide = '';
        $pages = '';
        if(isset($kivicare_options['pages_select'])){
            $pages = $kivicare_options['pages_select'];
            foreach($pages as $data){

                $page = get_page_by_path( $data );
                if(isset($page)){
                    $banner_hide .= '.page-id-'.$page->ID.' .kivicare-breadcrumb-one { display: none !important; }';
                }
    
            }
        }

        wp_add_inline_style('kivicare-global', $banner_hide);
    }

}
