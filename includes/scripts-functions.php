<?php

/**
 * Add all the assets of the public side
 *
 * @since 1.0.0
 *
 * @return void
 */
function load_public_assets($hook) {
	wp_register_style( 'wccs-fontawesome', WC_SLIDER_ASSETS_URL . "/vendor/font-awesome/css/font-awesome.css", [], date( 'i' ) );
	wp_register_style( 'wc-category-slider', WC_SLIDER_ASSETS_URL . "/css/wc-category-slider-public.css", [ 'wccs-fontawesome' ], date( 'i' ) );
	//		wp_register_script('owl-carousel', WC_CATEGORY_SLIDER_ASSETS_URL."/public/js/owl-carousel.js", ['jquery'], date('i'), true);
	//		wp_register_script('image-liquid', WC_CATEGORY_SLIDER_ASSETS_URL."/public/js/image-liquid.js", ['jquery'], date('i'), true);
	//		wp_register_script('wc-category-slider', WC_CATEGORY_SLIDER_ASSETS_URL."/public/js/wc-category-slider-public.js", ['jquery', 'owl-carousel', 'image-liquid'], date('i'), true);
	//		wp_localize_script('wc-category-slider', 'WCS', ['ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => 'wc-category-slider']);
	wp_enqueue_style( 'wc-category-slider' );
	//		wp_enqueue_script('wc-category-slider');
}

add_action( 'wp_enqueue_scripts', 'load_public_assets' );

/**
 * Add all the assets required by the plugin
 *
 * @since 1.0.0
 *
 * @return void
 */
function load_admin_assets( $hook ) {

	if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) ) {
		return;
	}
	global $post;
	if ( 'wc_category_slider' != $post->post_type ) {
		return;
	}
	?><?php
	wp_register_style( 'wccs-fontawesome', WC_SLIDER_ASSETS_URL . "/vendor/font-awesome/css/font-awesome.css", [], date( 'i' ) );

	wp_register_style( 'wc-category-slider', WC_SLIDER_ASSETS_URL . "/css/admin.css", [ 'woocommerce_admin_styles', 'wccs-fontawesome' ], date( 'i' ) );
	wp_register_script( 'wc-category-slider', WC_SLIDER_ASSETS_URL . "/js/wc-category-slider-admin.js", [
		'jquery',
		'wp-util',
		'select2',
	], date( 'i' ), true );
	//wp_localize_script('wc-category-slider', 'WCS', ['ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce' => 'wc-category-slider']);
	wp_enqueue_style( 'wc-category-slider' );
	wp_enqueue_script( 'wc-category-slider' );
}

add_action( 'admin_enqueue_scripts', 'load_admin_assets' );

