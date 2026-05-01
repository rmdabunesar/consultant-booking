<?php
    /**
     * Consultant Loop Template
     *
     * This template displays a loop of consultants with their details.
     *
     * @package Ahn\ConsultantBooking\Views
     */

    defined('ABSPATH') || exit;

    $ids            = ! empty($atts['ids']) ? array_map('intval', explode(',', $atts['ids'])) : [];
    $posts_per_page = get_option('_cb_consultants_per_page', 6);
    $paged          = 1;

    $consultants = new WP_Query([
        'post_type'      => 'cb_consultant',
        'post__in'       => $ids,
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
    ]);
?>

<section class="consultants-list-area">
    <?php if (is_active_sidebar('booking-sidebar')): ?>
        <?php dynamic_sidebar('booking-sidebar'); ?>
    <?php endif; ?>

    <?php if ($consultants->have_posts()): ?>
    <div class="list">
        <?php while ($consultants->have_posts()): $consultants->the_post(); ?>
	        <?php
                    /**
                     * Consultant Meta Info
                     */
                    $consultant_designation = get_post_meta(get_the_ID(), '_consultant_designation', true);
                    $consultant_phone       = get_post_meta(get_the_ID(), '_consultant_phone', true);
                    $consultant_email       = get_post_meta(get_the_ID(), '_consultant_email', true);
                    $consultant_fee         = get_post_meta(get_the_ID(), '_consultant_fee', true);
                    $consultant_socials     = get_post_meta(get_the_ID(), '_consultant_socials', true);
                    $consultant_socials     = is_array($consultant_socials) ? $consultant_socials : [];
                ?>
	        <div class="list-item">
	            <div class="user-main">

	                <div class="user-img">
	                    <?php the_post_thumbnail('thumbnail'); ?>
	                </div>

	                <div class="user-name">
	                    <div class="user-headline">
	                        <div class="user-title-wishlisht">
	                            <a href="<?php the_permalink(); ?>">
	                                <h3><?php echo esc_html(get_the_title()); ?></h3>
	                            </a>
	                            <a id="add_wishlisht" href="#">
	                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
	                                    <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/>
	                                </svg>
	                            </a>
	                        </div>
	                        <span class="user-designation"><?php echo esc_html($consultant_designation); ?></span>

	                        <div class="user_contact">
	                            <span>
	                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
	                                    <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
	                                </svg>
	                                <?php echo esc_html($consultant_phone); ?>
	                            </span>
	                            <span>
	                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
	                                    <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
	                                </svg>
	                                <?php echo esc_html($consultant_email); ?>
	                            </span>
	                        </div>
	                    </div>
	                </div>

	                <?php if (! empty($consultant_socials)): ?>
	                <div class="user_social ul-li-block">
	                    <ul>
	                        <?php foreach ($consultant_socials as $social): ?>
	                        <li>
	                            <a href="<?php echo esc_url($social['url']); ?>">
	                                <i class="fa-brands	                                                    <?php echo esc_attr($social['platform']); ?>"></i>
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
        <?php endwhile;
        wp_reset_query(); ?>
    </div>
    <div class="load-more">
        <a id="load-more" href="#">Load More</a>
    </div>
    <?php else: ?>
    <div class="no-consultants">
        <h2><?php esc_html_e('No consultants found', 'health-visit'); ?></h2>
        <p><?php esc_html_e('Please check back later.', 'health-visit'); ?></p>
    </div>
    <?php endif; ?>
</section>

<?php
