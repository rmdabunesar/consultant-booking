<?php
/**
 * Booking Controller
 *
 * Handles booking shortcode rendering, form submission, admin columns,
 * and PDF invoice email delivery.
 *
 * @package Ahn\ConsultantBooking\Controllers
 */

namespace Ahn\ConsultantBooking\Controllers;

defined( 'ABSPATH' ) || exit;

use Ahn\ConsultantBooking\Payments\Stripe;
use Dompdf\Dompdf;

/**
 * Class Booking
 */
class Booking {

	/**
	 * Constructor – registers all hooks for the booking post type and shortcode.
	 */
	public function __construct() {
		add_filter( 'manage_cb_booking_posts_columns', array( self::class, 'cb_booking_custom_columns' ) );
		add_action( 'manage_cb_booking_posts_custom_column', array( self::class, 'cb_booking_custom_column_content' ), 10, 2 );
		add_shortcode( 'cb_booking_form', array( self::class, 'cb_booking_form_shortcode' ) );

		// Must run before any output so the post-submission redirects can send headers.
		add_action( 'template_redirect', array( self::class, 'cb_handle_booking_submission' ) );
	}

	/**
	 * Define custom columns for the Booking list table in the admin.
	 *
	 * @param array $columns Default columns.
	 * @return array Modified columns.
	 */
	public static function cb_booking_custom_columns( $columns ) {
		return array(
			'cb'                   => $columns['cb'],
			'title'                => __( 'Booking Title', 'consultant-booking' ),
			'consultant_name'      => __( 'Consultant', 'consultant-booking' ),
			'student_name'         => __( 'Student', 'consultant-booking' ),
			'appointment_datetime' => __( 'Date & Time', 'consultant-booking' ),
			'payment_method'       => __( 'Payment', 'consultant-booking' ),
		);
	}

	/**
	 * Populate the content for each custom booking column.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Current post ID.
	 */
	public static function cb_booking_custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'consultant_name':
				echo esc_html( get_post_meta( $post_id, '_consultant_name', true ) );
				break;

			case 'student_name':
				echo esc_html( get_post_meta( $post_id, '_student_name', true ) );
				break;

			case 'appointment_datetime':
				$datetime = get_post_meta( $post_id, '_appointment_datetime', true );
				// translators: PHP date format for booking list table.
				echo esc_html( date( 'd M Y, h:i A', strtotime( $datetime ) ) );
				break;

