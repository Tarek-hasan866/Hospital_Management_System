<?php

/**
 * Template part for displaying the Breadcrumb 
 *
 * @package kivicare
 */

namespace Kivicare\Kivicare;

if (is_front_page()) {
        if (is_home()) { ?>
            <div class="kivicare-breadcrumb-one text-center green-bg">
                <div class="container">
                    <div class="row flex-row-reverse">
                        <div class="col-sm-12">
                            <div class="heading-title white kivicare-breadcrumb-title">
                                <h1 class="title"><?php esc_html_e('Home', 'kivicare'); ?></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php }
}
kivicare()->kivicare_inner_breadcrumb();
?>