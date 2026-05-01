<?php

namespace Ahn\ConsultantBooking;

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Class TemplatesManager
 * 
 * Manages custom page templates for the Consultant Booking plugin.
 * 
 * @package Ahn\ConsultantBooking
 */
class TemplatesManager {
    protected static $templates = [];

    public static function init() {
        self::$templates = [
            'templates/book-appointment.php' => __( 'Book Appointment', 'consultant-booking' ),
        ];

        add_filter( 'theme_page_templates', [ self::class, 'add_plugin_templates' ] );
        add_filter( 'template_include', [ self::class, 'load_plugin_template' ] );
    }

    public static function add_plugin_templates( $templates ) {
        return array_merge( $templates, self::$templates );
    }

    public static function load_plugin_template( $template ) {
        if ( ! is_page() ) {
            return $template;
        }

        global $post;
        if ( ! $post ) {
            return $template;
        }

        $page_template = get_page_template_slug( $post->ID );

        if ( isset( self::$templates[ $page_template ] ) ) {
            $theme_template = locate_template( basename( $page_template ) );

            if ( $theme_template ) {
                return $theme_template;
            }

            $plugin_template = trailingslashit( CB_PLUGIN_DIR ) . $page_template;

            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }

        return $template;
    }
}
