<?php
/**
 * Consultant Booking global helper functions.
 *
 * @package ConsultantBooking
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locate and include a plugin template file.
 *
 * Checks the active theme first (allowing theme overrides), then falls back
 * to the plugin's own templates directory.
 *
 * @param string $template_slug Template path relative to the templates folder, without .php extension.
 * @param array  $args          Variables to extract into the template scope.
 */
function cb_get_template( $template_slug, $args = array() ) {
	$template_name = 'consultant-booking/' . $template_slug . '.php';
	$template      = locate_template( $template_name );

	if ( ! $template ) {
		$template = CB_PLUGIN_DIR . 'templates/' . $template_slug . '.php';
	}

	if ( file_exists( $template ) ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Intentional for template scope.
		extract( $args, EXTR_SKIP );
		include $template;
	}
}
