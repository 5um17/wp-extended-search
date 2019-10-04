<?php
/*
Plugin Name: WP Extended Search
Plugin URI: http://www.secretsofgeeks.com/2014/09/wordpress-search-tags-and-categories.html
Author: 5um17
Author URI: http://www.secretsofgeeks.com
Text Domain: wp-extended-search
Version: 1.2
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

if (!defined('WP_ES_VERSION')) {
    //Plugin Version
    define('WP_ES_VERSION', '1.2');
}

/* Includes library files */
foreach( glob ( WP_ES_DIR . "/includes/*.php" ) as $filename ) {
    require_once( $filename );
}

/* Includes admin files */
if (is_admin()) {
    foreach( glob ( WP_ES_DIR . "/admin/*.php" ) as $filename ) {
       require_once( $filename );
    }
}
/* Global Class objects */
global $WP_ES, $WP_ES_admin;

/* Initiate classes */
$WP_ES = new WP_ES();

if (is_admin()) {
    $WP_ES_admin = new WP_ES_admin();
}