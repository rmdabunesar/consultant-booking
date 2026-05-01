<?php
/**
 * 2Checkout Payment Processor
 *
 * Handles order creation via the 2Checkout REST API (v6) on the `init` hook.
 * Reads booking data from the saved post meta and sends it to the API.
 *
 * @package Ahn\ConsultantBooking\Payments
 */

namespace Ahn\ConsultantBooking\Payments;

defined( 'ABSPATH' ) || exit;

/**
 * Class TcoProcess
 */
class TcoProcess {

	/**
	 * Constructor – hooks into WordPress init to process pending payments.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'process_payment' ) );
	}

	/**
	 * Process a 2Checkout payment for a booking.
	 *
	 * Reads the payment token from $_POST and booking details from the
	 * matching post meta, then submits the order to the 2Checkout API.
	 * Errors are logged via error_log and the method returns silently.
	 */
	public function process_payment() {
		// Only run when a token is submitted.
		if ( empty( $_POST['token'] ) || empty( $_POST['order_id'] ) ) {
			return;
		}

		$order_id = intval( $_POST['order_id'] );
		$token    = sanitize_text_field( wp_unslash( $_POST['token'] ) );

		// Retrieve 2Checkout settings.
		$merchant_code = get_option( '_cb_merchant_code' );
		$secret_key    = get_option( '_cb_secret_key' );
		$test_mode     = get_option( '_cb_test_mode' );
		$currency      = get_option( '_cb_currency_code', 'USD' );

		// Retrieve booking details from post meta.
		$student_name  = sanitize_text_field( get_post_meta( $order_id, '_student_name', true ) );
		$student_email = sanitize_email( get_post_meta( $order_id, '_student_email', true ) );
		$order_amount  = floatval( get_post_meta( $order_id, '_amount', true ) );

		// Choose sandbox or live endpoint.
		$is_test_mode = ( '1' === $test_mode );
		$endpoint     = $is_test_mode
			? 'https://sandbox.2checkout.com/rest/6.0/orders/'
			: 'https://api.2checkout.com/rest/6.0/orders/';

		// Build HMAC authentication header.
		$date       = gmdate( 'Y-m-d H:i:s' );
		$string     = strlen( $merchant_code ) . $merchant_code . strlen( $date ) . $date;
		$hash       = hash_hmac( 'sha256', $string, $secret_key );

		$headers = array(
			'Content-Type'              => 'application/json',
			'Accept'                    => 'application/json',
			'X-Avangate-Authentication' => sprintf(
				'code="%s" date="%s" hash="%s" algo="sha256"',
				$merchant_code,
				$date,
				$hash
			),
		);

		$payload = array(
			'Currency' => $currency,
			'Customer' => array(
				'Email' => $student_email,
				'Name'  => $student_name,
			),
			'Items'    => array(
				array(
					'Code'        => 'consultant_booking_' . $order_id,
					'Quantity'    => 1,
					'Price'       => array(
						'Amount' => round( $order_amount, 2 ),
						'Type'   => 'CUSTOM',
					),
					'Description' => 'Consultant Booking Service',
					'Tangible'    => false,
					'Recurring'   => false,
				),
			),
			'PaymentDetails' => array(
				'Type'  => $is_test_mode ? 'TEST' : 'EES_TOKEN_PAYMENT',
				'Token' => $token,
			),
			'BillingDetails' => array(
				'Email' => $student_email,
				'Name'  => $student_name,
			),
		);

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => $headers,
				'body'    => wp_json_encode( $payload ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( '2Checkout API Error: ' . $response->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
