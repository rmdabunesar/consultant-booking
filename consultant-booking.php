<?php

/**
 * Plugin Name: Consultant Booking
 * Plugin URI: https://github.com/rmdabunesar/consultant-booking
 * Description: A simple plugin that allows students to book consultation sessions with scholarship advisors.
 * Version: 1.1.0
 * Author: Abu Nesar
 * Author URI: https://github.com/rmdabunesar
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: consultant-booking
 * Domain Path: /languages
 */

/**
 * Exit if accessed directly.
 */
defined('ABSPATH') || die('No script kiddies please!');

/**
 * Define plugin constants.
 */
define('CB_VERSION', '1.0.0');
define('CB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CB_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load Composer autoloader.
 */
if (file_exists(CB_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CB_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>';
        esc_html_e('Consultant Booking plugin error: Composer dependencies are missing. Please run "composer install" in the plugin directory.', 'consultant-booking');
        echo '</p></div>';
    });
    return;
}

/**
 * Include main plugin class and functions.
 */
require_once CB_PLUGIN_DIR . 'includes/class-consultant-booking.php';
require_once CB_PLUGIN_DIR . 'functions.php';

/**
 * Load procedural shortcode handlers.
 *
 * These files register shortcodes at file scope and are not classes, so the
 * PSR-4 autoloader never loads them – they must be required explicitly.
 */
require_once CB_PLUGIN_DIR . 'includes/Payments/Success.php';
require_once CB_PLUGIN_DIR . 'includes/Payments/Cancel.php';

/**
 * Create required pages and seed options on activation.
 */
register_activation_hook( __FILE__, array( 'Ahn\ConsultantBooking\Activator', 'activate' ) );
