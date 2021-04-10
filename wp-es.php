<?php
/**
 * Plugin Name: WP Extended Search
 * Plugin URI: https://www.secretsofgeeks.com/2014/09/wordpress-search-tags-and-categories.html
 * Author: 5um17
 * Author URI: https://www.secretsofgeeks.com
 * Text Domain: wp-extended-search
 * Version: 2.0.2
 * Description: Extend search functionality to search in selected post meta, taxonomies, post types, and all authors.
 *
 * @package WPES
 */

defined( 'ABSPATH' ) || exit();

// Define plugin constants.
// Plugin root directory path.
if ( ! defined( 'WPES_DIR' ) ) {
	define( 'WPES_DIR', plugin_dir_path( __FILE__ ) );
}

// Include directory path.
if ( ! defined( 'WPES_INCLUDES_PATH' ) ) {
	define( 'WPES_INCLUDES_PATH', WPES_DIR . 'includes/' );
}

// Admin directory path.
if ( ! defined( 'WPES_ADMIN_PATH' ) ) {
	define( 'WPES_ADMIN_PATH', WPES_INCLUDES_PATH . 'admin/' );
}

// Integration directory path.
if ( ! defined( 'WPES_INTEGRATIONS_PATH' ) ) {
	define( 'WPES_INTEGRATIONS_PATH', WPES_INCLUDES_PATH . 'integrations/' );
}

// Assets directory URL.
if ( ! defined( 'WPES_ASSETS_URL' ) ) {
	define( 'WPES_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
}

// Plugin main file name.
if ( ! defined( 'WPES_FILENAME' ) ) {
	define( 'WPES_FILENAME', plugin_basename( __FILE__ ) );
}

// Includes library files.
require_once WPES_INCLUDES_PATH . '/class-wpes-core.php';
require_once WPES_INCLUDES_PATH . '/class-wpes-search-form.php';
require_once WPES_INCLUDES_PATH . '/class-wpes-search-widget.php';

// Includes admin files.
if ( is_admin() ) {
	require_once WPES_ADMIN_PATH . '/class-wpes-admin.php';
	require_once WPES_ADMIN_PATH . '/class-wpes-settings-cpt.php';
}

// Public functions.

/**
 * WPES functions.
 *
 * @since 2.0
 * @return WPES_Core returns WPES_Core instance.
 */
function WPES() {
	return WPES_Core::instance();
}

/**
 * Print or return WPES search form.
 *
 * @since 2.0
 * @param array $args Search form arguments.
 * @param bool  $print true for print false to return. Default true.
 * @return string Search form HTML.
 */
function wpes_search_form( $args, $print = true ) {
	if ( true == $print ) {
		echo WPES()->wpes_search_form->get_search_form( $args );
		return;
	}

	return WPES()->wpes_search_form->get_search_form( $args );
}

// Start the show <3
WPES();
