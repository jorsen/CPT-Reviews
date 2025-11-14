<?php
/**
 * Plugin Name: Client Reviews CPT with Styled Slick Slider
 * Description: Registers a custom post type for Client Reviews and provides a frontend slider using the Slick library via the [client_reviews_slider] shortcode.
 * Version: 1.1
 * Author: Jorsen Mejia
 */

add_action('init', 'register_client_reviews_cpt');
function register_client_reviews_cpt() {
    register_post_type('client_review', array(
        'labels' => array(
            'name' => 'Client Reviews',
            'singular_name' => 'Client Review',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Review',
            'edit_item' => 'Edit Review',
            'new_item' => 'New Review',
            'view_item' => 'View Review',
            'all_items' => 'All Reviews',
        ),
        'public' => true,
        'menu_icon' => 'dashicons-testimonial',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'client-reviews'),
        'show_in_rest' => true,
    ));
}

// Enqueue Slick assets
add_action('wp_enqueue_scripts', 'enqueue_client_reviews_slick_assets');
function enqueue_client_reviews_slick_assets() {
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);
    
    // Enqueue your plugin's own style.css
    wp_enqueue_style(
        'client-reviews-slider-style',
        plugins_url('css/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'css/style.css') // cache busting
    );
}

// Shortcode for frontend Slick slider
add_shortcode('client_reviews_slider', 'display_client_reviews_slider');
function display_client_reviews_slider() {
    $args = array(
        'post_type' => 'client_review',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) : ?>
        <div class="client-reviews-wrapper">

            <div class="section-header">
                <p class="subtitle">Client Reviews</p>
                <h3 class="slider-heading">What our clients say about<br>us and our work</h3>
            </div>

            <!-- Desktop Slider (grouped in 3s) -->
            <div class="client-reviews-slick desktop-slider">
                <?php
                $group = [];
                while ($query->have_posts()) : $query->the_post();
                    $group[] = array(
                        'title' => get_the_title(),
                        'content' => wp_trim_words(get_the_content()),
                        'thumbnail' => get_the_post_thumbnail(null, 'full'),
                    );

                    if (count($group) === 3) {
                        echo '<div class="review-slide">';
                        echo '<div class="column column-1">' . render_review_card($group[0]) . '</div>';
                        echo '<div class="column column-2">';
                        echo render_review_card($group[1]);
                        echo render_review_card($group[2]);
                        echo '</div>';
                        echo '</div>';
                        $group = [];
                    }
                endwhile;
                wp_reset_postdata();

                if (!empty($group)) {
                    echo '<div class="review-slide">';
                    echo '<div class="column column-1">' . render_review_card($group[0]) . '</div>';
                    echo '<div class="column column-2">';
                    if (isset($group[1])) echo render_review_card($group[1]);
                    if (isset($group[2])) echo render_review_card($group[2]);
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Mobile Slider (1 review per slide) -->
            <div class="client-reviews-slick mobile-slider">
                <?php
                $query = new WP_Query($args); // Rerun query for mobile slider
                while ($query->have_posts()) : $query->the_post();
                    $review = array(
                        'title' => get_the_title(),
                        'content' => wp_trim_words(get_the_content()),
                        'thumbnail' => get_the_post_thumbnail(null, 'full'),
                    );
                    echo '<div class="review-slide">';
                    echo render_review_card($review);
                    echo '</div>';
                endwhile;
                wp_reset_postdata();
                ?>
            </div>

        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.desktop-slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: false,
                dots: true,
                arrows: false
            });

            $('.mobile-slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: false,
                dots: true,
                arrows: false
            });
        });
        </script>

    <?php endif;

    return ob_get_clean();
}



function render_review_card($review) {
    ob_start(); ?>
    <div class="review-card">
        <p class="review-text"><?php echo $review['content']; ?></p>
        <div class="review-author">
            <?php echo $review['thumbnail']; ?>
            <div class="author-info">
                <div class="author-name"><?php echo $review['title']; ?></div>
                <span class="location">Location</span>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
