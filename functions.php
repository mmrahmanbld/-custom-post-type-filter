<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

add_filter('use_block_editor_for_post', '__return_false', 10);




function create_portfolio_post_type() {
    register_post_type('portfolio',
        array(
            'labels' => array(
                'name' => __('Portfolios'),
                'singular_name' => __('Portfolio')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'portfolio'),
            'supports' => array('title', 'editor', 'thumbnail'),
            'taxonomies' => array('category'),
        )
    );
}
add_action('init', 'create_portfolio_post_type');



function portfolio_enqueue_scripts_child_theme() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    // Enqueue the custom AJAX script
    wp_enqueue_script('portfolio-ajax', get_stylesheet_directory_uri() . '/js/portfolio-ajax.js', array('jquery'), null, true);
    
    // Enqueue Lightbox CSS and JS
    wp_enqueue_style('portfolio-lightbox', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
    wp_enqueue_script('portfolio-lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', array('jquery'), null, true);

    // Localize script for AJAX URL
    wp_localize_script('portfolio-ajax', 'portfolio_ajax_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'portfolio_enqueue_scripts_child_theme');



function portfolio_filter_shortcode() {
    $categories = get_categories(array(
        'taxonomy' => 'category',
        'hide_empty' => true,
    ));

    ob_start();
    ?>
    <div id="portfolio-filter">
        <ul id="portfolio-tabs">
            <li data-category="all" class="active"><?php _e('All Categories'); ?></li>
            <?php foreach ($categories as $category) : ?>
                <li data-category="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div id="portfolio-items">
        <!-- Portfolio items will be loaded here via AJAX -->
    </div>
    <div id="portfolio-pagination">
        <!-- Pagination links will be loaded here via AJAX -->
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('portfolio_filter', 'portfolio_filter_shortcode');



function load_portfolio_items() {
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $category = isset($_POST['category']) ? intval($_POST['category']) : '';

    $args = array(
        'post_type' => 'portfolio',
        'posts_per_page' => 3,
        'paged' => $paged,
    );

    if ($category && $category != 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $category,
            ),
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            ?>
            <div class="portfolio-item" data-category="<?php echo esc_attr($category); ?>">
                <a href="<?php the_post_thumbnail_url('full'); ?>" data-lightbox="portfolio" data-title="<?php the_title(); ?>">
                    <?php the_post_thumbnail('thumbnail'); ?>
                </a>
                <h2><?php the_title(); ?></h2>
                <?php the_excerpt(); ?>
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>No portfolio items found.</p>';
    endif;

    $total_pages = $query->max_num_pages;
    if ($total_pages > 1) {
        echo '<div class="pagination">';
        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<a class="portfolio-page" href="#" data-page="' . $i . '">' . $i . '</a>';
        }
        echo '</div>';
    }

    wp_die();
}
add_action('wp_ajax_load_portfolio_items', 'load_portfolio_items');
add_action('wp_ajax_nopriv_load_portfolio_items', 'load_portfolio_items');



