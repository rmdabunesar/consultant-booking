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
	$home_url = esc_url( home_url( '/' ) );

	ob_start();
	?>
	<div class="cb-result cb-result--cancel" style="max-width:620px;margin:60px auto;padding:44px 32px;text-align:center;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 12px 34px rgba(0,0,0,.07);font-family:Arial,Helvetica,sans-serif;">
		<div style="width:88px;height:88px;margin:0 auto 22px;border-radius:50%;background:#fdecec;display:flex;align-items:center;justify-content:center;">
			<span style="font-size:46px;line-height:1;color:#e74c3c;">&#10005;</span>
		</div>
		<h2 style="margin:0 0 12px;color:#1f2937;font-size:27px;"><?php esc_html_e( 'Payment Cancelled', 'consultant-booking' ); ?></h2>
		<p style="margin:0 0 8px;color:#4b5563;font-size:16px;line-height:1.6;"><?php esc_html_e( 'Your payment was cancelled and no charge was made.', 'consultant-booking' ); ?></p>
		<p style="margin:0 0 30px;color:#6b7280;font-size:15px;line-height:1.6;"><?php esc_html_e( 'You can try again or choose a different payment method.', 'consultant-booking' ); ?></p>
		<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
			<a href="javascript:history.back();" style="display:inline-block;padding:12px 24px;background:#e74c3c;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:bold;"><?php esc_html_e( 'Try Again', 'consultant-booking' ); ?></a>
			<a href="<?php echo esc_url( $home_url ); ?>" style="display:inline-block;padding:12px 24px;background:#f3f4f6;color:#374151;text-decoration:none;border-radius:8px;font-size:15px;font-weight:bold;"><?php esc_html_e( 'Back to Home', 'consultant-booking' ); ?></a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
