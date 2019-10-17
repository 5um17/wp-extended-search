<?php
/*
Plugin Name: WP Extended Search
Plugin URI: https://www.secretsofgeeks.com/2014/09/wordpress-search-tags-and-categories.html
Author: 5um17
Author URI: https://www.secretsofgeeks.com
Text Domain: wp-extended-search
Version: 2.0-dev
Description: Extend default search to search in selected post meta, taxonomies, post types and all authors.
*/

/* Define plugin constants */
if (!defined('WP_ES_DIR')) {
    //Plugin path
    define('WP_ES_DIR', plugin_dir_path(__FILE__));
}

if (!defined('WP_ES_URL')) {
    //Plugin url
    define('WP_ES_URL', plugin_dir_url(__FILE__));
}

if ( ! defined( 'WP_ES_Filename' ) ) {
    //Plugin Filename
    define( 'WP_ES_Filename', plugin_basename( __FILE__ ) );
}

/* Includes library files */
require_once WP_ES_DIR . '/includes/WP_ES.php';

/* Includes admin files */
if (is_admin()) {
    require_once WP_ES_DIR . '/admin/WP_ES_admin.php';
    require_once WP_ES_DIR . '/admin/WP_ES_setting_post_type.php';
}

function WPES() {
    return WP_ES::instance();
}

WPES();
