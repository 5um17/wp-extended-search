<?php
/**
 * WooCommerce compatibility
 *
 * @package WPES\Integrations
 * @author 5um17
 */

defined( 'ABSPATH' ) || exit();

/**
 * WooCommerce compatibility class
 */
class WPES_WC {

	/**
	 * Class constructor
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Init method, hooked on init action in main class.
	 *
	 * @since 2.0
	 */
	public function init() {
		add_filter( 'pre_get_posts', array( $this, 'set_wc_archive_page' ), 9 );
	}

	/**
	 * Register actions.
	 *
	 * @since 2.0
	 */
	public function admin_init() {
		add_filter( 'wpes_meta_keys_query', array( $this, 'product_meta_keys_query' ) );
		add_filter( 'wpes_meta_keys', array( $this, 'add_hidden_product_meta_keys' ) );
		add_filter( 'wpes_tax_args', array( $this, 'filter_product_taxonomy_args' ) );
		add_filter( 'wpes_post_types_args', array( $this, 'filter_post_types_args' ) );
	}

	/**
	 * Alert the meta query to display only product meta keys.
	 *
	 * @since 2.0
	 * @global object $wpdb wpdb instance
	 * @return NULL
	 */
	public function product_meta_keys_query() {
		global $wpdb;
		return "select DISTINCT meta_key from $wpdb->postmeta WHERE meta_key NOT LIKE '\_%' AND post_id IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'product') ORDER BY meta_key ASC";
	}

	/**
	 * Append the products hidden meta keys.
	 *
	 * @since 2.0
	 * @param array $fields Array of meta keys.
	 * @return array $fields Array of meta keys
	 */
	public function add_hidden_product_meta_keys( $fields ) {
		$fields[] = '_product_attributes';
		$fields[] = '_purchase_note';
		$fields[] = '_sku';
		$fields[] = '_tax_class';

		return array_unique( $fields );
	}

	/**
	 * Display only product taxonomies in WPES settings.
	 *
	 * @since 2.0
	 * @param array $tax_args Array of arguments for get_taxonomies().
	 * @return array $tax_args
	 */
	public function filter_product_taxonomy_args( $tax_args ) {
		$tax_args['object_type'] = array( 'product' );
		return $tax_args;
	}

	/**
	 * Display only product CPT in WPES settings.
	 *
	 * @since 2.0
	 * @param array $args Array of arguments for get_post_types().
	 * @return string
	 */
	public function filter_post_types_args( $args ) {
		$args['name'] = 'product';
		return $args;
	}

	/**
	 * Set is_archive and is_post_type_archive to true to display the WC template.
	 *
	 * @since 2.0
	 * @param object $query WP_Query object.
	 */
	public function set_wc_archive_page( $query ) {
		if ( WPES()->is_search( $query ) ) {
			$query->set( 'post_type', 'product' );
			$query->is_archive           = true;
			$query->is_post_type_archive = true;
		}
	}
}
