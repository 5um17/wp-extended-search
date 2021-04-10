<?php
/**
 * Class for setting page.
 *
 * @package WPES/Classes
 * @author 5um17
 */

defined( 'ABSPATH' ) || exit();

/**
 * Register option page an other hooks.
 */
class WPES_Admin {

	/**
	 * Array of all setting posts.
	 *
	 * @since 2.0
	 * @var array
	 */
	private $all_setting_posts = array();

	/**
	 * Default Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		// Add option page.
		add_action( 'admin_menu', array( $this, 'add_setting_pages' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Register scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );

		// Add docs and setting links.
		add_filter( 'plugin_row_meta', array( $this, 'plugin_links' ), 10, 2 );
		add_filter( 'plugin_action_links_' . WPES_FILENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Add option pages.
	 *
	 * @global array $submenu Array of sub-menus.
	 * @since 1.0
	 */
	public function add_setting_pages() {
		global $submenu;

		add_menu_page( 'WP Extended Search Settings', 'Extended Search', 'manage_options', 'wp-es', null, $this->get_menu_icon() );
		add_submenu_page( 'wp-es', 'WP Extended Search Settings', 'Search Settings', 'manage_options', 'wp-es', array( $this, 'wp_es_page' ) );

		// Shift main setting page to top.
		if ( current_user_can( 'manage_options' ) ) {
			$wpes_menu = $submenu['wp-es'];
			$cpt_items = array_shift( $wpes_menu );
			array_push( $wpes_menu, $cpt_items );
			$submenu['wp-es'] = $wpes_menu;
		}
	}

