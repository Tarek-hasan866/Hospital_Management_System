<?php

/**
 * Kivicare\Kivicare\Redux_Framework\Options\Banner class
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare\Redux_Framework\Options;

use Redux;
use Kivicare\Kivicare\Redux_Framework\Component;

class Banner extends Component
{

    public function __construct()
    {
        $this->set_widget_option();
    }
    function iqonic_get_post($post_type = "", $post_status = "publish")
    {

        $args = array(
            'post_type'         => $post_type,
            'post_status'       => $post_status,
            'posts_per_page'    => -1
        );
        
        $post_q = get_posts($args);
        $iqonic_blog_list = [];
        if ($post_q) {
            foreach ($post_q as $post) {
                $iqonic_blog_list[$post->post_name] = get_the_title($post->ID);
            }
            return $iqonic_blog_list;
        }
        wp_reset_postdata();
    }
    protected function set_widget_option()
    {
        Redux::set_section($this->opt_name, array(
            'title' => esc_html__('Page Banner Setting', 'kivicare'),
            'id' => 'banner',
            'icon' => 'el el-cog',
            'desc' => esc_html__('This section contains options for Page Breadcrumbs Area.', 'kivicare'),
            'fields' => array(

                array(
                    'id'       => 'pages_select',
                    'type'     => 'select',
                    'multi'    => true,
                    'title'    => esc_html__('Select pages for hide banner', 'kivicare'),
                    'options'  => $this->iqonic_get_post("page"),
                    'default'  => array('2', '3'),
                ),

                array(
                    'id'       => 'page_default_banner_image',
                    'type'     => 'media',
                    'url'      => true,
                    'title'    => esc_html__('Default Banner Image', 'kivicare'),
                    'read-only' => false,
                    'subtitle' => esc_html__('Upload default banner image for your Website. Default will be blank.', 'kivicare') . '<b>' . esc_html__("(Note:Only Display Banner Style Second & Third)", "kivicare") . '</b>',
                ),

                array(
                    'id'      => 'bg_image',
                    'type'    => 'image_select',
                    'title'   => esc_html__('Select Banner Style', 'kivicare'),
                    'subtitle' => esc_html__('Select the style that best fits your needs.', 'kivicare'),
                    'options' => array(
                        '1'      => array(
                            'alt' => 'Style1',
                            'img' => get_template_directory_uri() . '/assets/images/redux/bg-1.jpg',
                        ),
                        '2'      => array(
                            'alt' => 'Style2',
                            'img' => get_template_directory_uri() . '/assets/images/redux/bg-2.jpg',
                        ),
                        '3'      => array(
                            'alt' => 'Style3',
                            'img' => get_template_directory_uri() . '/assets/images/redux/bg-3.jpg',
                        ),
                        '4'      => array(
                            'alt' => 'Style4',
                            'img' => get_template_directory_uri() . '/assets/images/redux/bg-4.jpg',
                        ),
                        '5'      => array(
                            'alt' => 'Style5',
                            'img' => get_template_directory_uri() . '/assets/images/redux/bg-5.jpg',
                        ),
                    ),
                    'default' => '1'
                ),


                array(
                    'id' => 'display_banner',
                    'type' => 'button_set',
                    'title' => esc_html__('Display Banner on inner Pages', 'kivicare'),
                    'options' => array(
                        'yes' => esc_html__('Yes', 'kivicare'),
                        'no' => esc_html__('No', 'kivicare')
                    ),
                    'default' => esc_html__('yes', 'kivicare')
                ),

                array(
                    'id' => 'display_breadcrumbs',
                    'type' => 'button_set',
                    'title' => esc_html__('Display Breadcrumbs on Banner', 'kivicare'),
                    'options' => array(
                        'yes' => esc_html__('Yes', 'kivicare'),
                        'no' => esc_html__('No', 'kivicare')
                    ),
                    'required' => array(
                        'display_banner',
                        '=',
                        'yes'
                    ),
                    'default' => esc_html__('yes', 'kivicare')
                ),

                array(
                    'id' => 'display_title',
                    'type' => 'button_set',
                    'title' => esc_html__('Display Breadcrumbs on Title', 'kivicare'),
                    'options' => array(
                        'yes' => esc_html__('Yes', 'kivicare'),
                        'no' => esc_html__('No', 'kivicare')
                    ),
                    'required' => array(
                        'display_banner',
                        '=',
                        'yes'
                    ),
                    'default' => esc_html__('yes', 'kivicare')
                ),


                array(
                    'id' => 'breadcum_title_tag',
                    'type' => 'select',
                    'title' => __('Select Breadcrumbs Title Tag', 'kivicare'),
                    'options' => array(
                        'h1' => 'h1',
                        'h2' => 'h2',
                        'h3' => 'h3',
                        'h5' => 'h4',
                        'h5' => 'h5',
                        'h6' => 'h6'

                    ),
                    'required' => array(
                        'display_title',
                        '=',
                        'yes'
                    ),
                    'default' => 'h2'
                ),

                array(
                    'id' => 'bg_title_color',
                    'type' => 'color',
                    'title' => esc_html__('Set Title Color', 'kivicare'),
                    'default'       => '',
                    'mode' => 'background',
                    'required' => array(
                        'display_title',
                        '=',
                        'yes'
                    ),
                    'transparent' => false
                ),

                array(
                    'id'       => 'bg_type',
                    'type'     => 'button_set',
                    'title'    => esc_html__('Banner Background Type', 'kivicare'),
                    'options'  => array(
                        '1' => 'Color',
                        '2' => 'Image'
                    ),
                    'default'  => '2'
                ),

                array(
                    'id'       => 'banner_image',
                    'type'     => 'media',
                    'url'      => false,
                    'title'    => esc_html__('Set Body Image', 'kivicare'),
                    'read-only' => false,
                    'required'  => array('bg_type', '=', '2'),
                    'subtitle' => esc_html__('Upload Image for your body.', 'kivicare'),
                    'default'  => array('url' => get_template_directory_uri() . '/assets/images/redux/banner.jpg'),
                ),

                array(
                    'id'            => 'bg_color',
                    'type'          => 'color',
                    'title'         => esc_html__('Set Background Color', 'kivicare'),
                    'required'  => array('bg_type', '=', '1'),
                    'mode'          => 'background',
                    'transparent'   => false
                ),

                array(
                    'id'       => 'bg_opacity',
                    'type'     => 'button_set',
                    'title'    => esc_html__('Background Opacity Color', 'kivicare'),
                    'required' => array(
                        array('bg_type', '!=', '1')
                    ),
                    'subtitle' => esc_html__('Select this option for Background Opacity Color.', 'kivicare'),
                    'options'  => array(
                        '1' => 'None',
                        '2' => 'Dark',
                        '3' => 'Custom'
                    ),
                    'default'  => '1'
                ),


                array(
                    'id'            => 'opacity_color',
                    'type'          => 'color_rgba',
                    'title'         => esc_html__('Background Gradient Color', 'kivicare'),
                    'required'  => array('bg_opacity', '=', '3'),
                    'subtitle'      => esc_html__('Choose body Gradient background color', 'kivicare'),
                    'transparent'   => false
                ),

            )
        ));
    }
}
