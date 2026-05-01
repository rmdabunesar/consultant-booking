<?php
/**
 * Payment Cancel Handler
 *
 * Registers the [cb_booking_cancel] shortcode displayed when a Stripe
 * Checkout session is cancelled by the user.
 *
 * @package Ahn\ConsultantBooking\Payments
 */

namespace Ahn\ConsultantBooking\Payments;

defined( 'ABSPATH' ) || exit;

/**
 * Register cancel shortcode on load.
 */
add_shortcode( 'cb_booking_cancel', 'cb_booking_cancel_shortcode' );

/**
 * Render the booking cancellation message.
 *
 * @return string HTML output for the shortcode.
 */
function cb_booking_cancel_shortcode() {
	return '
		<div style="max-width:600px;margin:60px auto;text-align:center;font-family:Arial">
			<h2 style="color:#e74c3c;">' . esc_html__( 'Payment Cancelled', 'consultant-booking' ) . '</h2>
			<p>' . esc_html__( 'Your payment was cancelled. Please try again or choose a different payment method.', 'consultant-booking' ) . '</p>
		</div>
	';
}
