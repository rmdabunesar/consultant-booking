<?php
/**
 * Search Form Template
 * 
 * This template is used to search consultant.
 */
defined('ABSPATH') || exit;
?>

<form role="search" method="post" id="consultant-search-form" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <input type="hidden" name="post_type" value="cb_consultant" />
    <?php wp_nonce_field( 'consultant_booking_nonce', 'nonce' ); ?>
    <label>
        <span class="screen-reader-text"><?php _e( 'Search for:', 'consultant-booking' ); ?></span>
        <input id="consultant-search-input" type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search Consultants…', 'consultant-booking' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
    </label>
    <button type="submit" class="search-submit"><?php echo esc_html__( 'Search', 'consultant-booking' ); ?></button>
</form>

<div id="consultant-search-results"></div>

