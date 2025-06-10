<?php
/**
 * Plugin Name: Saint Edward The Martyr Catholic Church
 * Description: Adds a custom page template(s) that can be selected for pages or posts
 * Version: 1.0
 * Author: Chris Parsons
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
 exit;
}

// Include meta fields functionality
require_once plugin_dir_path(__FILE__) . 'inc/meta-field.php';

// Define constants for paths
define('CHURCH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CHURCH_TEMPLATES_PATH', CHURCH_PLUGIN_PATH . 'templates/');

/**
 * Add custom page template to the templates dropdown for pages
 */
function custom_add_page_template($templates)
{
 $templates['bible-study-template.php'] = 'Bible Study';
 return $templates;
}

add_filter('theme_page_templates', 'custom_add_page_template');

/**
 * Add the same template for posts
 */
function custom_add_post_template($templates)
{
 $templates['bible-study-template.php'] = 'Bible Study';
 return $templates;
}

add_filter('theme_post_templates', 'custom_add_post_template');

/**
 * Load the custom template when selected for pages or posts
 */
function custom_load_page_template($template)
{
 // Get global post
 global $post;

 // Return template if post is empty
 if (!$post) {
	return $template;
 }

 // Check if we're on a post or page with our template
 $template_name = get_post_meta($post->ID, '_wp_page_template', true);
 if ('bible-study-template.php' !== $template_name) {
	return $template;
 }

 // At this point, we know we're using our custom template
 // Let's output our complete template instead of returning a path

 // Ensure the template directory exists
 custom_create_template_directory();

 // Buffer the output to return it
 ob_start();

 // Include our custom header - this completely bypasses get_header()
 include(CHURCH_TEMPLATES_PATH . 'header-bible-study.php');
 ?>

    <div class="wrap">
        <div id="primary" class="content-area bible-study-template">
            <main id="main" class="site-main" role="main">

                 <?php while (have_posts()) : the_post(); ?>

                 <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                     <header class="well">
                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                        <?php if (is_singular('post')) : ?>
                          <div class="entry-meta">
                             <?php
                             echo '<span class="posted-on">' . get_the_date() . '</span>';
                             echo '<span class="byline"> ' . __('by', 'twentyseventeen') . ' ';
                             echo get_the_author() . '</span>';
                             ?>
                          </div><!-- .entry-meta -->
                        <?php endif; ?>
                     </header>

                     <div class="container-fluid">
                         <div class="row">
                          <?php
                          // Get sidebar content first
                          $card_title = get_post_meta(get_the_ID(), '_bible_study_card_title', true);
                          $card_text = get_post_meta(get_the_ID(), '_bible_study_card_text', true);

                          // Check if we have any meaningful content
                          $has_title = !empty(trim($card_title));
                          $has_text = !empty(trim(strip_tags($card_text)));
                          $has_sidebar_content = $has_title || $has_text;
                          ?>

                             <div class="<?php echo $has_sidebar_content ? 'col-md-7' : 'col-md-12'; ?>">
                              <?php the_content(); ?>
                             </div>

                          <?php if ($has_sidebar_content) : ?>
                              <div id="card-bible-sidebar" class="col-md-5">
                                  <div class="card">
                                      <div class="card-body">
                                       <?php if ($has_title) : ?>
                                           <h4 class="card-title"><?php echo esc_html($card_title); ?></h4>
                                       <?php endif; ?>

                                       <?php if ($has_text) : ?>
                                           <div class="card-text">
                                            <?php echo do_shortcode(wp_kses_post($card_text)); ?>
                                           </div>
                                       <?php endif; ?>
                                      </div>
                                  </div>
                              </div>
                          <?php endif; ?>
                         </div>
                     </div>
                 </article>
             <?php endwhile; ?>
            </main>
        </div>
    </div>

 <?php
 // Include footer
 get_footer();

 // Get the buffer and clear it
 $content = ob_get_clean();

 // Output the content
 echo $content;

 // Exit to prevent WordPress from continuing
 exit;
}

add_filter('template_include', 'custom_load_page_template', 999);

/**
 * Enqueue Bootstrap CSS and JS only when the Bible Study template is used
 */
function enqueue_bootstrap_for_template()
{
 // Check if we're on a singular post or page
 if (is_singular()) {
	global $post;

	// Get the template being used
	$template = get_post_meta($post->ID, '_wp_page_template', true);

	// Only enqueue Bootstrap if our template is being used
	if ('bible-study-template.php' === $template) {
        // Enqueue bible-study.css
        wp_enqueue_style('bible-study', plugins_url('css/bible-study.css', __FILE__), array(), '1.0');
        // Enqueue Bootstrap CSS
        wp_enqueue_style('bootstrap-css',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        [],
        '5.3.3'
        );

        // Enqueue Bootstrap JS with jQuery dependency
        wp_enqueue_script('bootstrap-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        [],
        '5.3.3',
        true
	 );
	}
 }
}

add_action('wp_enqueue_scripts', 'enqueue_bootstrap_for_template');

/**
 * Create custom header template and other template files
 */
function custom_create_template_directory()
{
 // Create templates directory if it doesn't exist
 if (!is_dir(CHURCH_TEMPLATES_PATH)) {
	mkdir(CHURCH_TEMPLATES_PATH, 0755, true);
 }

 // Create the header template file
 $header_file = CHURCH_TEMPLATES_PATH . 'header-bible-study.php';
 if (!file_exists($header_file)) {
	$header_content = '<?php
/**
 * Custom header for Bible Study template
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( \'charset\' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content">
        <?php
        /* translators: Hidden accessibility text. */
        _e( \'Skip to content\', \'twentyseventeen\' );
        ?>
    </a>

    <header id="masthead" class="site-header">

        <div class="custom-header narf">
            <div class="custom-header-media">
                <?php 
                // Use the post thumbnail as the header image
                echo get_the_post_thumbnail( get_queried_object_id(), \'twentyseventeen-featured-image\' );
                ?>
            </div>

            <div class="site-branding">
                <div class="wrap">
                    <?php if ( is_front_page() ) : ?>
                        <h1 class="site-title"><a href="<?php echo esc_url( home_url( \'/\' ) ); ?>" rel="home"><?php bloginfo( \'name\' ); ?></a></h1>
                    <?php else : ?>
                        <p class="site-title"><a href="<?php echo esc_url( home_url( \'/\' ) ); ?>" rel="home"><?php bloginfo( \'name\' ); ?></a></p>
                    <?php endif; ?>

                    <?php
                    $description = get_bloginfo( \'description\', \'display\' );

                    if ( $description || is_customize_preview() ) :
                    ?>
                        <p class="site-description"><?php echo $description; ?></p>
                    <?php endif; ?>
                </div><!-- .wrap -->
            </div><!-- .site-branding -->
        </div><!-- .custom-header -->

        <?php if ( has_nav_menu( \'top\' ) ) : ?>
            <div class="navigation-top">
                <div class="wrap">
                    <?php get_template_part( \'template-parts/navigation/navigation\', \'top\' ); ?>
                </div><!-- .wrap -->
            </div><!-- .navigation-top -->
        <?php endif; ?>

    </header><!-- #masthead -->

    <div class="site-content-contain">
        <div id="content" class="site-content">
';

	file_put_contents($header_file, $header_content);
 }
}

// Run this at plugin load time, not just activation
custom_create_template_directory();

// Register activation hook too for first install
register_activation_hook(__FILE__, 'custom_create_template_directory');

/**
 * Add post type support for page templates
 */
function custom_add_post_type_support()
{
 add_post_type_support('post', 'page-attributes');
}

add_action('init', 'custom_add_post_type_support');