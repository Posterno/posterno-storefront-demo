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
		remove_action( 'homepage', 'storefront_product_categories', 20 );
		remove_action( 'homepage', 'storefront_recent_products', 30 );
		remove_action( 'homepage', 'storefront_featured_products', 40 );
		remove_action( 'homepage', 'storefront_popular_products', 50 );
		remove_action( 'homepage', 'storefront_on_sale_products', 60 );
		remove_action( 'homepage', 'storefront_best_selling_products', 70 );
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

	<div class="pno-search-form posterno-template">
		<div class="form-row">
			<div class="col">
				<?php echo do_shortcode( '[pno-search-facet facet="1"]' ); ?>
			</div>
			<div class="col">
				<?php echo do_shortcode( '[pno-search-facet facet="2"]' ); ?>
			</div>
			<div class="col-md-2">
				<?php echo do_shortcode( '[pno-search-submit label="Search" submit="' . home_url( 'recent-listings' ) . '"]' ); ?>
			</div>
		</div>
	</div>

	<?php

	echo do_shortcode( '[pno-search-fakequery]' );

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
	'admin_initt',
	function() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Access to the WordPress admin panel has been disabled for the purpose of this demo.' );
		}

	}
);


/**
 * Disable edits to customer's data on WC checkout.
 */
add_filter( 'woocommerce_checkout_update_customer_data', '__return_false' );

/**
 * Disable the shop page.
 */
add_action(
	'template_redirect',
	function() {

		if ( is_shop() ) {
			wp_die( 'For the purpose of this demo, the WooCommerce shop page has been disabled.' );
		}

	}
);

/**
 * Prevent access to wp-login.php
 *
 * @return void
 */
function wpum_prevent_wp_login() {

	global $pagenow;

	$action = ( isset( $_GET['action'] ) ) ? $_GET['action'] : '';

	if ( $pagenow == 'wp-login.php' && ( ! $action || ( $action && ! in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass' ) ) ) ) ) {
		$page = wp_login_url();
		wp_safe_redirect( $page );
		exit();
	}
}
add_action( 'init', 'wpum_prevent_wp_login' );

/**
 * Add GTM.
 */
add_action(
	'wp_head',
	function() {

		?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TS4KKH2');</script>
<!-- End Google Tag Manager -->
		<?php

	},
	100
);

/**
 * Cleanup packages purchased by users on the demo.
 *
 * @return void
 */
function posterno_demo_user_packages_cleanup() {

	$args = [
		'number' => 9999,
		'fields' => 'ID',
	];

	$users = new WP_User_Query( $args );

	if ( ! empty( $users->get_results() ) && is_array( $users->get_results() ) ) {
		foreach ( $users->get_results() as $user_id ) {
			$packages = pno_paid_listings_get_user_packages( $user_id );
			if ( is_array( $packages ) && ! empty( $packages ) ) {
				$query = new \Posterno\PaidListings\Database\Queries\UserPackages();
				foreach ( $packages as $package ) {
					$query->delete_item( $package->get_user_package_id() );
				}
			}
		}
	}

}
add_action( 'pno_live_demo_packages_cleanup', 'posterno_demo_user_packages_cleanup' );

/**
 * Removes the google maps script on the recent listings page to avoid loading the gmap api multiple times
 * when the map facet is present.
 *
 * @return void
 */
function pno_remove_maps_script_on_recent_page() {
	wp_deregister_script( 'pno-listings-page-googlemap' );
}
add_action( 'wp_enqueue_scripts', 'pno_remove_maps_script_on_recent_page', 100 );

/**
 * Automatically delete the submitted claim and throw an error message.
 */
add_action(
	'pno_after_claim_submission',
	function( $id, $listing_id, $form ) {

		if ( $id ) {
			\Posterno\Claims\Plugin::instance()->queries->delete_item( $id );
		}

		throw new \PNO\Exception( 'This functionality has been disabled for this demo.' );

	},
	10,
	3
);

/**
 * Disable review submission.
 */
add_action(
	'pno_reviews_before_review_submission',
	function( $form ) {
		throw new \PNO\Exception( 'This functionality has been disabled for this demo.' );
	}
);

/**
 * Prevent comment submission.
 */
add_filter(
	'preprocess_comment',
	function( $data ) {

		if ( ! is_admin() ) {
			wp_die( 'This functionality has been disabled for this demo.' );
		}

		return $data;

	}
);
