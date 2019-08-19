<?php
/**
 * Theme functions file.
 *
 * This file is used to bootstrap the theme.
 *
 * @package posterno-storefront-demo
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the product search and cart from the header.
 */
add_action(
	'init',
	function() {
		remove_action( 'storefront_header', 'storefront_product_search', 40 );
		remove_action( 'storefront_header', 'storefront_header_cart', 60 );
	}
);

/**
 * Load custom css file.
 */
add_action(
	'wp_enqueue_scripts',
	function() {

		wp_enqueue_style( 'posterno-demo', get_stylesheet_directory_uri() . '/dist/css/screen.css', false, '1.0.0' );

	}
);
