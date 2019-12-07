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
	add_action( 'post_submitbox_misc_actions', array( $this, 'post_submit_box_js' ) );
	add_filter( 'post_row_actions', array( $this, 'remove_quick_edit' ), 10, 2 );
	add_filter( 'bulk_actions-edit-wpes_setting', array( $this, 'remove_bulk_actions' ) );
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
    
    public function post_submit_box_js() {
	if ( get_current_screen()->id !== 'wpes_setting' ) {
	    return; //Return if not the wpes setting CPT
	} ?>

	<script type="text/javascript">
	    // remove edit links
	    jQuery('#misc-publishing-actions a').remove();

	    //Remove visibilty button
	    jQuery('#visibility').remove();

	    //Remove draft button
	    jQuery('#minor-publishing-actions').remove();
	</script><?php
    }
    
    public function remove_quick_edit( $actions, $post ) {
	if ( get_post_type( $post ) === 'wpes_setting' ) {
	    unset($actions['inline hide-if-no-js']);
	}
	
	return $actions;
    }
    
    public function remove_bulk_actions( $actions ){
        unset( $actions[ 'edit' ] );
        return $actions;
    }

    public function delete_wpes_setting( $post_id ) {
	if ( get_post_type( $post_id ) === 'wpes_setting' ) {
	    delete_option( 'wp_es_options_' . $post_id );
	}
    }

    public function register_meta_boxes( $a ) {
	add_meta_box( 'wpes-cpt-configure-setting', 'WP Extended Search', array( $this, 'primary_meta_box' ), 'wpes_setting', 'side' );
	if ( !empty( $_GET['post'] ) && $this->is_setting_published( intval( $_GET['post'] ) ) ) {
	    add_meta_box( 'wpes-cpt-info', 'Uses:', array( $this, 'setting_info' ), 'wpes_setting', 'normal' );
	}
    }
    
    private function is_setting_published( $post ) {
	if ( get_post_status( $post ) === 'publish' ) {
	    return true;
	}
	
	return false;
    }

    public function primary_meta_box( $post ) { 
	
	if ( $this->is_setting_published( $post ) ) { ?>
	    <a class="button button-primary button-large" href="<?php echo admin_url( 'admin.php?page=wp-es&wpessid=' . $post->ID ); ?>"><?php _e( 'Configure Search Setting', 'wp-extended-search' ); ?></a><?php
	} else {
	    _e( 'To configure search setting please publish the setting first.', 'wp-extended-search' );
	}
    }
    
    public function setting_info( $post ) { ?>
	<h2><?php _e( 'You can display the search form for this setting in following ways:-', 'wp-extended-search' ); ?></h2>
	<hr />
	<table class="form-table">
	    <tr>
		<th><?php _e( 'Widget', 'wp-extended-search' ); ?></th>
		<td><?php printf( __( 'Go to <a href="%s">Appearance &raquo; Widgets</a> and add <em>%s Search Form</em> widget.', 'wp-extended-search' ), admin_url( 'widgets.php' ), 'WPES' ); ?></td>
	    </tr>
	    <tr>
		<th><?php _e( 'Shortcode', 'wp-extended-search' ); ?></th>
		<td>
		    <input title="<?php _e( 'Click to copy.', 'wp-extended-search' ); ?>" class="wpes-display-input" readonly="readonly" type="text" value="[wpes_search_form wpessid='<?php echo $post->ID; ?>']" />
		    <p class="description"><?php _e( 'You can add this shortcode in post/page. See Parameters section below to add more attributes to this shotcode.', 'wp-extended-search' ); ?></p>
		</td>
	    </tr>
	    <tr>
		<th><?php _e( 'PHP', 'wp-extended-search' ); ?></th>
		<td>
		    <textarea title="<?php _e( 'Click to copy.', 'wp-extended-search' ); ?>" rows="5" class="wpes-display-input" readonly="readonly">if ( function_exists( 'WPES_search_form' ) ) {
    WPES_search_form( array( 
	'wpessid' => <?php echo $post->ID; ?> 
    ) );
}</textarea>
		    <p class="description"><?php _e( 'Call this function after plugins_loaded action in functions.php or template files. See Parameters section below to pass more arguments to this function.', 'wp-extended-search' ); ?></p>
		</td>
	    </tr>
	    <tr>
		<th><?php _e( 'HTML', 'wp-extended-search' ); ?></th>
		<td>
		    <input title="<?php _e( 'Click to copy.', 'wp-extended-search' ); ?>" class="wpes-display-input" readonly="readonly" type="text" value="&#x3C;input type=&#x27;hidden&#x27; value=&#x27;<?php echo $post->ID; ?>&#x27; name=&#x27;wpessid&#x27; /&#x3E;" />
		    <p class="description"><?php _e( 'If you are using custom search form template e.g. searchform.php then add this field before &#x3C;/form&#x3E; form closing tag.', 'wp-extended-search' ); ?></p>
		</td>
	    </tr>
	</table>
	<h2><?php _e( 'Parameters:-', 'wp-extended-search' ); ?></h2>
	<hr />
	<dl class="wpes-params"><?php
	    foreach ( WPES()->WP_ES_searchform->get_form_default_args() as $key => $key_desc ) { ?>
		<dt><?php echo $key; ?></dt>
		<dd>
		    <p class="wpes-param-desc"><?php echo $key_desc; ?></p>
		    <p class="wpes-param-default"><em><?php echo __( 'Default value:', 'wp-extended-search' ) . ' ' . WPES()->WP_ES_searchform->get_form_default_args( $key ); ?></em></p>
		</dd><?php
	    } ?>
	</dl><?php
    }
    
}
