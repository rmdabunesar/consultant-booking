<?php
/**
 * Class Consultant_Booking
 *
 * Main plugin class to initialize the Consultant Booking plugin.
 *
 * @package Ahn\ConsultantBooking
 */

defined('ABSPATH') || exit;

use Ahn\ConsultantBooking\Controllers\Booking as BookingController;
use Ahn\ConsultantBooking\Controllers\Consultant as ConsultantController;
use Ahn\ConsultantBooking\Models\Booking;
use Ahn\ConsultantBooking\Models\Consultant;
use Ahn\ConsultantBooking\Settings;
use Ahn\ConsultantBooking\TemplatesManager;
use Ahn\ConsultantBooking\Widgets\CbWidgetSearch;

class Consultant_Booking
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Register hooks on init to ensure correct load order.
        add_action('init', [$this, 'load_textdomain'], 10);
        add_action('init', [$this, 'init_settings_and_templates'], 40);

        // Load models and controllers.
        $this->load_models();
        $this->load_controllers();

        // Enqueue scripts and styles.
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets'], 100);

        // Register widgets and sidebars.
        add_action('widgets_init', [$this, 'register_search_widget']);
        add_action('widgets_init', [$this, 'register_booking_sidebar']);

        // Force Classic Editor for custom post types.
        add_filter('use_block_editor_for_post_type', [$this, 'disable_block_editor_for_cpt'], 10, 2);
    }

    /**
     * Load plugin textdomain for translations.
     */
    public function load_textdomain(): void
    {
        load_plugin_textdomain('consultant-booking', false, CB_PLUGIN_DIR . '/languages/');
    }

    /**
     * Load model classes.
     */
    public function load_models(): void
    {
        new Consultant();
        new Booking();
    }

    /**
     * Load controller classes.
     */
    public function load_controllers(): void
    {
        new ConsultantController();
        new BookingController();
    }

    /**
     * Initialize Settings and TemplatesManager singletons.
     */
    public function init_settings_and_templates(): void
    {
        Settings::get_instance()->init();
        TemplatesManager::init();
    }

    /**
     * Enqueue public-facing scripts and styles.
     */
    public function enqueue_public_assets(): void
    {
        // CSS.
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
            [],
            '6.7.2'
        );
        wp_enqueue_style(
            'cb-style',
            CB_PLUGIN_URL . 'assets/public/css/style.css',
            [],
            CB_VERSION
        );

        // JS.
        wp_enqueue_script(
            'cb-script',
            CB_PLUGIN_URL . 'assets/public/js/main.js',
            ['jquery'],
            CB_VERSION,
            true
        );
        wp_localize_script(
            'cb-script',
            'consultantBooking',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('consultant_booking_nonce'),
            ]
        );

        // 2Checkout payment processing script.
        wp_enqueue_script(
            'cb-tco-token',
            'https://api.2checkout.com/2co/order_api/2co.min.js',
            [],
            null,
            false
        );
    }

    /**
     * Enqueue admin area scripts and styles.
     */
    public function enqueue_admin_assets(): void
    {
        // CSS.
        wp_enqueue_style(
            'cb-admin-style',
            CB_PLUGIN_URL . 'assets/admin/css/admin.css',
            [],
            CB_VERSION
        );

        // JS.
        wp_enqueue_script(
            'cb-metabox',
            CB_PLUGIN_URL . 'assets/admin/js/metabox.js',
            ['jquery'],
            CB_VERSION,
            true
        );
        wp_enqueue_script(
            'cb-admin-script',
            CB_PLUGIN_URL . 'assets/admin/js/admin.js',
            ['jquery'],
            CB_VERSION,
            true
        );
    }

    /**
     * Register booking sidebar widget area.
     */
    public function register_booking_sidebar(): void
    {
        register_sidebar(
            [
                'name'          => __('Booking Sidebar', 'consultant-booking'),
                'id'            => 'booking-sidebar',
                'before_widget' => '<div class="booking-sidebar">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="booking-title">',
                'after_title'   => '</h3>',
            ]
        );
    }

    /**
     * Register consultant booking search widget.
     */
    public function register_search_widget(): void
    {
        register_widget(CbWidgetSearch::class);
    }

    /**
     * Disable Gutenberg block editor for custom post types.
     *
     * @param bool   $use_block_editor Whether to use the block editor.
     * @param string $post_type        Post type.
     * @return bool
     */
    public function disable_block_editor_for_cpt(bool $use_block_editor, string $post_type): bool
    {
        if (in_array($post_type, ['cb_consultant', 'cb_booking'], true)) {
            return false;
        }

        return $use_block_editor;
    }
}

/**
 * Initialize the Consultant Booking plugin.
 */
new Consultant_Booking();
