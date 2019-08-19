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
		remove_action( 'storefront_homepage', 'storefront_page_content', 20 );
	}
);

/**
 * Load custom css file.
 */
add_action(
	'wp_enqueue_scripts',
	function() {

		wp_enqueue_style( 'posterno-demo', get_stylesheet_directory_uri() . '/dist/css/screen.css', false, '1.0.0' );

	},
	20
);

/**
 * Change the title on the homepage.
 *
 * @since 1.0.0
 */
function storefront_homepage_header() {
	edit_post_link( __( 'Edit this section', 'storefront' ), '', '', '', 'button storefront-hero__button-edit' );
	?>
	<header class="entry-header">
		<h1 class="entry-title">Discover great places with Posterno</h1>
	</header><!-- .entry-header -->
	<?php
}

/**
 * Prevent registrations.
 */
add_action(
	'pno_before_registration',
	function() {

		throw new \PNO\Exception( 'Registration is disabled for this demo.' );

	}
);