			case 'payment_method':
				$method = get_post_meta( $post_id, '_payment_method', true );
				echo esc_html( ucfirst( $method ) );
				break;
		}
	}

	/**
	 * Shortcode handler for [cb_booking_form].
	 *
	 * Renders the booking form for the consultant specified via the
	 * `consultant_id` query parameter.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Rendered HTML output.
	 */
	public static function cb_booking_form_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'consultant_id' => 0,
			),
			$atts,
			'cb_booking_form'
		);

		// Prefer URL parameter over shortcode attribute.
		$consultant_id = isset( $_GET['consultant_id'] ) ? intval( $_GET['consultant_id'] ) : intval( $atts['consultant_id'] ); 

		ob_start();

		$consultant_name        = get_the_title( $consultant_id );
		$consultant_fee         = get_post_meta( $consultant_id, '_consultant_fee', true );
		$consultant_image       = get_the_post_thumbnail_url( $consultant_id, 'medium' );
		$consultant_designation = get_post_meta( $consultant_id, '_consultant_designation', true );

		cb_get_template(
			'form/booking-form',
			array(
				'consultant_id'          => $consultant_id,
				'consultant_name'        => $consultant_name,
				'consultant_fee'         => $consultant_fee,
				'consultant_image'       => $consultant_image,
				'consultant_designation' => $consultant_designation,
			)
		);

		return ob_get_clean();
	}

	/**
	 * Process a submitted booking form.
	 *
	 * Validates availability, prevents overlapping bookings, routes to the
	 * selected payment method, and — for cash bookings — saves the booking
	 * post and sends the invoice email.
	 */
	public static function cb_handle_booking_submission() {
		if ( ! isset( $_POST['submit_booking'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['cb_booking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cb_booking_nonce'] ) ), 'cb_submit_booking' ) ) {
			wp_die( esc_html__( 'Security check failed. Please refresh the page and try again.', 'consultant-booking' ) );
		}

		$consultant_id   = intval( $_POST['consultant_id'] );
		$consultant_name = get_the_title( $consultant_id );

		$student_name  = sanitize_text_field( wp_unslash( $_POST['student_name'] ?? '' ) );
		$student_email = sanitize_email( wp_unslash( $_POST['student_email'] ?? '' ) );
		$student_phone = sanitize_text_field( wp_unslash( $_POST['student_phone'] ?? '' ) );
		$datetime      = sanitize_text_field( wp_unslash( $_POST['appointment_datetime'] ?? '' ) );
		$notes         = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );
		$payment       = sanitize_text_field( wp_unslash( $_POST['payment_method'] ?? '' ) );
		$amount        = floatval( $_POST['amount'] ?? 0 );

		// Validate consultant availability for the requested slot.
		$consultant_available_times = get_post_meta( $consultant_id, '_consultant_availability', true );
		$is_available               = false;

		if ( $consultant_available_times && is_array( $consultant_available_times ) ) {
			$appointment_time = date( 'H:i', strtotime( $datetime ) );
			$appointment_day  = date( 'l', strtotime( $datetime ) );

			foreach ( $consultant_available_times as $time_slot ) {
				if (
					$time_slot['day'] === $appointment_day &&
					$appointment_time >= $time_slot['from'] &&
					$appointment_time < $time_slot['to']
				) {
					$is_available = true;
					break;
				}
			}
		}

		if ( ! $is_available ) {
			// Use wp_die for clean error output rather than inline <script> alerts.
			wp_die(
				esc_html__( 'Consultant is not available on the selected date. Please choose another time.', 'consultant-booking' ),
				esc_html__( 'Booking Error', 'consultant-booking' ),
				array( 'back_link' => true )
			);
		}

		// Prevent overlapping bookings for the same consultant.
		$slot_duration_minutes = intval( get_option( '_cb_default_slot_duration', 30 ) );
		$appointment_start     = strtotime( $datetime );
		$appointment_end       = $appointment_start + ( $slot_duration_minutes * 60 );

		$start_bound = date( 'Y-m-d\TH:i', $appointment_start - ( $slot_duration_minutes * 60 ) );
		$end_bound   = date( 'Y-m-d\TH:i', $appointment_end );

		$existing_bookings = new \WP_Query(
			array(
				'post_type'   => 'cb_booking',
				'post_status' => 'publish',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => '_consultant_id',
						'value'   => $consultant_id,
						'compare' => '=',
					),
					array(
						'key'     => '_appointment_datetime',
						'value'   => array( $start_bound, $end_bound ),
						'compare' => 'BETWEEN',
						'type'    => 'CHAR',
					),
				),
			)
		);

		if ( $existing_bookings->have_posts() ) {
			wp_reset_postdata();
			wp_die(
				esc_html__( 'This time slot is already booked. Please choose another time.', 'consultant-booking' ),
				esc_html__( 'Booking Error', 'consultant-booking' ),
				array( 'back_link' => true )
			);
		}

		wp_reset_postdata();

		// Persist the booking for both cash and online payments so every
		// booking has a record and an invoice reference number.
		$payment_status = ( 'online' === $payment ) ? 'pending' : 'paid';

		$booking_id = self::cb_save_booking(
			array(
				'_consultant_id'        => $consultant_id,
				'_consultant_name'      => $consultant_name,
				'_student_name'         => $student_name,
				'_student_email'        => $student_email,
				'_student_phone'        => $student_phone,
				'_appointment_datetime' => $datetime,
				'_notes'                => $notes,
				'_payment_method'       => $payment,
				'_amount'               => $amount,
				'_payment_status'       => $payment_status,
			),
			$student_name
		);

		if ( is_wp_error( $booking_id ) || ! $booking_id ) {
			wp_die(
				esc_html__( 'Booking could not be saved. Please try again.', 'consultant-booking' ),
				esc_html__( 'Booking Error', 'consultant-booking' ),
				array( 'back_link' => true )
			);
		}

		$booking_number = get_post_meta( $booking_id, '_booking_order_number', true );

		// Route to Stripe for online payments.
		if ( 'online' === $payment ) {
			$stripe = new Stripe(
				get_option( '_cb_secret_key' ),
				get_option( '_cb_publishable_key' ),
				strtolower( get_option( '_cb_currency_code', 'usd' ) ),
				$amount * 100, // Stripe expects amount in cents.
				'Booking #' . $booking_number . ' (' . $consultant_name . ')',
				$booking_id
			);

			$session = $stripe->create_checkout_session();
			wp_redirect( esc_url_raw( $session->url ) );
			exit;
		}

		// Cash: generate + email the invoice, then redirect to the success page.
		self::cb_send_booking_email( $booking_id );

		$success_page_id = get_option( '_cb_success_page_id' );
		$success_page    = $success_page_id ? get_post( $success_page_id ) : null;
		$success_base    = $success_page ? get_permalink( $success_page ) : get_permalink();

		$redirect_url = add_query_arg(
			array(
				'booking'    => 'success',
				'booking_id' => $booking_id,
			),
			$success_base
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Persist a cb_booking post and reserve its invoice order number.
	 *
	 * @param array  $meta         Post meta to store on the booking.
	 * @param string $student_name Student name used for the human-readable title.
	 * @return int|\WP_Error The new booking post ID, or a WP_Error on failure.
	 */
	public static function cb_save_booking( array $meta, string $student_name ) {
		// Reserve the next booking order number.
		$last_number = intval( get_option( '_cb_last_booking_number', 1000 ) );
		$new_number  = $last_number + 1;

		$booking_id = wp_insert_post(
			array(
				'post_title'  => '',
				'post_type'   => 'cb_booking',
				'post_status' => 'publish',
				'meta_input'  => $meta,
			)
		);

		if ( is_wp_error( $booking_id ) || ! $booking_id ) {
			return $booking_id;
		}

		update_post_meta( $booking_id, '_booking_order_number', $new_number );
		update_option( '_cb_last_booking_number', $new_number );

		// Update the post title to a human-readable format.
		wp_update_post(
			array(
				'ID'         => $booking_id,
				'post_title' => 'Booking #' . $new_number . ' (' . $student_name . ')',
			)
		);

		return $booking_id;
	}

	/**
	 * Generate a PDF invoice for a booking and store it in the uploads folder.
	 *
	 * The file is written to uploads/cb-invoice/invoice_booking_{number}.pdf and
	 * kept on disk so it can be attached to emails and offered as a download on
	 * the success page. Returns the absolute file path.
	 *
	 * @param int $booking_id Booking post ID.
	 * @return string Absolute path to the generated PDF file.
	 */
	public static function cb_generate_invoice_pdf( $booking_id ): string {
		$student_name         = get_post_meta( $booking_id, '_student_name', true );
		$consultant_name      = get_post_meta( $booking_id, '_consultant_name', true );
		$appointment_datetime = get_post_meta( $booking_id, '_appointment_datetime', true );
		$consultant_fee       = get_post_meta( $booking_id, '_amount', true );
		$booking_number       = get_post_meta( $booking_id, '_booking_order_number', true );

		// Render invoice HTML via template.
		$dompdf = new Dompdf();
		ob_start();
		cb_get_template(
			'invoice',
			array(
				'student_name'         => $student_name,
				'consultant_name'      => $consultant_name,
				'appointment_datetime' => $appointment_datetime,
				'consultant_fee'       => $consultant_fee,
				'booking_number'       => $booking_number,
			)
		);
		$html = ob_get_clean();

		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();
		$pdf_output = $dompdf->output();

		// Write PDF to a dedicated subfolder inside the uploads directory.
		$upload_dir    = wp_upload_dir();
		$custom_folder = $upload_dir['basedir'] . '/cb-invoice';

		if ( ! file_exists( $custom_folder ) ) {
			wp_mkdir_p( $custom_folder );
		}

		$filename = 'invoice_booking_' . sanitize_file_name( $booking_number ) . '.pdf';
		$pdf_path = $custom_folder . '/' . $filename;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $pdf_path, $pdf_output );

		return $pdf_path;
	}

	/**
	 * Generate the invoice PDF and email it to the student as an attachment.
	 *
	 * The PDF is kept on disk after sending so it remains available for download
	 * from the booking success page.
	 *
	 * @param int $booking_id Booking post ID.
	 */
	public static function cb_send_booking_email( $booking_id ) {
		$student_email  = get_post_meta( $booking_id, '_student_email', true );
		$student_name   = get_post_meta( $booking_id, '_student_name', true );
		$booking_number = get_post_meta( $booking_id, '_booking_order_number', true );

		// Generate (and persist) the invoice PDF.
		$pdf_path = self::cb_generate_invoice_pdf( $booking_id );

		$subject     = sprintf(
			/* translators: %s: booking reference number */
			__( 'Your Booking Invoice - Booking #%s', 'consultant-booking' ),
			$booking_number
		);
		$message     = sprintf(
			/* translators: %s: student name */
			__( "Dear %s,\n\nThank you for your booking. Please find your invoice attached.\n\nBest regards,\nConsultant Booking Team", 'consultant-booking' ),
			$student_name
		);
		$headers     = array( 'Content-Type: text/plain; charset=UTF-8' );
		$attachments = array( $pdf_path );

		wp_mail( $student_email, $subject, $message, $headers, $attachments );
	}
}
