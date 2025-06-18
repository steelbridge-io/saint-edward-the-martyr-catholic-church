<?php
/**
 * Custom login page functionality
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
 exit;
}

/**
 * Enqueue custom login styles
 */
function church_custom_login_styles() {
 // Define the CSS file path
 $css_file = plugins_url('css/login-style.css', dirname(__FILE__));

 // Enqueue the custom login style
 wp_enqueue_style('custom-login', $css_file, array(), '1.0.0');
}
add_action('login_enqueue_scripts', 'church_custom_login_styles');

/**
 * Modify the login logo URL to point to your site home
 */
function church_login_logo_url() {
 return home_url();
}
add_filter('login_headerurl', 'church_login_logo_url');

/**
 * Change the login logo title
 */
function church_login_logo_title() {
 return get_bloginfo('name');
}
add_filter('login_headertext', 'church_login_logo_title');