<?php
/**
 * Plugin Activator
 *
 * Runs once on plugin activation. Creates the Booking, Success and Cancel
 * pages (each populated with its shortcode) and stores their IDs in the
 * corresponding plugin options so the booking flow works out of the box.
 *
 * @package Ahn\ConsultantBooking
 */

namespace Ahn\ConsultantBooking;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activator
 */
class Activator {

	/**
	 * Activation entry point.
	 *
	 * Creates the three required pages and seeds their option IDs. Safe to run
	 * repeatedly – pages that already exist are left untouched.
	 */
	public static function activate(): void {
		self::create_page( '_cb_booking_page_id', __( 'Booking Form', 'consultant-booking' ), '[cb_booking_form]' );
		self::create_page( '_cb_success_page_id', __( 'Booking Success', 'consultant-booking' ), '[cb_booking_success]' );
		self::create_page( '_cb_cancel_page_id', __( 'Booking Cancel', 'consultant-booking' ), '[cb_booking_cancel]' );
	}

	/**
	 * Create a published page containing the given shortcode and store its ID.
	 *
	 * If the option already points to an existing published page, nothing is
	 * created so re-activation does not produce duplicates.
	 *
	 * @param string $option_key Option name that holds the page ID.
	 * @param string $title      Page title.
	 * @param string $shortcode  Shortcode to use as the page content.
	 */
	private static function create_page( string $option_key, string $title, string $shortcode ): void {
		$existing = (int) get_option( $option_key );

		if ( $existing > 0 ) {
			$page = get_post( $existing );

			if ( $page && 'page' === $page->post_type && 'trash' !== $page->post_status ) {
				return;
			}
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $shortcode,
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( $option_key, $page_id );
		}
	}
}