	/**
	 * Print options page content.
	 *
	 * @since 1.0
	 */
	public function wp_es_page(){ ?>
		<div class="wrap">
			<h2>WP Extended Search <?php _e( 'Settings', 'wp-extended-search' ); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wp_es_option_group' );
				do_settings_sections( 'wp-es' );

				if ( empty( WPES()->wpes_settings['disabled'] ) ) {
					submit_button( __( 'Save Changes' ), 'primary', 'submit', false );
					echo '&nbsp;&nbsp;';
					submit_button( __( 'Reset to WP default', 'wp-extended-search' ), 'secondary', 'reset', false );
					if ( empty( WPES()->current_setting_id ) ) {
						echo '&nbsp;&nbsp;';
						submit_button( __( 'Disable WPES for global search', 'wp-extended-search' ), 'secondary', 'disable_global', false );
					}
				} else {
					submit_button( __( 'Enable WPES for global search', 'wp-extended-search' ), 'primary', 'enable_global', false );
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add Section settings and settings fields
	 *
	 * @since 1.0
	 */
	public function admin_init() {
		// Register Settings.
		register_setting( 'wp_es_option_group', WPES()->option_key_name, array( $this, 'wp_es_save' ) );

		// Add Sections.
		if ( empty( WPES()->wpes_settings['disabled'] ) ) {
			add_settings_section( 'wp_es_section_1', __( 'Select Fields to include in WordPress default Search', 'wp-extended-search' ), array( $this, 'wp_es_section_content' ), 'wp-es' );
			add_settings_section( 'wp_es_section_misc', __( 'Miscellaneous Settings', 'wp-extended-search' ), null, 'wp-es' );
		} else {
			add_settings_section( 'wp_es_section_disabled', __( 'WPES is disabled for global WordPress search. Select setting name to manage other search settings.', 'wp-extended-search' ), null, 'wp-es' );
		}

		// Add fields.
		add_settings_field( 'wp_es_settings_name', __( 'Setting Name', 'wp-extended-search' ), array( $this, 'wp_es_settings_name' ), 'wp-es', 'wp_es_section_1' );
		add_settings_field( 'wp_es_settings_name', __( 'Setting Name', 'wp-extended-search' ), array( $this, 'wp_es_settings_name' ), 'wp-es', 'wp_es_section_disabled' );

		// Add WC option only if WC is active.
		if ( class_exists( 'WooCommerce' ) ) {
			add_settings_field( 'wp_es_wc_search', __( 'WooCommerce', 'wp-extended-search' ), array( $this, 'wp_es_wc_search' ), 'wp-es', 'wp_es_section_1' );
		}

		add_settings_field( 'wp_es_title_and_post_content', __( 'General Search Setting', 'wp-extended-search' ), array( $this, 'wp_es_title_content_checkbox' ), 'wp-es', 'wp_es_section_1' );
		add_settings_field( 'wp_es_list_custom_fields', __( 'Select Meta Key Names', 'wp-extended-search' ), array( $this, 'wp_es_custom_field_name_list' ), 'wp-es', 'wp_es_section_1' );
		add_settings_field( 'wp_es_list_taxonomies', __( 'Select Taxonomies', 'wp-extended-search' ), array( $this, 'wp_es_taxonomies_settings' ), 'wp-es', 'wp_es_section_1' );
		add_settings_field( 'wp_es_include_authors', __( 'Author Setting', 'wp-extended-search' ), array( $this, 'wp_es_author_settings' ), 'wp-es', 'wp_es_section_1' );
		add_settings_field( 'wp_es_list_post_types', __( 'Select Post Types', 'wp-extended-search' ), array( $this, 'wp_es_post_types_settings' ), 'wp-es', 'wp_es_section_1' );
		add_settings_field( 'wp_es_terms_relation_type', __( 'Terms Relation Type', 'wp-extended-search' ), array( $this, 'wp_es_terms_relation_type' ), 'wp-es', 'wp_es_section_misc', array( 'label_for' => 'es_terms_relation' ) );
		if ( ! WPES()->wpes_settings['wc_search'] ) {
			// Exact matching is not available for WC till this bug this fixed https://core.trac.wordpress.org/ticket/50871.
			add_settings_field( 'wp_es_exact_search', __( 'Match the search term exactly', 'wp-extended-search' ), array( $this, 'wp_es_exact_search' ), 'wp-es', 'wp_es_section_misc' );
		}
		add_settings_field( 'wp_es_exclude_older_results', __( 'Select date to exclude older results', 'wp-extended-search' ), array( $this, 'wp_es_exclude_results' ), 'wp-es', 'wp_es_section_misc', array( 'label_for' => 'es_exclude_date' ) );
		add_settings_field( 'wp_es_number_of_posts', __( 'Posts per page', 'wp-extended-search' ), array( $this, 'wp_es_posts_per_page' ), 'wp-es', 'wp_es_section_misc', array( 'label_for' => 'es_posts_per_page' ) );
		add_settings_field( 'wp_es_search_results_order', __( 'Search Results Order', 'wp-extended-search' ), array( $this, 'wp_es_search_results_order' ), 'wp-es', 'wp_es_section_misc', array( 'label_for' => 'es_search_results_order' ) );
	}

	/**
	 * Enqueue admin style and scripts.
	 *
	 * @param string $hook Current page name.
	 * @since 1.0
	 */
	public function register_scripts( $hook ) {
		// Register scripts for main setting page.
		if ( 'toplevel_page_wp-es' === $hook ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'wpes_select2_js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js' );
			wp_enqueue_script( 'wpes_admin_js', WPES_ASSETS_URL . 'js/wp-es-admin.js', array( 'jquery-ui-datepicker', 'wpes_select2_js' ) );
			wp_enqueue_style( 'wpes_jquery_ui', WPES_ASSETS_URL . 'css/jQueryUI/jquery-ui.min.css' );
			wp_enqueue_style( 'wpes_jquery_ui_theme', WPES_ASSETS_URL . 'css/jQueryUI/jquery-ui.theme.min.css' );
			wp_enqueue_style( 'wpes_select2_css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
			wp_enqueue_style( 'wpes_admin_css', WPES_ASSETS_URL . 'css/wp-es-admin.css', array( 'wpes_jquery_ui', 'wpes_jquery_ui_theme', 'wpes_select2_css' ) );

			wp_localize_script(
				'wpes_admin_js',
				'wpes_admin_vars',
				array(
					'admin_setting_page'      => admin_url( 'admin.php?page=wp-es' ),
					'new_setting_url'         => admin_url( 'post-new.php?post_type=wpes_setting' ),
					'wc_setting_alert_txt'    => __( 'The setting will be saved before you can make further changes.', 'wp-extended-search' ),
					'select2_str_noResults'   => __( 'No results found.', 'wp-extended-search' ),
					'select2_str_placeholder' => __( 'Select', 'wp-extended-search' ),
				)
			);

			// Register scripts for setting CPT.
		} elseif ( get_current_screen()->id === 'wpes_setting' ) {
			wp_enqueue_style( 'wpes_admin_css', WPES_ASSETS_URL . 'css/wp-es-admin.css' );
			wp_enqueue_script( 'wpes_admin_cpt_js', WPES_ASSETS_URL . 'js/wp-es-setting-cpt.js' );

			wp_localize_script(
				'wpes_admin_cpt_js',
				'wpes_admin_cpt_vars',
				array(
					'str_copy' => __( 'copied', 'wp-extended-search' ),
				)
			);
		}
	}

	/**
	 * Get all meta keys.
	 *
	 * @since 1.0
	 * @global Object $wpdb WPDB object.
	 * @return Array array of meta keys.
	 */
	public function wp_es_fields() {
		global $wpdb;
		/**
		 * Filter query for meta keys in admin options.
		 *
		 * @since 1.0.1
		 * @param string SQL query.
		 */
		$wp_es_fields = $wpdb->get_results( apply_filters( 'wpes_meta_keys_query', "select DISTINCT meta_key from $wpdb->postmeta where meta_key NOT LIKE '\_%' ORDER BY meta_key ASC" ) );
		$meta_keys    = array();

		if ( is_array( $wp_es_fields ) && ! empty( $wp_es_fields ) ) {
			foreach ( $wp_es_fields as $field ) {
				if ( isset( $field->meta_key ) ) {
					$meta_keys[] = $field->meta_key;
				}
			}
		}

		/**
		 * Filter results of SQL query for meta keys.
		 *
		 * @since 1.1
		 * @param array $meta_keys array of meta keys.
		 */
		return apply_filters( 'wpes_meta_keys', $meta_keys );
	}

	/**
	 * Validate input settings.
	 *
	 * @since 1.0
	 * @global object $WP_ES Main class object.
	 * @param array $input input array by user.
	 * @return array validated input for saving.
	 */
	public function wp_es_save( $input ) {
		$settings = WPES()->wpes_settings;

		if ( isset( $_POST['disable_global'] ) ) {
			$input['disabled'] = true;
			add_settings_error( 'wp_es_error', 'wp_es_error_disable_global', __( 'WPES for global search has been disabled.', 'wp-extended-search' ), 'updated' );
			return $input;
		}

		if ( isset( $_POST['enable_global'] ) ) {
			$settings['disabled'] = false;
			add_settings_error( 'wp_es_error', 'wp_es_error_enable_global', __( 'WPES for global search has been enabled.', 'wp-extended-search' ), 'updated' );
			return $settings;
		}

		if ( isset( $_POST['reset'] ) ) {
			add_settings_error( 'wp_es_error', 'wp_es_error_reset', __( 'Settings has been changed to WordPress default search setting.', 'wp-extended-search' ), 'updated' );
			return WPES()->default_options();
		}

		if ( ! isset( $input['post_types'] ) || empty( $input['post_types'] ) ) {
			add_settings_error( 'wp_es_error', 'wp_es_error_post_type', __( 'Select atleast one post type!', 'wp-extended-search' ) );
			return $settings;
		}

		if ( empty( $input['title'] ) && empty( $input['content'] ) && empty( $input['excerpt'] ) && empty( $input['meta_keys'] ) && empty( $input['taxonomies'] ) && empty( $input['authors'] ) ) {
			add_settings_error( 'wp_es_error', 'wp_es_error_all_empty', __( 'Select atleast one setting to search!', 'wp-extended-search' ) );
			return $settings;
		}

		if ( ! empty( $input['exclude_date'] ) && ! strtotime( $input['exclude_date'] ) ) {
			add_settings_error( 'wp_es_error', 'wp_es_error_invalid_date', __( 'Date seems to be in invalid format!', 'wp-extended-search' ) );
			return $settings;
		}

		return $input;
	}

	/**
	 * Section content before displaying search settings.
	 *
	 * @since 1.0
	 */
	public function wp_es_section_content() {
		?>
		<em><?php _e( 'Every field have OR relation with each other. e.g. if someone search for "5um17" then search results will show those items which have "5um17" as meta value or taxonomy\'s term or in title or in content, whatever option is selected.', 'wp-extended-search' ); ?></em>
		<?php
	}

	/**
	 * Set and return all settings CPT items.
	 *
	 * @since 2.0
	 * @return array List of all settings.
	 */
	public function get_all_setting_names() {
		if ( empty( $this->all_setting_posts ) ) {
			$this->all_setting_posts = get_posts(
				array(
					'post_type'      => 'wpes_setting',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
		}

		return $this->all_setting_posts;
	}

	/**
	 * Display settings dropdown.
	 *
	 * @since 2.0
	 */
	public function wp_es_settings_name() {
		$all_settings = $this->get_all_setting_names();
		?>
		<select id="wpessid" name="wpessid">
			<option value=""><?php _e( 'Global (default)', 'wp-extended-search' ); ?></option>
			<?php
			foreach ( $all_settings as $setting_name ) {
				?>
			<option <?php selected( $setting_name->ID, WPES()->current_setting_id ); ?> value="<?php echo $setting_name->ID; ?>"><?php echo esc_attr( get_the_title( $setting_name ) ); ?></option>
				<?php
			}
			?>
			<option value="new"><?php _e( 'Create New', 'wp-extended-search' ); ?></option>
		</select>
		<?php

		if ( ! empty( WPES()->current_setting_id ) ) {
			echo '&nbsp;<a href="' . get_edit_post_link( WPES()->current_setting_id ) . '">' . __( 'Edit Name', 'wp-extended-search' ) . '</a>';
		}
	}

	/**
	 * Display WC options.
	 *
	 * @since 2.0
	 */
	public function wp_es_wc_search() {
		?>
		<input type="hidden" name="<?php echo WPES()->option_key_name; ?>[wc_search]" value="0" />
		<input <?php checked( WPES()->wpes_settings['wc_search'] ); ?> type="checkbox" id="es_wc_search" name="<?php echo WPES()->option_key_name; ?>[wc_search]" value="1" />&nbsp;
		<label for="es_wc_search"><?php _e( 'Optimize for Products Search', 'wp-extended-search' ); ?></label>
		<?php
	}

	/**
	 * Default settings checkbox.
	 *
	 * @since 1.0
	 */
	public function wp_es_title_content_checkbox() {
		?>
		<input type="hidden" name="<?php echo WPES()->option_key_name; ?>[title]" value="0" />
		<input <?php checked( WPES()->wpes_settings['title'] ); ?> type="checkbox" id="estitle" name="<?php echo WPES()->option_key_name; ?>[title]" value="1" />&nbsp;
		<label for="estitle"><?php _e( 'Search in Title', 'wp-extended-search' ); ?></label>
		<br />
		<input type="hidden" name="<?php echo WPES()->option_key_name; ?>[content]" value="0" />
		<input <?php checked( WPES()->wpes_settings['content'] ); ?> type="checkbox" id="escontent" name="<?php echo WPES()->option_key_name; ?>[content]" value="1" />&nbsp;
		<label for="escontent"><?php _e( 'Search in Content', 'wp-extended-search' ); ?></label>
		<br />
		<input type="hidden" name="<?php echo WPES()->option_key_name; ?>[excerpt]" value="0" />
		<input <?php checked( WPES()->wpes_settings['excerpt'] ); ?> type="checkbox" id="esexcerpt" name="<?php echo WPES()->option_key_name; ?>[excerpt]" value="1" />&nbsp;
		<label for="esexcerpt"><?php _e( 'Search in Excerpt', 'wp-extended-search' ); ?></label>
		<?php
	}

	/**
	 * Meta keys checkboxes.
	 *
	 * @since 1.0
	 */
	public function wp_es_custom_field_name_list() {
		$meta_keys = $this->wp_es_fields();
		if ( ! empty( $meta_keys ) ) {
			?>
			<select class="wpes-select2" multiple="multiple" name="<?php echo WPES()->option_key_name; ?>[meta_keys][]">
			<?php
			foreach ( (array) $meta_keys as $meta_key ) {
				?>
				<option <?php echo $this->wp_es_checked( $meta_key, WPES()->wpes_settings['meta_keys'], true ); ?> value="<?php echo $meta_key; ?>"><?php echo $meta_key; ?></option>
				<?php
			}
			?>
			</select>
			<?php
		} else {
			?>
			<em><?php _e( 'No meta key found!', 'wp-extended-search' ); ?></em>
			<?php
		}
	}

	/**
	 * Taxonomies checkbox.
	 *
	 * @since 1.0
	 */
	public function wp_es_taxonomies_settings() {

		/**
		 * Filter taxonomies arguments.
		 *
		 * @since 1.0.1
		 * @param array arguments array.
		 */
		$tax_args = apply_filters(
			'wpes_tax_args',
			array(
				'show_ui' => true,
				'public'  => true,
			)
		);

		/**
		 * Filter taxonomy list return by get_taxonomies function.
		 *
		 * @since 1.1
		 * @param $all_taxonomies Array of taxonomies.
		 */
		$all_taxonomies = apply_filters( 'wpes_tax', get_taxonomies( $tax_args, 'objects' ) );
		if ( is_array( $all_taxonomies ) && ! empty( $all_taxonomies ) ) {
			?>
			<select multiple="multiple" class="wpes-select2" name="<?php echo WPES()->option_key_name; ?>[taxonomies][]">
			<?php
			foreach ( $all_taxonomies as $tax_name => $tax_obj ) {
				?>
				<option <?php echo $this->wp_es_checked( $tax_name, WPES()->wpes_settings['taxonomies'], true ); ?> value="<?php echo $tax_name; ?>">
				<?php echo ! empty( $tax_obj->labels->name ) ? $tax_obj->labels->name : $tax_name; ?>
				</option>
				<?php
			}
			?>
			</select>
			<?php
		} else {
			?>
			<em><?php _e( 'No public taxonomy found!', 'wp-extended-search' ); ?></em>
			<?php
		}
	}

	/**
	 * Author settings meta box.
	 *
	 * @since 1.1
	 */
	public function wp_es_author_settings() {
		?>
		<input name="<?php echo WPES()->option_key_name; ?>[authors]" type="hidden" value="0" />
		<input id="wpes_inlcude_authors" <?php checked( WPES()->wpes_settings['authors'] ); ?> type="checkbox" value="1" name="<?php echo WPES()->option_key_name; ?>[authors]" />
		<label for="wpes_inlcude_authors"><?php _e( 'Search in Author display name', 'wp-extended-search' ); ?></label>
		<p class="description"><?php _e( 'If checked then it will display those results whose Author "Display name" match the search terms.', 'wp-extended-search' ); ?></p>
		<?php
	}

	/**
	 * Post type checkboexes.
	 *
	 * @since 1.0
	 */
	public function wp_es_post_types_settings() {

		/**
		 * Filter post type arguments.
		 *
		 * @since 1.0.1
		 * @param array arguments array.
		 */
		$post_types_args = apply_filters(
			'wpes_post_types_args',
			array(
				'show_ui' => true,
				'public'  => true,
			)
		);

		/**
		 * Filter post type array return by get_post_types function.
		 *
		 * @since 1.1
		 * @param array $all_post_types Array of post types.
		 */
		$all_post_types = apply_filters( 'wpes_post_types', get_post_types( $post_types_args, 'objects' ) );

		if ( is_array( $all_post_types ) && ! empty( $all_post_types ) ) {
			?>
			<select multiple="multiple" class="wpes-select2" name="<?php echo WPES()->option_key_name; ?>[post_types][]">
			<?php
			foreach ( $all_post_types as $post_name => $post_obj ) {
				?>
				<option <?php echo $this->wp_es_checked( $post_name, WPES()->wpes_settings['post_types'], true ); ?> value="<?php echo $post_name; ?>" >
				<?php echo isset( $post_obj->labels->name ) ? $post_obj->labels->name : $post_name; ?>
				</option>
				<?php
			}
			?>
			</select>
			<?php
		} else {
			?>
			<em><?php _e( 'No public post type found!', 'wp-extended-search' ); ?></em>
			<?php
		}
	}

	/**
	 * Terms relation type meta box.
	 *
	 * @since 1.1
	 */
	public function wp_es_terms_relation_type() {
		?>
		<select <?php echo $this->wp_es_disabled( WPES()->wpes_settings['exact_match'], 'yes' ); ?> id="es_terms_relation" name="<?php echo WPES()->option_key_name; ?>[terms_relation]">
			<option <?php selected( WPES()->wpes_settings['terms_relation'], 1 ); ?> value="1"><?php _e( 'AND', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['terms_relation'], 2 ); ?> value="2"><?php _e( 'OR', 'wp-extended-search' ); ?></option>
		</select>
		<p class="description">
			<?php
			if ( 'yes' === WPES()->wpes_settings['exact_match'] ) {
				_e( 'This option is disabled because you have selected "Match the search term exactly".  When using the exact match option, the sentence is not broken into terms instead the whole sentence is matched thus this option has no meaning.', 'wp-extended-search' );
			} else {
				_e( 'Type of query relation between search terms. e.g. someone searches for "my query" then define the relation between "my" and "query". The default value is AND.', 'wp-extended-search' );
			}
			?>
		</p>
		<?php
	}

	/**
	 * Exclude older results.
	 *
	 * @since 1.0.2
	 */
	public function wp_es_exclude_results() {
		?>
		<input class="regular-text" type="text" value="<?php echo esc_attr( WPES()->wpes_settings['exclude_date'] ); ?>" name="<?php echo WPES()->option_key_name; ?>[exclude_date]" id="es_exclude_date" />
		<p class="description"><?php _e( 'Contents will not appear in search results older than this date OR leave blank to disable this feature.', 'wp-extended-search' ); ?></p>
		<?php
	}

	/**
	 * Posts per search results page.
	 *
	 * @since 1.1
	 */
	public function wp_es_posts_per_page() {
		?>
		<input min="-1" class="small-text" type="number" value="<?php echo esc_attr( WPES()->wpes_settings['posts_per_page'] ); ?>" name="<?php echo WPES()->option_key_name; ?>[posts_per_page]" id="es_posts_per_page" />
		<p class="description"><?php _e( 'Number of posts to display on search result page OR leave blank for default value.', 'wp-extended-search' ); ?></p>
		<?php
	}

	/**
	 * Search results order.
	 *
	 * @since 1.3
	 */
	public function wp_es_search_results_order() {
		?>
		<select id="es_search_results_order" name="<?php echo WPES()->option_key_name; ?>[orderby]">
			<option <?php selected( WPES()->wpes_settings['orderby'], '' ); ?> value=""><?php _e( 'Relevance', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['orderby'], 'date' ); ?> value="date"><?php _e( 'Date', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['orderby'], 'modified' ); ?> value="modified"><?php _e( 'Last Modified Date', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['orderby'], 'title' ); ?> value="title"><?php _e( 'Post Title', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['orderby'], 'name' ); ?> value="name"><?php _e( 'Post Slug', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['orderby'], 'type' ); ?> value="type"><?php _e( 'Post Type', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['orderby'], 'comment_count' ); ?> value="comment_count"><?php _e( 'Number of Comments', 'wp-extended-search' ); ?></option>
			<option <?php selected( WPES()->wpes_settings['orderby'], 'rand' ); ?> value="rand"><?php _e( 'Random', 'wp-extended-search' ); ?></option>
		</select>
		<p class="description">
			<?php
			/* translators: %1$s: anchor tag opening, %2$s: anchor tag closed. */
			echo sprintf( __( 'Sort search results based on metadata of items. The default value is %1$sRelevance%2$s.', 'wp-extended-search' ), '<a href="https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters">', '</a>' );
			?>
		</p>
		<br />
		<label><input <?php echo $this->wp_es_checked( WPES()->wpes_settings['order'], array( 'DESC' ) ); ?> type="radio" value="DESC" name="<?php echo WPES()->option_key_name; ?>[order]" /><?php _e( 'Descending', 'wp-extended-search' ); ?></label>
		<label><input <?php echo $this->wp_es_checked( WPES()->wpes_settings['order'], array( 'ASC' ) ); ?> type="radio" value="ASC" name="<?php echo WPES()->option_key_name; ?>[order]" /><?php _e( 'Ascending', 'wp-extended-search' ); ?></label>
		<p class="description"><?php _e( 'Order the sorted search items in Descending or Ascending. Default is Descending.', 'wp-extended-search' ); ?></p>
		<?php
	}

	/**
	 * Select exact or partial term matching.
	 *
	 * @since 1.3
	 */
	public function wp_es_exact_search() {
		?>
		<label><input <?php echo $this->wp_es_checked( WPES()->wpes_settings['exact_match'], array( 'yes' ) ); ?> type="radio" value="yes" name="<?php echo WPES()->option_key_name; ?>[exact_match]" /><?php _e( 'Yes', 'wp-extended-search' ); ?></label>
		<label><input <?php echo $this->wp_es_checked( WPES()->wpes_settings['exact_match'], array( 'no' ) ); ?> type="radio" value="no" name="<?php echo WPES()->option_key_name; ?>[exact_match]" /><?php _e( 'No', 'wp-extended-search' ); ?></label>
		<p class="description"><?php _e( 'Whether to match search term exactly or partially e.g. If someone search "Word" it will display items matching "WordPress" or "Word" but if you select Yes then it will display items only matching "Word". The default value is No.', 'wp-extended-search' ); ?></p>
		<?php
	}

	/**
	 * Return checked or selected if value exist in array.
	 *
	 * @since 1.0
	 * @param mixed $value value to check against array.
	 * @param array $array haystack array.
	 * @param bool  $selected Set to <code>true</code> when using in select else <code>false</code>.
	 * @return string checked="checked" or selected="selected" or blank string.
	 */
	public function wp_es_checked( $value = false, $array = array(), $selected = false ) {
		if ( in_array( $value, $array, true ) ) {
			$checked = $selected ? 'selected="selected"' : 'checked="checked"';
		} else {
			$checked = '';
		}

		return $checked;
	}

	/**
	 * Return disabled if both values are equal.
	 *
	 * @since 1.3
	 * @param mixed $first_value First value to compare.
	 * @param mixed $second_value Second value to compare.
	 * @return string disabled="disabled" or blank string.
	 */
	public function wp_es_disabled( $first_value, $second_value = true ) {
		if ( $first_value == $second_value ) {
			return 'disabled="disabled"';
		}

		return '';
	}

	/**
	 * Add docs and other links to plugin row meta.
	 *
	 * @since 1.2
	 * @param array  $links The array having default links for the plugin.
	 * @param string $file The name of the plugin file.
	 * @return array $links array with newly added links.
	 */
	public function plugin_links( $links, $file ) {
		if ( WPES_FILENAME !== $file ) {
			return $links;
		}

		if ( is_array( $links ) ) {
			$links[] = '<a href="https://wpes.secretsofgeeks.com" target="_blank">'
					. __( 'Docs', 'wp-extended-search' )
					. '</a>';
			$links[] = '<a href="https://wordpress.org/plugins/search/5um17/" target="_blank">'
					. __( 'More Plugins', 'wp-extended-search' )
					. '</a>';
		}
		return $links;
	}

	/**
	 * Add setting link to plugin action list.
	 *
	 * @since 1.3
	 * @param array $links action links.
	 * @return array $links new action links.
	 */
	public function plugin_action_links( $links ) {
		if ( is_array( $links ) ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=wp-es' ) . '">'
					. __( 'Settings', 'wp-extended-search' )
					. '</a>';
		}

		return $links;
	}

	/**
	 * Get base64 encoded svg icon for menu.
	 *
	 * @since 2.0.1
	 * @return string svg icon data.
	 */
	public function get_menu_icon() {
		return 'data:image/svg+xml;base64,' . 'PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIKICJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy1TVkctMjAwMTA5MDQvRFREL3N2ZzEwLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA2ODMuMDAwMDAwIDQzNy4wMDAwMDAiPgogICAgPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsNDM3LjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSIKICAgICAgIGZpbGw9IiMwMDAwMDAiIHN0cm9rZT0ibm9uZSI+CiAgICAgICAgPHBhdGggZD0iTTQyNSA0MDYwIGwtMzUgLTM2IDAgLTE4MDUgMCAtMTgwNSAzMyAtMzIgMzMgLTMyIDYxMCAwIDYxMCAwIDMyIDMzCmMzMCAzMSAzMiAzNyAzMiAxMDggMCA3MiAtMSA3NiAtMzMgMTA3IGwtMzMgMzIgLTQ3NCAyIC00NzUgMyAwIDE1OTAgMCAxNTkwCjQ3NSAzIDQ3NCAyIDMzIDMyIGMzMiAzMSAzMyAzNSAzMyAxMDggMCA3MCAtMiA3OCAtMjggMTAzIGwtMjggMjcgLTYxMiAyCi02MTMgMyAtMzQgLTM1eiIvPgogICAgICAgIDxwYXRoIGQ9Ik01MjM3IDQwNjIgYy0yNCAtMjUgLTI3IC0zNSAtMjcgLTEwMyAwIC03MiAxIC03NiAzMyAtMTA3IGwzMyAtMzIKNDc3IDAgNDc3IDAgMCAtMTU5NSAwIC0xNTk1IC00NzggMCAtNDc4IDAgLTMyIC0zMyBjLTMwIC0zMSAtMzIgLTM3IC0zMiAtMTA4CjAgLTcyIDEgLTc2IDMzIC0xMDcgbDMzIC0zMiA2MTAgMCA2MTAgMCAzMiAzMyAzMiAzMyAwIDE4MDkgMCAxODA5IC0yNyAyOAotMjcgMjggLTYyMSAwIC02MjEgMCAtMjcgLTI4eiIvPgogICAgICAgIDxwYXRoIGQ9Ik0yODEyIDM0MTAgYy0yMTYgLTM5IC00MDQgLTE2OCAtNTI0IC0zNjAgLTI3MCAtNDMyIC0xMTkgLTEwMjMgMzE3Ci0xMjQwIDEyMiAtNjAgMTg5IC03NSAzMzUgLTc0IDEwNyAxIDEzNyA1IDIwNSAyNyAxMTAgMzcgMjEzIDk2IDI4OSAxNjcgbDY0CjYwIDcgLTM3IGM5IC01MiAzNyAtMTAzIDc3IC0xMzkgNDYgLTQzIDg3NCAtNjYzIDkyMyAtNjkxIDYwIC0zNCAxMzcgLTMyIDE5OAo2IDY5IDQ0IDEwMSA5OSAxMDUgMTgyIDQgNTYgMCA3NyAtMjEgMTIzIC0yNSA1NCAtMzUgNjIgLTQ5OSA0MDkgLTI4MCAyMTAKLTQ4OSAzNjAgLTUxMiAzNjcgLTIyIDcgLTU5IDEwIC04NyA3IC0yNyAtMyAtNDkgLTUgLTQ5IC00IDAgMiA5IDMwIDIxIDYyCjE0MyA0MDkgLTMyIDg3OSAtMzk3IDEwNjMgLTEzMyA2NyAtMzE1IDk2IC00NTIgNzJ6IG0yNzAgLTI3MCBjMTc2IC01NCAzMjIKLTIxNSAzNzUgLTQxMSAyNiAtOTkgMjIgLTI0NCAtMTEgLTM0NCAtNjAgLTE4NSAtMjEwIC0zMzUgLTM4MSAtMzgwIC0yNDMgLTYzCi00OTEgNTUgLTYwOCAyODggLTE2OSAzMzggLTEgNzU3IDM0MyA4NTMgNzIgMjAgMjA4IDE3IDI4MiAtNnoiLz4KICAgIDwvZz4KPC9zdmc+';
	}
}
