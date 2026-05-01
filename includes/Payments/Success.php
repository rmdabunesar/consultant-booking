<?php
/**
 * Stripe Payment Success Handler
 *
 * Registers the [cb_booking_success] shortcode that is displayed on the
 * payment success page after a completed Stripe Checkout session.
 *
 * @package Ahn\ConsultantBooking\Payments
 */

namespace Ahn\ConsultantBooking\Payments;

defined( 'ABSPATH' ) || exit;

use Stripe\Stripe;
use Stripe\Checkout\Session;

/**
 * Register success shortcode on load.
 */
add_shortcode( 'cb_booking_success', 'cb_booking_success_shortcode' );

/**
 * Render the booking payment success message.
 *
 * Retrieves the Stripe session by ID, confirms payment status, then outputs
 * a confirmation message. Returns an error string on any failure so the
 * shortcode degrades gracefully.
 *
 * @return string HTML output for the shortcode.
 */
function cb_booking_success_shortcode() {
	if ( empty( $_GET['session_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '<p>' . esc_html__( 'Invalid payment session.', 'consultant-booking' ) . '</p>';
	}

	$session_id = sanitize_text_field( wp_unslash( $_GET['session_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$secret_key = get_option( '_cb_secret_key' );

	if ( ! $secret_key ) {
		return '<p>' . esc_html__( 'Stripe is not configured.', 'consultant-booking' ) . '</p>';
	}

	Stripe::setApiKey( $secret_key );

	try {
		$session = Session::retrieve( $session_id );

		if ( 'paid' !== $session->payment_status ) {
			return '<p>' . esc_html__( 'Payment not completed.', 'consultant-booking' ) . '</p>';
		}
	} catch ( \Exception $e ) {
		return '<p>' . esc_html__( 'Payment verification failed.', 'consultant-booking' ) . '</p>';
	}

	return '
		<div style="max-width:600px;margin:60px auto;text-align:center;font-family:Arial">
			<h2 style="color:#2ecc71;">' . esc_html__( 'Thank you!', 'consultant-booking' ) . '</h2>
			<p>' . esc_html__( 'Your booking payment was successful.', 'consultant-booking' ) . '</p>
		</div>
	';
}
