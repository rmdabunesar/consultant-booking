<?php
/**
 * Consultant Controller
 *
 * Handles template loading, meta field saving, shortcode rendering,
 * and AJAX handlers for the Consultant post type.
 *
 * @package Ahn\ConsultantBooking\Controllers
 */

namespace Ahn\ConsultantBooking\Controllers;

defined( 'ABSPATH' ) || exit;

use WP_Query;

/**
 * Class Consultant
 */
class Consultant {

	/**
	 * Constructor – registers all hooks for the consultant post type.
	 */
	public function __construct() {
		add_filter( 'template_include', array( $this, 'load_template' ), 99 );
		add_action( 'save_post_cb_consultant', array( $this, 'cb_consultant_save_meta_fields' ) );
		add_shortcode( 'cb_consultants', array( $this, 'cb_consultants_shortcode' ) );

		add_action( 'wp_ajax_cb_load_more', array( $this, 'cb_load_more' ) );
		add_action( 'wp_ajax_nopriv_cb_load_more', array( $this, 'cb_load_more' ) );

		add_action( 'wp_ajax_cb_consultant_search', array( $this, 'cb_consultant_search' ) );
		add_action( 'wp_ajax_nopriv_cb_consultant_search', array( $this, 'cb_consultant_search' ) );
	}

	/**
	 * Override the active theme template for consultant post type views.
	 *
	 * @param string $template Path to the currently resolved template file.
	 * @return string Path to the plugin template, or the original if not applicable.
	 */
	public function load_template( $template ) {
		if ( is_post_type_archive( 'cb_consultant' ) ) {
			$new_template = CB_PLUGIN_DIR . 'includes/Views/public/archive-consultant.php';
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		} elseif ( is_singular( 'cb_consultant' ) ) {
			$new_template = CB_PLUGIN_DIR . 'includes/Views/public/single-consultant.php';
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		}

		return $template;
	}

	/**
	 * Save consultant meta fields on post save.
	 *
	 * Verifies nonce before processing any data.
	 *
	 * @param int $post_id Post ID being saved.
	 */
	public function cb_consultant_save_meta_fields( $post_id ) {
		if ( ! isset( $_POST['consultant_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['consultant_meta_nonce'] ) ), 'save_consultant_meta' ) ) {
			return;
		}

		update_post_meta( $post_id, '_consultant_phone', sanitize_text_field( wp_unslash( $_POST['consultant_phone'] ?? '' ) ) );
		update_post_meta( $post_id, '_consultant_email', sanitize_email( wp_unslash( $_POST['consultant_email'] ?? '' ) ) );
		update_post_meta( $post_id, '_consultant_fee', floatval( $_POST['consultant_fee'] ?? 0 ) );
		update_post_meta( $post_id, '_consultant_designation', sanitize_text_field( wp_unslash( $_POST['consultant_designation'] ?? '' ) ) );

		// Save social media links (repeater field).
		$socials = array();
		if ( ! empty( $_POST['consultant_socials'] ) && is_array( $_POST['consultant_socials'] ) ) {
			foreach ( $_POST['consultant_socials'] as $item ) {
				$item = wp_unslash( $item );
				if ( ! empty( $item['platform'] ) && ! empty( $item['url'] ) ) {
					$socials[] = array(
						'platform' => sanitize_text_field( $item['platform'] ),
						'url'      => esc_url_raw( $item['url'] ),
					);
				}
			}
		}
		update_post_meta( $post_id, '_consultant_socials', $socials );

		// Save weekly availability (repeater field).
		$availability = array();
		if ( ! empty( $_POST['consultant_availability'] ) && is_array( $_POST['consultant_availability'] ) ) {
			foreach ( $_POST['consultant_availability'] as $item ) {
				$item = wp_unslash( $item );
				if ( ! empty( $item['day'] ) && isset( $item['from'] ) && isset( $item['to'] ) ) {
					$availability[] = array(
						'day'  => sanitize_text_field( $item['day'] ),
						'from' => sanitize_text_field( $item['from'] ),
						'to'   => sanitize_text_field( $item['to'] ),
					);
				}
			}
		}
		update_post_meta( $post_id, '_consultant_availability', $availability );
	}

	/**
	 * Shortcode handler for [cb_consultants].
	 *
	 * @param array $atts Shortcode attributes. Accepts 'ids' (comma-separated post IDs).
	 * @return string Rendered HTML.
	 */
	public function cb_consultants_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'ids' => '',
			),
			$atts,
			'cb_consultants'
		);

