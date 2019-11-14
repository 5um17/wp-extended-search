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

    public function delete_wpes_setting( $post_id ) {
	if ( get_post_type( $post_id ) === 'wpes_setting' ) {
	    delete_option( 'wp_es_options_' . $post_id );
	}
    }

    public function register_meta_boxes() {
	add_meta_box( 'wpes-cpt-configure-setting', 'WP Extended Search', array( $this, 'primary_meta_box' ), 'wpes_setting', 'side' );
	add_meta_box( 'wpes-cpt-info', 'Uses:', array( $this, 'setting_info' ), 'wpes_setting', 'normal' );
    }
    
    public function primary_meta_box( $post ) { 
	
	if ( get_post_status($post->ID) === 'publish' ) { ?>
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
		<th><?php _e( 'Shortcode', 'wp-extended-search' ); ?></th>
		<td><input class="wpes-display-input" readonly="readonly" type="text" value="[wpes_search_form wpessid='<?php echo $post->ID; ?>']" /></td>
	    </tr>
	    <tr>
		<th><?php _e( 'PHP', 'wp-extended-search' ); ?></th>
		<td>
		    <textarea rows="3" class="wpes-display-input" readonly="readonly">WPES_search_form( array( 
    'wpessid' => <?php echo $post->ID; ?> 
) );</textarea>
		</td>
	    </tr>
	    <tr>
		<th><?php _e( 'HTML', 'wp-extended-search' ); ?></th>
		<td><input class="wpes-display-input" readonly="readonly" type="text" value="&#x3C;input type=&#x27;hidden&#x27; value=&#x27;<?php echo $post->ID; ?>&#x27; name=&#x27;wpessid&#x27; /&#x3E;" /></td>
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
