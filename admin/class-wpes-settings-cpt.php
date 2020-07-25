<?php
/**
 * Custom post type for settings.
 *
 * @package WPES/Classes
 * @author 5um17
 */

defined( 'ABSPATH' ) || exit();

/**
 * Class to handle setting CPT.
 */
class WPES_Settings_CPT {

	/**
	 * Register actions.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_setting_posttype' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'post_submit_box_js' ) );
		add_filter( 'post_row_actions', array( $this, 'remove_quick_edit' ), 10, 2 );
		add_filter( 'bulk_actions-edit-wpes_setting', array( $this, 'remove_bulk_actions' ) );
	}

	/**
	 * Register post type
	 */
	public function register_setting_posttype() {
		// CPT labels.
		$labels = array(
			'name'               => 'WPES ' . __( 'Setting Names', 'wp-extended-search' ),
			'singular_name'      => 'WPES ' . __( 'Setting Name', 'wp-extended-search' ),
			'add_new'            => __( 'Add New Setting Name', 'wp-extended-search' ),
			'add_new_item'       => __( 'Add New Setting Name', 'wp-extended-search' ),
			'edit_item'          => __( 'Edit Setting Name', 'wp-extended-search' ),
			'all_items'          => __( 'Setting Names', 'wp-extended-search' ),
			'search_items'       => __( 'Search Setting Names', 'wp-extended-search' ),
			'not_found'          => __( 'No Setting Names Found.', 'wp-extended-search' ),
			'not_found_in_trash' => __( 'No Setting Names found in Trash.', 'wp-extended-search' ),
		);

		// CPT arguments.
		$args = array(
			'labels'            => $labels,
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => 'wp-es',
			'show_in_admin_bar' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capability_type'   => 'post',
			'has_archive'       => false,
			'hierarchical'      => false,
			'menu_position'     => null,
			'supports'          => array( 'title' ),
			'show_in_rest'      => false,
		);

		register_post_type( 'wpes_setting', $args );

		// Register function to handle delete post action.
		add_action( 'delete_post', array( $this, 'delete_wpes_setting' ) );
	}

	/**
	 * Remove some buttons from CPT meta box.
	 *
	 * @return type
	 */
	public function post_submit_box_js() {
		if ( get_current_screen()->id !== 'wpes_setting' ) {
			return; // Return if not the wpes setting CPT.
		} ?>

		<script type="text/javascript">
			// remove edit links
			jQuery('#misc-publishing-actions a').remove();

			//Remove visibilty button
			jQuery('#visibility').remove();

			//Remove draft button
			jQuery('#minor-publishing-actions').remove();
		</script>
		<?php
	}

	/**
	 * Remove quick edit controls.
	 *
	 * @param array  $actions quick edit actions.
	 * @param string $post WP_Post object.
	 * @return array $actions quick edit actions.
	 */
	public function remove_quick_edit( $actions, $post ) {
		if ( get_post_type( $post ) === 'wpes_setting' ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Remove bulk edit.
	 *
	 * @param array $actions Bulk edit actions.
	 * @return array $actions Bulk edit actions.
	 */
	public function remove_bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	/**
	 * Delete WPES setting when post is deleted from trash.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_wpes_setting( $post_id ) {
		if ( get_post_type( $post_id ) === 'wpes_setting' ) {
			delete_option( 'wp_es_options_' . $post_id );
		}
	}

	/**
	 * Register settings meta boxes.
	 */
	public function register_meta_boxes() {
		add_meta_box( 'wpes-cpt-configure-setting', 'WP Extended Search', array( $this, 'primary_meta_box' ), 'wpes_setting', 'side' );

		// Only display the info metabox when post is published.
		if ( ! empty( $_GET['post'] ) && $this->is_setting_published( intval( $_GET['post'] ) ) ) {
			add_meta_box( 'wpes-cpt-info', 'Uses:', array( $this, 'setting_info' ), 'wpes_setting', 'normal' );
		}
	}

	/**
	 * Check if setting is published or not.
	 *
	 * @param object $post WP_Post object.
	 * @return boolean true when published else false.
	 */
	private function is_setting_published( $post ) {
		if ( get_post_status( $post ) === 'publish' ) {
			return true;
		}

		return false;
	}

	/**
	 * Primary metabox to display the setting button.
	 *
	 * @param object $post WP_Post object.
	 */
	public function primary_meta_box( $post ) {
		if ( $this->is_setting_published( $post ) ) {
			?>
			<a class="button button-primary button-large" href="<?php echo admin_url( 'admin.php?page=wp-es&wpessid=' . $post->ID ); ?>"><?php _e( 'Configure Search Setting', 'wp-extended-search' ); ?></a>
			<?php
		} else {
			_e( 'To configure search setting please publish the setting first.', 'wp-extended-search' );
		}
	}

	/**
	 * Metabox to display the setting post info.
	 *
	 * @param object $post WP_Post object.
	 */
	public function setting_info( $post ) {
		?>
		<h2><?php _e( 'You can display the search form for this setting in following ways:-', 'wp-extended-search' ); ?></h2>
		<hr />
		<table class="form-table">
			<tr>
			<th><?php _e( 'Widget', 'wp-extended-search' ); ?></th>
			<td>
				<?php
				/* translators: %1$s: URL, %2$s: Plugin Name. */
				printf( __( 'Go to <a href="%1$s">Appearance &raquo; Widgets</a> and add <em>%2$s Search Form</em> widget.', 'wp-extended-search' ), admin_url( 'widgets.php' ), 'WPES' );
				?>
			</td>
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
				<textarea title="<?php _e( 'Click to copy.', 'wp-extended-search' ); ?>" rows="5" class="wpes-display-input" readonly="readonly">
if ( function_exists( 'WPES_search_form' ) ) {
	WPES_search_form( array( 
		'wpessid' => <?php echo $post->ID; ?> 
	) );
}				</textarea>
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
		<dl class="wpes-params">
			<?php
			foreach ( WPES()->WPES_Search_Form->get_form_default_args() as $key => $key_desc ) {
				?>
				<dt><?php echo $key; ?></dt>
				<dd>
					<p class="wpes-param-desc"><?php echo $key_desc; ?></p>
					<p class="wpes-param-default"><em><?php echo __( 'Default value:', 'wp-extended-search' ) . ' ' . WPES()->WPES_Search_Form->get_form_default_args( $key ); ?></em></p>
				</dd>
				<?php
			}
			?>
		</dl>
		<?php
	}
}
