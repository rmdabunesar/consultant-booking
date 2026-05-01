<?php
/**
 * Admin Settings
 *
 * Singleton class that registers the plugin admin menu, enqueues the React
 * settings app, and exposes a REST API endpoint for reading and updating
 * all plugin options.
 *
 * @package Ahn\ConsultantBooking
 */

namespace Ahn\ConsultantBooking;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 */
class Settings {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Return the singleton instance, creating it if necessary.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		return self::$instance ??= new self();
	}

	/**
	 * Register all admin-facing hooks.
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'cb_add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'cb_settings_app_enqueue_assets' ) );
		add_action( 'rest_api_init', array( $this, 'register_settings_api' ) );
	}

	/**
	 * Register the top-level admin menu and its sub-pages.
	 */
	public function cb_add_admin_menu(): void {
		add_menu_page(
			__( 'Appointment', 'consultant-booking' ),
			__( 'Appointment', 'consultant-booking' ),
			'manage_options',
			'cb-booking-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-schedule',
			26
		);

		add_submenu_page(
			'cb-booking-settings',
			__( 'Consultants', 'consultant-booking' ),
			__( 'Consultants', 'consultant-booking' ),
			'edit_posts',
			'edit.php?post_type=cb_consultant'
		);

		add_submenu_page(
			'cb-booking-settings',
			__( 'Booking List', 'consultant-booking' ),
			__( 'Booking List', 'consultant-booking' ),
			'edit_posts',
			'edit.php?post_type=cb_booking'
		);

		add_submenu_page(
			'cb-booking-settings',
			__( 'Settings', 'consultant-booking' ),
			__( 'Settings', 'consultant-booking' ),
			'manage_options',
			'cb-booking-settings',
			array( $this, 'render_settings_page' )
		);

		// Remove the duplicate top-level entry that WordPress auto-adds.
		remove_submenu_page( 'cb-booking-settings', 'cb-booking-settings' );
	}

	/**
	 * Output the settings page shell that hosts the React app.
	 */
	public function render_settings_page(): void {
		$settings_file = CB_PLUGIN_DIR . 'includes/Views/admin/settings.php';

		if ( file_exists( $settings_file ) ) {
			include $settings_file;
		} else {
			echo '<div class="wrap"><h1>' . esc_html__( 'Appointment Settings', 'consultant-booking' ) . '</h1>';
			echo '<p>' . esc_html__( 'Settings page not found.', 'consultant-booking' ) . '</p></div>';
		}
	}

	/**
	 * Enqueue the compiled React settings app on the settings page only.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function cb_settings_app_enqueue_assets( string $hook ): void {
		if ( 'toplevel_page_cb-booking-settings' !== $hook ) {
			return;
		}

		$handle      = 'cb-settings-app';
		$script_path = CB_PLUGIN_DIR . 'assets/admin/build/index.js';
		$script_url  = CB_PLUGIN_URL . 'assets/admin/build/index.js';
		$asset_file  = CB_PLUGIN_DIR . 'assets/admin/build/index.asset.php';

		$asset_data = file_exists( $asset_file )
			? include $asset_file
			: array(
				'dependencies' => array(),
				'version'      => file_exists( $script_path ) ? filemtime( $script_path ) : time(),
			);

		wp_enqueue_script(
			$handle,
			$script_url,
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);

		wp_enqueue_style( 'wp-components' );

		wp_localize_script(
			$handle,
			'CB_DATA',
			array(
				'apiBase' => esc_url_raw( rest_url( 'consultant-booking/v1/settings' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Register the REST API route for plugin settings (GET and POST).
	 */
	public function register_settings_api(): void {
		register_rest_route(
			'consultant-booking/v1',
			'/settings',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'get_settings' ),
				),
				array(
					'methods'             => 'POST',
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'callback'            => array( $this, 'update_settings' ),
					'args'                => $this->get_rest_args(),
				),
			)
		);
	}

	/**
	 * Return the list of setting keys with their default values.
	 *
	 * @return array<string, mixed>
	 */
	private function get_setting_keys(): array {
		return array(
			'booking_enabled'           => false,
			'default_consultant_status' => 'active',
			'default_slot_duration'     => 15,
			'slot_interval_type'        => 'fixed',
			'max_bookings_per_slot'     => 1,
			'admin_email'               => get_option( 'admin_email' ),
			'user_email_subject'        => 'Your appointment is confirmed',
			'notification_sender_name'  => 'Consultant Booking',
			'working_days'              => array(),
			'working_hours_start'       => '09:00',
			'working_hours_end'         => '17:00',
			'currency_code'             => 'USD',
			'currency_position'         => 'left',
			'consultants_per_page'      => 10,
			'booking_page_id'           => 0,
			'success_page_id'           => 0,
			'cancel_page_id'            => 0,
			'secret_key'                => '',
			'publishable_key'           => '',
		);
	}

	/**
	 * Return REST API argument definitions with sanitisation callbacks.
	 *
	 * @return array
	 */
	private function get_rest_args(): array {
		return array(
			'booking_enabled'           => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
			),
			'default_consultant_status' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'default_slot_duration'     => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'slot_interval_type'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'max_bookings_per_slot'     => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'admin_email'               => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
			),
			'user_email_subject'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'notification_sender_name'  => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'working_days'              => array(
				'type'              => 'array',
				'items'             => array( 'type' => 'string' ),
				'sanitize_callback' => array( $this, 'sanitize_working_days' ),
			),
			'working_hours_start'       => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'working_hours_end'         => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'currency_code'             => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'currency_position'         => array(
				'type'              => 'string',
				'enum'              => array( 'left', 'right' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'consultants_per_page'      => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'booking_page_id'           => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'success_page_id'           => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'cancel_page_id'            => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'secret_key'                => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'publishable_key'           => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Sanitize the working_days REST parameter.
	 *
	 * @param mixed $value Raw value from the request.
	 * @return array
	 */
	public function sanitize_working_days( $value ): array {
		return array_map( 'sanitize_text_field', (array) $value );
	}

	/**
	 * REST GET callback – return all plugin settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_settings(): array {
		$keys = $this->get_setting_keys();
		$data = array();

		foreach ( $keys as $key => $default ) {
			$data[ $key ] = get_option( "_cb_{$key}", $default );
		}

		return $data;
	}

	/**
	 * REST POST callback – persist updated settings.
	 *
	 * Only keys present in the request body are updated.
	 *
	 * @param \WP_REST_Request $request Incoming REST request.
	 * @return array<string, mixed> The saved values.
	 */
	public function update_settings( \WP_REST_Request $request ): array {
		$data = array();
		$keys = $this->get_setting_keys();

		foreach ( $keys as $key => $default ) {
			if ( $request->has_param( $key ) ) {
				$value          = $request->get_param( $key );
				$data[ $key ]   = $value;
				update_option( "_cb_{$key}", $value );
			}
		}

		return $data;
	}
}
