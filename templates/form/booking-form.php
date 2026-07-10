<?php
/**
 * Booking Form Template
 *
 * Displays the appointment booking form for a single consultant.
 * Variables are passed in via cb_get_template().
 *
 * @var int    $consultant_id
 * @var string $consultant_name
 * @var float  $consultant_fee
 * @var string $consultant_image
 * @var string $consultant_designation
 *
 * @package ConsultantBooking
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="appointment-form">
	<div class="consultant-info group-row">
		<div class="consultant-image">
			<?php if ( $consultant_image ) : ?>
				<img src="<?php echo esc_url( $consultant_image ); ?>" alt="<?php echo esc_attr( $consultant_name ); ?>">
			<?php endif; ?>
		</div>
		<div class="consultant-details">
			<h2><?php echo esc_html( $consultant_name ); ?></h2>
			<p class="designation"><?php echo esc_html( $consultant_designation ); ?></p>
			<span><?php esc_html_e( 'Consult Fee:', 'consultant-booking' ); ?></span>
			<span class="price">&#2547;<?php echo esc_html( number_format( (float) $consultant_fee, 2 ) ); ?></span>
		</div>
	</div>

	<?php do_action( 'cb_before_form' ); ?>

	<form class="cb-booking-form" method="post">
		<?php wp_nonce_field( 'cb_submit_booking', 'cb_booking_nonce' ); ?>

		<div class="group-row">
			<div class="column">
				<label for="student_name"><?php esc_html_e( 'Your Name', 'consultant-booking' ); ?></label>
				<input type="text" id="student_name" name="student_name" required>
			</div>
			<div class="column">
				<label for="student_email"><?php esc_html_e( 'Your Email', 'consultant-booking' ); ?></label>
				<input type="email" id="student_email" name="student_email" required>
			</div>
		</div>

		<div class="group-row">
			<div class="column">
				<label for="student_phone"><?php esc_html_e( 'Your Phone', 'consultant-booking' ); ?></label>
				<input type="tel" id="student_phone" name="student_phone" required>
			</div>
			<div class="column">
				<label for="appointment_datetime"><?php esc_html_e( 'Preferred Date & Time', 'consultant-booking' ); ?></label>
				<input type="datetime-local" id="appointment_datetime" name="appointment_datetime" required>
			</div>
		</div>

		<label for="notes"><?php esc_html_e( 'Information', 'consultant-booking' ); ?></label>
		<textarea id="notes" name="notes" rows="3" placeholder="<?php esc_attr_e( 'Please provide your academic result, IELTS score, desired country', 'consultant-booking' ); ?>"></textarea>

		<label for="payment_method"><?php esc_html_e( 'Payment Method', 'consultant-booking' ); ?></label>
		<select id="payment_method" name="payment_method" required>
			<option value=""><?php esc_html_e( 'Select a payment method', 'consultant-booking' ); ?></option>
			<option value="cash"><?php esc_html_e( 'Cash on visit', 'consultant-booking' ); ?></option>
			<option value="online"><?php esc_html_e( 'Online Payment', 'consultant-booking' ); ?></option>
		</select>

		<input type="hidden" name="consultant_id" value="<?php echo esc_attr( $consultant_id ); ?>">
		<input type="hidden" name="amount" value="<?php echo esc_attr( $consultant_fee ); ?>">

		<br>
		<button type="submit" name="submit_booking"><?php esc_html_e( 'Book Appointment', 'consultant-booking' ); ?></button>
	</form>

	<?php do_action( 'cb_after_form' ); ?>
</div>
