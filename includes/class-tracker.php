<?php

namespace Pluginever\WoocommerceCategorySlider;

/**
 * Tracker class
 */
class Tracker extends \Pluginever_Insights {

    public function __construct() {

        $notice = __( 'Want to help make <strong>WooCommerce Category Slider</strong> even more awesome? Allow PluginEver to collect non-sensitive diagnostic data and usage information.', 'woocatslider' );

        parent::__construct( 'woo-category-slider-by-pluginever', 'WooCommerce Category Slider', WCS_FILE, $notice );
    }

    /**
     * Get the extra data
     *
     * @return array
     */
    protected function get_extra_data() {
        $data = array(
            'wc_products'   => $this->get_post_count( 'product' ),
            'wc_categories' => wp_count_terms( 'product_cat' ),
            'shortcodes'    => $this->get_post_count( 'wc_category_slider' ),
            'is_pro'        => wc_category_slider_is_pro_active() ? 'yes' : 'no',
        );

        return $data;
    }


    /**
     * Explain the user which data we collect
     *
     * @return array
     */
    protected function data_we_collect() {
        $data = array(
            'Server environment details (php, mysql, server, WordPress versions)',
            'Number of WC Products in your site',
            'Number of WC Categories in your site',
            'Site language',
            'Number of active and inactive plugins',
            'Site name and url',
            'Your name and email address',
        );

        return $data;
    }


}
