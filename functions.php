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
 * Prevent forms processing.
 */
function pno_functionality_disabled_exception() {
	throw new \PNO\Exception( 'This functionality has been disabled for this demo.' );
}
add_action( 'pno_before_registration', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_password_recovery', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_password_change', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_data_erasure', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_data_request', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_delete_account', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_user_update', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_listing_contact', 'pno_functionality_disabled_exception' );
add_action( 'pno_before_listing_editing', 'pno_functionality_disabled_exception' );

/**
 * Prevent listing delete.
 */
add_action(
	'init',
	function() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! isset( $_GET['listing_action'] ) ) {
			return;
		}

		if ( ! isset( $_GET['listing_id'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'verify_listing_action' ) ) {
			return;
		}

		if ( isset( $_GET['listing_action'] ) && $_GET['listing_action'] !== 'delete' ) {
			return;
		}

		if ( ! pno_get_option( 'listing_allow_delete' ) ) {
			return;
		}

		wp_die( 'Listings cannot be deleted in this demo.' );

	},
	5
);

/**
 * Display an alert before the location taxonomy loop.
 */
add_action(
	'pno_before_taxonomy_loop',
	function() {

		$object = get_queried_object();

		if ( $object instanceof WP_Term && $object->taxonomy === 'listings-locations' ) {
			echo '<div class="alert alert-primary" role="alert"><strong>Please note: this is a location taxonomy page. Listings in this page are not arranged by their coordinates.</strong></div>';
		}

	}
);

/**
 * Change the message on the wc thank you page.
 */
add_filter(
	'pno_paid_listings_thank_you_message',
	function() {

		return 'Your listing has been automatically deleted because this is a demo.';

	}
);

/**
 * Automatically delete listings after submission.
 */
add_action(
	'woocommerce_thankyou',
	function( $order_id ) {
		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item ) {
			if ( isset( $item['listing_id'] ) && get_post_type( $item['listing_id'] ) === 'listings' ) {
				wp_delete_post( $item['listing_id'], true );
			}
		}
	},
	4
);

/**
 * Files cannot be uploaded on this demo.
 */
add_filter(
	'pno_upload_file_pre_upload',
	function() {
		return new WP_Error( 'demo-upload', 'Files cannot be uploaded on this demo.' );
	}
);

/**
 * Redirect when trying to upgrade a listing on the demo.
 */
add_action(
	'wp_loaded',
	function() {

		//phpcs:ignore
		if ( ! isset( $_POST[ 'do_listing_upgrade_nonce' ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['do_listing_upgrade_nonce'], 'do_listing_upgrade' ) ) {
			return;
		}

		$listing_id = isset( $_POST['listing_id'] ) && ! empty( $_POST['listing_id'] ) ? absint( $_POST['listing_id'] ) : false;
		$ref        = pno_paid_listings_get_listing_upgrade_url( $listing_id );

		wp_safe_redirect(
			add_query_arg(
				[
					'error' => 'demo',
				],
				$ref
			)
		);
		exit;

	},
	5
);

/**
 * Change message when upgrading on the demo.
 */
add_filter(
	'pno_paid_listings_upgrade_error_message',
	function() {
		return 'Listings cannot be upgraded on this demo.';
	}
);

/**
 * Lock access to admin panel.
 */
add_action(
	'admin_init',
	function() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access to the WordPress admin panel has been disabled for the purpose of this demo.' );
		}

	}
);
