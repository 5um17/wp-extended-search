<?php
/**
 * WPES setting post type
 *
 * @author 5um17
 */
class WP_ES_setting_post_type {
    
    public function __construct() {
	add_action( 'init', array( $this, 'register_setting_posttype') );
	add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
    }
    
    public function register_setting_posttype() {
	$labels = array(
	    'name' => 'WPES ' . __('Setting Names', 'wp-extended-search'),
	    'singular_name' => 'WPES ' . __('Setting Name', 'wp-extended-search'),
	    'add_new' => __('Add New Setting Name', 'wp-extended-search'),
	    'add_new_item' => __('Add New Setting Name', 'wp-extended-search'),
	    'edit_item' => __('Edit Setting Name', 'wp-extended-search'),
	    'all_items' => __('Setting Names', 'wp-extended-search'),
	    'search_items' => __('Search Setting Names', 'wp-extended-search'),
	    'not_found' => __('No Setting Names Found.', 'wp-extended-search'),
	    'not_found_in_trash' => __('No Setting Names found in Trash.', 'wp-extended-search')
	);

	$args = array(
	    'labels' => $labels,
	    'public' => false,
	    'show_ui' => true,
	    'show_in_menu' => 'wp-es',
	    'show_in_admin_bar' => false,
	    'query_var' => false,
	    'rewrite' => false,
	    'capability_type' => 'post',
	    'has_archive' => false,
	    'hierarchical' => false,
	    'menu_position' => NULL,
	    'supports' => array( 'title' ),
	    'show_in_rest' => false,
	);

	register_post_type('wpes_setting', $args);
	
	add_action( 'delete_post', array( $this, 'delete_wpes_setting' ) );
    }
    
    public function delete_wpes_setting( $post_id ) {
	if ( get_post_type( $post_id ) === 'wpes_setting' ) {
	    delete_option( 'wp_es_options_' . $post_id );
	}
    }

    public function register_meta_boxes() {
	add_meta_box( 'wpes-cpt-configure-setting', 'WP Extended Search', array( $this, 'primary_meta_box' ), 'wpes_setting', 'side' );
    }
    
    public function primary_meta_box( $post ) { 
	if ( get_post_status($post->ID) === 'publish' ) { ?>
	    <a class="button button-primary button-large" href="<?php echo admin_url( 'admin.php?page=wp-es&wpessid=' . $post->ID ); ?>"><?php _e( 'Configure Search Setting', 'wp-extended-search' ); ?></a><?php
	} else {
	    _e('To configure search setting please publish the setting post first.');
	}
    }
    
}
