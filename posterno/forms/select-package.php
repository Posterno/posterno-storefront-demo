<?php
/**
 * The template for displaying the packages during submission
 *
 * This template can be overridden by copying it to yourtheme/posterno/forms/select-package.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( empty( $data->packages ) ) {
	posterno()->templates
		->set_template_data(
			[
				'type'    => 'danger',
				'message' => esc_html__( 'No packages found.', 'posterno-wc-paid-listings' ),
			]
		)
		->get_template_part( 'message' );
	return;
}

$user_id = get_current_user_id();

$has_active_packages = false;

foreach ( $data->user_packages as $active_user_package ) {
	if ( $active_user_package->is_active() ) {
		$has_active_packages = true;
		break;
	}
}

posterno()->templates
		->set_template_data(
			[
				'type'    => 'info',
				'message' => 'The free package on this demo can be selected unlimited times. Administrators can however limit the purchase to one time only.',
			]
		)
		->get_template_part( 'message' );

?>

<div id="pno-listing-package-selection">

	<?php if ( ! empty( $data->user_packages ) && $has_active_packages ) : ?>

		<h2><?php esc_html_e( 'Use existing package', 'posterno-wc-paid-listings' ); ?></h2>

		<?php
		foreach ( $data->user_packages as $user_package ) :

			if ( ! $user_package->is_active() ) {
				continue;
			}

		?>
		<div class="card mb-4 shadow-sm">
			<div class="row no-gutters">
				<div class="col-md-8">
					<div class="card-body">
						<h5 class="card-title"><?php echo esc_html( $user_package->package->get_name() ); ?></h5>
						<p class="card-text">
							<span class="badge badge-info p-2"><?php echo esc_html( $user_package->get_current_formatted_labels_package_count() ); ?></span>
						</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="card-body">
						<form action="<?php echo esc_url( $data->action ); ?>" method="get" enctype="multipart/form-data" class="mb-0">
							<input type="hidden" name="package_id" value="<?php echo absint( $user_package->package->get_id() ); ?>">
							<input type="hidden" name="user_package" value="<?php echo absint( $user_package->get_user_package_id() ); ?>">
							<input type="hidden" name="submission_step" value="<?php echo esc_attr( $data->step ); ?>" />
							<?php pno_do_listing_form_submission_step_keys(); ?>
							<button type="submit" class="btn btn-small btn-block btn-outline-primary"><?php echo esc_html( 'Use package' ); ?> &rarr;</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php endforeach; ?>

	<?php endif; ?>

	<?php if ( ! empty( $data->user_packages ) && $has_active_packages ) : ?>
	<h2><?php esc_html_e( 'Buy new package', 'posterno-wc-paid-listings' ); ?></h2>
	<?php endif; ?>

	<div class="card-deck mb-3 text-center">
		<?php
		foreach ( $data->packages as $package ) :

			if ( ! $package->can_purchase_multiple() && pno_paid_listings_user_has_purchased_package( $user_id, $package->get_id() ) ) {
				continue;
			}

			if ( pno_wc_subscriptions_enabled() && wcs_user_has_subscription( $user_id, $package->get_product()->get_id(), 'active' ) ) {
				continue;
			}

			?>
			<div class="card mb-4 shadow-sm">
				<div class="card-header">
					<h4 class="my-0 font-weight-normal"><?php echo esc_html( $package->get_name() ); ?></h4>
				</div>
				<div class="card-body">

					<?php if ( ! empty( $package->get_product()->get_price_html() ) ) : ?>
						<h1 class="card-title pricing-card-title"><?php echo wp_kses_post( $package->get_product()->get_price_html() ); ?></h1>
					<?php else : ?>
						<h1 class="card-title pricing-card-title"><span><?php esc_html_e( 'Free', 'posterno-wc-paid-listings' ); ?></span></h1>
					<?php endif; ?>

					<?php if ( ! empty( $package->get_product()->get_description() ) ) : ?>
						<div class="package-description">
							<?php echo wp_kses_post( apply_filters( 'the_content', $package->get_product()->get_description() ) ); ?>
						</div>
					<?php endif; ?>

					<form action="<?php echo esc_url( $data->action ); ?>" method="get" enctype="multipart/form-data" class="mb-0">
						<input type="hidden" name="package_id" value="<?php echo absint( $package->get_id() ); ?>">
						<input type="hidden" name="submission_step" value="<?php echo esc_attr( $data->step ); ?>" />
						<?php pno_do_listing_form_submission_step_keys(); ?>

						<?php if ( ! empty( $package->get_product()->get_price_html() ) ) : ?>
							<button type="submit" class="btn btn-lg btn-block btn-primary"><?php echo esc_html( 'Purchase package' ); ?> &rarr;</button>
						<?php else : ?>
							<button type="submit" class="btn btn-lg btn-block btn-primary"><?php echo esc_html( 'Get started' ); ?> &rarr;</button>
						<?php endif; ?>
					</form>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
