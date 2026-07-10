<?php
/**
 * Booking Payment Success Handler
 *
 * Registers the [cb_booking_success] shortcode shown after a completed booking.
 * Handles both cash bookings (identified by booking_id) and online/Stripe
 * bookings (verified via session_id), and offers the invoice PDF for download.
 *
 * @package Ahn\ConsultantBooking\Payments
 */

namespace Ahn\ConsultantBooking\Payments;

defined( 'ABSPATH' ) || exit;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Ahn\ConsultantBooking\Controllers\Booking;

/**
 * Register success shortcode on load.
 */
add_shortcode( 'cb_booking_success', 'cb_booking_success_shortcode' );

/**
 * Render the booking payment success message with an invoice download link.
 *
 * @return string HTML output for the shortcode.
 */
function cb_booking_success_shortcode() {
	$booking_id  = isset( $_GET['booking_id'] ) ? absint( wp_unslash( $_GET['booking_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$has_session = ! empty( $_GET['session_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$amount_paid = '';

	// Verify online/Stripe payments against the Checkout session.
	if ( $has_session ) {
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

		if ( isset( $session->amount_total ) ) {
			$amount_paid = number_format( $session->amount_total / 100, 2 ) . ' ' . strtoupper( $session->currency );
		}

		// Mark paid and send the invoice email once for confirmed online bookings.
		if ( $booking_id && 'cb_booking' === get_post_type( $booking_id ) ) {
			if ( 'paid' !== get_post_meta( $booking_id, '_payment_status', true ) ) {
				update_post_meta( $booking_id, '_payment_status', 'paid' );
			}

			if ( ! get_post_meta( $booking_id, '_cb_invoice_sent', true ) ) {
				Booking::cb_send_booking_email( $booking_id );
				update_post_meta( $booking_id, '_cb_invoice_sent', 1 );
			}
		}
	}

	// A valid booking reference is required to show the invoice download.
	if ( ! $booking_id || 'cb_booking' !== get_post_type( $booking_id ) ) {
		return '<p>' . esc_html__( 'Invalid booking reference.', 'consultant-booking' ) . '</p>';
	}

	$booking_number = get_post_meta( $booking_id, '_booking_order_number', true );

	// Resolve the invoice PDF, generating it if it is not already on disk.
	$upload_dir = wp_upload_dir();
	$filename   = 'invoice_booking_' . sanitize_file_name( $booking_number ) . '.pdf';
	$pdf_path   = $upload_dir['basedir'] . '/cb-invoice/' . $filename;
	$pdf_url    = $upload_dir['baseurl'] . '/cb-invoice/' . $filename;

	if ( ! file_exists( $pdf_path ) ) {
		Booking::cb_generate_invoice_pdf( $booking_id );
	}

	// Fall back to the stored booking amount when no Stripe amount is present.
	if ( '' === $amount_paid ) {
		$amount = get_post_meta( $booking_id, '_amount', true );
		if ( '' !== $amount && null !== $amount ) {
			$amount_paid = number_format( (float) $amount, 2 ) . ' ' . strtoupper( get_option( '_cb_currency_code', 'USD' ) );
		}
	}

	$home_url = esc_url( home_url( '/' ) );

	ob_start();
	?>
	<div class="cb-result cb-result--success" style="max-width:620px;margin:60px auto;padding:44px 32px;text-align:center;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 12px 34px rgba(0,0,0,.07);font-family:Arial,Helvetica,sans-serif;">
		<div style="width:88px;height:88px;margin:0 auto 22px;border-radius:50%;background:#e9f9ef;display:flex;align-items:center;justify-content:center;">
			<span style="font-size:46px;line-height:1;color:#2ecc71;">&#10003;</span>
		</div>
		<h2 style="margin:0 0 12px;color:#1f2937;font-size:27px;"><?php esc_html_e( 'Payment Successful!', 'consultant-booking' ); ?></h2>
		<p style="margin:0 0 8px;color:#4b5563;font-size:16px;line-height:1.6;"><?php esc_html_e( 'Thank you — your booking payment has been received.', 'consultant-booking' ); ?></p>
		<?php if ( $amount_paid ) : ?>
			<p style="margin:0 0 8px;color:#1f2937;font-size:18px;font-weight:bold;">
				<?php echo esc_html( sprintf( /* translators: %s: amount paid with currency. */ __( 'Amount Paid: %s', 'consultant-booking' ), $amount_paid ) ); ?>
			</p>
		<?php endif; ?>
		<p style="margin:0 0 30px;color:#6b7280;font-size:15px;line-height:1.6;"><?php esc_html_e( 'A confirmation has been sent to your email address.', 'consultant-booking' ); ?></p>
		<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
			<a href="<?php echo esc_url( $pdf_url ); ?>" download style="display:inline-block;padding:12px 24px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:bold;"><?php esc_html_e( 'Download Invoice (PDF)', 'consultant-booking' ); ?></a>
			<a href="<?php echo esc_url( $home_url ); ?>" style="display:inline-block;padding:12px 24px;background:#2ecc71;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:bold;"><?php esc_html_e( 'Back to Home', 'consultant-booking' ); ?></a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