		$ids            = ! empty( $atts['ids'] ) ? array_map( 'intval', explode( ',', $atts['ids'] ) ) : array();
		$posts_per_page = get_option( '_cb_consultants_per_page', 10 );

		ob_start();
		cb_get_template(
			'loop/consultant-loop',
			array(
				'ids'            => $ids,
				'posts_per_page' => $posts_per_page,
			)
		);

		return ob_get_clean();
	}

	/**
	 * AJAX handler for the "Load More" button on the consultant listing.
	 *
	 * Outputs HTML for the next page of consultant cards and calls wp_die().
	 */
	public function cb_load_more() {
		$paged          = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1; 
		$posts_per_page = get_option( '_cb_consultants_per_page', 6 );

		$consultants = new WP_Query(
			array(
				'post_type'      => 'cb_consultant',
				'posts_per_page' => $posts_per_page,
				'paged'          => $paged,
			)
		);

		if ( $consultants->have_posts() ) {
			ob_start();

			while ( $consultants->have_posts() ) {
				$consultants->the_post();

				$consultant_designation = get_post_meta( get_the_ID(), '_consultant_designation', true );
				$consultant_phone       = get_post_meta( get_the_ID(), '_consultant_phone', true );
				$consultant_email       = get_post_meta( get_the_ID(), '_consultant_email', true );
				$consultant_socials     = get_post_meta( get_the_ID(), '_consultant_socials', true );
				$consultant_socials     = is_array( $consultant_socials ) ? $consultant_socials : array();
				?>

				<div id="list-item" class="list-item">
					<div class="user-main">

						<div class="user-img">
							<?php the_post_thumbnail( 'thumbnail' ); ?>
						</div>

						<div class="user-name">
							<div class="user-headline">
								<div class="user-title-wishlisht">
									<a href="<?php the_permalink(); ?>">
										<h3><?php echo esc_html( get_the_title() ); ?></h3>
									</a>
									<a id="add_wishlisht" href="#">
										<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
											<path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/>
										</svg>
									</a>
								</div>

								<span class="user-designation"><?php echo esc_html( $consultant_designation ); ?></span>

								<div class="user_contact">
									<span>
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
											<path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
										</svg>
										<?php echo esc_html( $consultant_phone ); ?>
									</span>
									<span>
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
											<path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
										</svg>
										<?php echo esc_html( $consultant_email ); ?>
									</span>
								</div>
							</div>
						</div>

						<?php if ( ! empty( $consultant_socials ) ) : ?>
						<div class="user_social ul-li-block">
							<ul>
								<?php foreach ( $consultant_socials as $social ) : ?>
								<li>
									<a href="<?php echo esc_url( $social['url'] ); ?>">
										<i class="fa-brands <?php echo esc_attr( $social['platform'] ); ?>"></i>
									</a>
								</li>
								<?php endforeach; ?>
							</ul>
							<div class="s2-share_btn text-center">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-share-fill" viewBox="0 0 16 16">
									<path d="M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5"/>
								</svg>
							</div>
						</div>
						<?php endif; ?>

					</div>
				</div>

				<?php
			}

			wp_reset_postdata();
			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is built with escaped values above.
		}

		wp_die();
	}

	/**
	 * AJAX handler for live consultant search.
	 *
	 * Verifies nonce and requires a minimum search term length of 2 characters.
	 * Returns an unordered list of matching consultant links, or an error JSON.
	 */
	public function cb_consultant_search() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'consultant_booking_nonce' ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'consultant-booking' ) );
			wp_die();
		}

		$search_term = sanitize_text_field( wp_unslash( $_POST['s'] ?? '' ) );

		if ( strlen( $search_term ) < 2 ) {
			wp_send_json_error( __( 'Search term is too short.', 'consultant-booking' ) );
			wp_die();
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'cb_consultant',
				's'              => $search_term,
				'posts_per_page' => 3,
			)
		);

		if ( $query->have_posts() ) {
			echo '<ul class="consultant-results">';
			while ( $query->have_posts() ) {
				$query->the_post();
				echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
			}
			echo '</ul>';
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No consultants found.', 'consultant-booking' ) . '</p>';
		}

		wp_die();
	}
}
