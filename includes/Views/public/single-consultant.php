<?php
    /**
     * Single Consultant Template
     *
     * This template displays the details of a single consultant.
     *
     * @package Ahn\ConsultantBooking\Views
     */

    defined('ABSPATH') || exit;

    get_header();

    $booking_page_id   = get_option('_cb_booking_page_id');
    $booking_page_slug = get_post($booking_page_id) ? get_post($booking_page_id)->post_name : '';
?>

<div class="consultant-profile">
    <!-- LEFT SECTION -->
    <div class="consultant-left">

        <?php the_post_thumbnail('medium'); ?>

        <div class="social-links">
            <?php
                $consultant_socials = get_post_meta(get_the_ID(), '_consultant_socials', true);
            ?>
            <ul>
                <?php foreach ($consultant_socials as $social): ?>
                <li>
                    <a href="<?php echo esc_url($social['url']); ?>">
                        <i class="fa-brands                                            <?php echo esc_attr($social['platform']); ?>"></i>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="description">
            <?php the_content(); ?>
        </div>
    </div>

    <!-- RIGHT SECTION -->
    <div class="consultant-right">
        <h2><?php the_title(); ?></h2>
        <div class="designation">
            <?php echo esc_html(get_post_meta(get_the_ID(), '_consultant_designation', true)); ?>
        </div>

        <div class="contact">
            📞 Phone: <a href="tel:<?php echo esc_attr(get_post_meta(get_the_ID(), '_consultant_phone', true)); ?>">
                <?php echo esc_html(get_post_meta(get_the_ID(), '_consultant_phone', true)); ?>
            </a><br>
            📧 Email: <a href="mailto:<?php echo esc_attr(get_post_meta(get_the_ID(), '_consultant_email', true)); ?>">
                <?php echo esc_html(get_post_meta(get_the_ID(), '_consultant_email', true)); ?>
            </a>
        </div>

        <div class="price">
            💰 Consultation Fee: <strong>
                <?php echo number_format((float) get_post_meta(get_the_ID(), '_consultant_fee', true), 2); ?> BDT
            </strong>
        </div>

        <a href="<?php echo home_url($booking_page_slug . '?consultant_id=' . get_the_ID()); ?>" class="appointment-btn">
            Make Appointment
        </a>


    </div>
</div>
<?php
get_footer();
