<?php
/**
 * Consultant Model
 *
 * Registers the cb_consultant custom post type and the admin meta box used to
 * manage consultant-specific fields (designation, social links, availability).
 *
 * @package Ahn\ConsultantBooking\Models
 */

namespace Ahn\ConsultantBooking\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Consultant
 */
class Consultant {

	/**
	 * Constructor – registers CPT and meta box hooks.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'cb_register_post_type_consultant' ) );
		add_action( 'add_meta_boxes', array( self::class, 'cb_add_consultant_meta_boxes' ) );
	}

	/**
	 * Register the cb_consultant custom post type.
	 */
	public function cb_register_post_type_consultant() {
		$labels = array(
			'name'               => __( 'Consultants', 'consultant-booking' ),
			'singular_name'      => __( 'Consultant', 'consultant-booking' ),
			'add_new'            => __( 'Add New', 'consultant-booking' ),
			'add_new_item'       => __( 'Add New Consultant', 'consultant-booking' ),
			'edit_item'          => __( 'Edit Consultant', 'consultant-booking' ),
			'new_item'           => __( 'New Consultant', 'consultant-booking' ),
			'view_item'          => __( 'View Consultant', 'consultant-booking' ),
			'view_items'         => __( 'View Consultants', 'consultant-booking' ),
			'search_items'       => __( 'Search Consultants', 'consultant-booking' ),
			'not_found'          => __( 'No consultants found', 'consultant-booking' ),
			'not_found_in_trash' => __( 'No consultants found in Trash', 'consultant-booking' ),
			'all_items'          => __( 'All Consultants', 'consultant-booking' ),
			'archives'           => __( 'Consultant Archives', 'consultant-booking' ),
		);

		$args = array(
			'labels'          => $labels,
			'public'          => true,
			'show_ui'         => true,
			'show_in_menu'    => false,
			'show_in_rest'    => true,
			'rest_base'       => 'consultants',
			'supports'        => array( 'title', 'editor', 'thumbnail' ),
			'has_archive'     => true,
			'rewrite'         => array( 'slug' => 'consultants' ),
			'capability_type' => 'post',
		);

		register_post_type( 'cb_consultant', $args );
	}

	/**
	 * Register the Consultant Information meta box on the consultant edit screen.
	 */
	public static function cb_add_consultant_meta_boxes() {
		add_meta_box(
			'consultant_info',
			__( 'Consultant Information', 'consultant-booking' ),
			array( self::class, 'cb_render_consultant_meta_box' ),
			'cb_consultant',
			'normal',
			'default'
		);
	}

