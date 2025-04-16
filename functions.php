<?php
/**
 * Portal Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Portal Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'PORTAL_CHILD_THEME_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'portal-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('portal-theme-css'), PORTAL_CHILD_THEME_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );