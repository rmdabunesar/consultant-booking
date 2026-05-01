<?php
/**
 * Booking Model
 *
 * Registers the cb_booking custom post type and the admin meta box used to
 * display booking details.
 *
 * @package Ahn\ConsultantBooking\Models
 */

namespace Ahn\ConsultantBooking\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Booking
 */
class Booking {

	/**
	 * Constructor – registers CPT and meta box hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'cb_register_post_type_booking' ), 0 );
		add_action( 'add_meta_boxes', array( self::class, 'cb_add_booking_meta_boxes' ) );
	}

	/**
	 * Register the cb_booking custom post type.
	 */
	public function cb_register_post_type_booking() {
		$labels = array(
			'name'               => __( 'Bookings', 'consultant-booking' ),
			'singular_name'      => __( 'Booking', 'consultant-booking' ),
			'add_new'            => __( 'Add New', 'consultant-booking' ),
			'add_new_item'       => __( 'Add New Booking', 'consultant-booking' ),
			'edit_item'          => __( 'Edit Booking', 'consultant-booking' ),
			'new_item'           => __( 'New Booking', 'consultant-booking' ),
			'view_item'          => __( 'View Booking', 'consultant-booking' ),
			'view_items'         => __( 'View Bookings', 'consultant-booking' ),
			'search_items'       => __( 'Search Bookings', 'consultant-booking' ),
			'not_found'          => __( 'No bookings found', 'consultant-booking' ),
			'not_found_in_trash' => __( 'No bookings found in Trash', 'consultant-booking' ),
			'all_items'          => __( 'All Bookings', 'consultant-booking' ),
			'archives'           => __( 'Booking Archives', 'consultant-booking' ),
		);

		$args = array(
			'labels'          => $labels,
			'public'          => true,
			'show_ui'         => true,
			'show_in_menu'    => false,
			'show_in_rest'    => true,
			'rest_base'       => 'bookings',
			'supports'        => array( 'title' ),
			'has_archive'     => true,
			'rewrite'         => array( 'slug' => 'bookings' ),
			'capability_type' => 'post',
		);

		register_post_type( 'cb_booking', $args );
	}

	/**
	 * Register the Booking Information meta box on the booking edit screen.
	 */
	public static function cb_add_booking_meta_boxes() {
		add_meta_box(
			'booking_info',
			__( 'Booking Information', 'consultant-booking' ),
			array( self::class, 'cb_render_booking_meta_box' ),
			'cb_booking',
			'normal',
			'default'
		);
	}

	/**
	 * Render the read-only Booking Information meta box.
	 *
	 * Booking details are created programmatically and are not intended to be
	 * edited directly from the admin; fields are displayed as read-only inputs.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function cb_render_booking_meta_box( $post ) {
		$consultant_name = get_post_meta( $post->ID, '_consultant_name', true );
		$student_name    = get_post_meta( $post->ID, '_student_name', true );
		$student_email   = get_post_meta( $post->ID, '_student_email', true );
		$student_phone   = get_post_meta( $post->ID, '_student_phone', true );
		$datetime        = get_post_meta( $post->ID, '_appointment_datetime', true );
		$note            = get_post_meta( $post->ID, '_notes', true );
		$payment_method  = get_post_meta( $post->ID, '_payment_method', true );
		?>
		<table class="form-table">
			<tr>
				<th><label for="consultant_name"><?php esc_html_e( 'Consultant Name', 'consultant-booking' ); ?></label></th>
				<td><input type="text" id="consultant_name" value="<?php echo esc_attr( $consultant_name ); ?>" class="regular-text" readonly /></td>
			</tr>
			<tr>
				<th><label for="student_name"><?php esc_html_e( 'Student Name', 'consultant-booking' ); ?></label></th>
				<td><input type="text" id="student_name" name="student_name" value="<?php echo esc_attr( $student_name ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="student_email"><?php esc_html_e( 'Student Email', 'consultant-booking' ); ?></label></th>
				<td><input type="email" id="student_email" name="student_email" value="<?php echo esc_attr( $student_email ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="student_phone"><?php esc_html_e( 'Student Phone', 'consultant-booking' ); ?></label></th>
				<td><input type="tel" id="student_phone" name="student_phone" value="<?php echo esc_attr( $student_phone ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="appointment_datetime"><?php esc_html_e( 'Booking Time', 'consultant-booking' ); ?></label></th>
				<td><input type="datetime-local" id="appointment_datetime" name="appointment_datetime" value="<?php echo esc_attr( date( 'Y-m-d\TH:i', strtotime( $datetime ) ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th><label for="note"><?php esc_html_e( 'Booking Note', 'consultant-booking' ); ?></label></th>
				<td><textarea id="note" name="note" class="regular-text"><?php echo esc_textarea( $note ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="payment_method"><?php esc_html_e( 'Payment Method', 'consultant-booking' ); ?></label></th>
				<td><input type="text" id="payment_method" value="<?php echo esc_attr( ucfirst( $payment_method ) ); ?>" class="regular-text" readonly /></td>
			</tr>
		</table>
		<?php
	}
}