	/**
	 * Render the Consultant Information meta box.
	 *
	 * Outputs fields for designation, contact info, social media links
	 * (repeater), and weekly availability (repeater).
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public static function cb_render_consultant_meta_box( $post ) {
		wp_nonce_field( 'save_consultant_meta', 'consultant_meta_nonce' );

		$socials = get_post_meta( $post->ID, '_consultant_socials', true );
		$socials = is_array( $socials ) ? $socials : array();

		$availability = get_post_meta( $post->ID, '_consultant_availability', true );
		$availability = is_array( $availability ) ? $availability : array();

		$phone       = get_post_meta( $post->ID, '_consultant_phone', true );
		$email       = get_post_meta( $post->ID, '_consultant_email', true );
		$price       = get_post_meta( $post->ID, '_consultant_fee', true );
		$designation = get_post_meta( $post->ID, '_consultant_designation', true );
		?>

		<p>
			<label for="consultant_designation"><?php esc_html_e( 'Designation / Specialty:', 'consultant-booking' ); ?></label><br>
			<input type="text" id="consultant_designation" name="consultant_designation" value="<?php echo esc_attr( $designation ); ?>" class="regular-text" />
		</p>
		<p>
			<label for="consultant_phone"><?php esc_html_e( 'Phone:', 'consultant-booking' ); ?></label><br>
			<input type="text" id="consultant_phone" name="consultant_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text" />
		</p>
		<p>
			<label for="consultant_email"><?php esc_html_e( 'Email:', 'consultant-booking' ); ?></label><br>
			<input type="email" id="consultant_email" name="consultant_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" />
		</p>
		<p>
			<label for="consultant_fee"><?php esc_html_e( 'Visiting Price:', 'consultant-booking' ); ?></label><br>
			<input type="number" id="consultant_fee" name="consultant_fee" value="<?php echo esc_attr( $price ); ?>" class="regular-text" min="0" step="0.01" />
		</p>

		<h4><?php esc_html_e( 'Social Media Links', 'consultant-booking' ); ?></h4>
		<div id="social-repeater">
			<?php foreach ( $socials as $i => $s ) : ?>
			<div class="social-row">
				<?php self::social_dropdown( $s['platform'] ?? '', $s['url'] ?? '', $i ); ?>
			</div>
			<?php endforeach; ?>
		</div>
		<button type="button" id="add-social"><?php esc_html_e( 'Add Social Link', 'consultant-booking' ); ?></button>

		<h4><?php esc_html_e( 'Weekly Availability', 'consultant-booking' ); ?></h4>
		<div id="availability-container">
			<?php foreach ( $availability as $i => $a ) : ?>
				<?php self::availability_row( $a['day'] ?? '', $a['from'] ?? '', $a['to'] ?? '', $i ); ?>
			<?php endforeach; ?>
		</div>
		<button type="button" id="add-availability"><?php esc_html_e( 'Add Availability', 'consultant-booking' ); ?></button>

		<?php
	}

	/**
	 * Render a single social media platform dropdown row.
	 *
	 * @param string $platform Selected Font Awesome class (e.g. 'fa-facebook-f').
	 * @param string $url      Platform profile URL.
	 * @param int    $index    Repeater row index.
	 */
	private static function social_dropdown( $platform = '', $url = '', $index = 0 ) {
		$platforms = array(
			'fa-facebook-f'  => __( 'Facebook', 'consultant-booking' ),
			'fa-linkedin-in' => __( 'LinkedIn', 'consultant-booking' ),
			'fa-instagram'   => __( 'Instagram', 'consultant-booking' ),
			'fa-youtube'     => __( 'YouTube', 'consultant-booking' ),
		);
		?>
		<select name="consultant_socials[<?php echo esc_attr( $index ); ?>][platform]">
			<?php foreach ( $platforms as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $platform, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<input type="url" name="consultant_socials[<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" placeholder="https://..." />
		<button type="button" class="remove-row"><?php esc_html_e( 'Remove', 'consultant-booking' ); ?></button>
		<?php
	}

	/**
	 * Render a single weekly availability row.
	 *
	 * @param string $day   Day of the week (e.g. 'Monday').
	 * @param string $from  Start time in H:i format.
	 * @param string $to    End time in H:i format.
	 * @param int    $index Repeater row index.
	 */
	private static function availability_row( $day = '', $from = '', $to = '', $index = 0 ) {
		$days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
		?>
		<div class="availability-row">
			<label><?php esc_html_e( 'Day:', 'consultant-booking' ); ?></label>
			<select name="consultant_availability[<?php echo esc_attr( $index ); ?>][day]">
				<?php foreach ( $days as $d ) : ?>
					<option value="<?php echo esc_attr( $d ); ?>" <?php selected( $day, $d ); ?>>
						<?php echo esc_html( $d ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<label><?php esc_html_e( 'From:', 'consultant-booking' ); ?></label>
			<input type="time" name="consultant_availability[<?php echo esc_attr( $index ); ?>][from]" value="<?php echo esc_attr( $from ); ?>" />

			<label><?php esc_html_e( 'To:', 'consultant-booking' ); ?></label>
			<input type="time" name="consultant_availability[<?php echo esc_attr( $index ); ?>][to]" value="<?php echo esc_attr( $to ); ?>" />

			<button type="button" class="remove-row"><?php esc_html_e( 'Remove', 'consultant-booking' ); ?></button>
		</div>
		<?php
	}
}
