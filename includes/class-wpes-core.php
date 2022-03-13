<?php
/**
 * Main search function class
 *
 * @package WPES/Classes
 * @author 5um17
 */

defined( 'ABSPATH' ) || exit();

/**
 * Main class of WP Extended Search
 */
final class WPES_Core {

	/**
	 * Plugin settings.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $wpes_settings = '';

	/**
	 * Current setting ID.
	 *
	 * @since 2.0
	 * @var int
	 */
	private $current_setting_id = false;

	/**
	 * Current setting option name.
	 *
	 * @since 2.0
	 * @var string
	 */
	private $option_key_name = 'wp_es_options';

	/**
	 * Class instance.
	 *
	 * @since 2.0
	 * @var WPES_Core
	 */
	public static $instance = false;

	/**
	 * WPES_Search_Form instance.
	 *
	 * @since 2.0
	 * @var WPES_Search_Form
	 */
	public $wpes_search_form = false;

	/**
	 * WPES_WC instance.
	 *
	 * @since 2.0
	 * @var WPES_WC
	 */
	public $wpes_wc = false;

	/**
	 * WPES_WPML instance.
	 *
	 * @since 2.0
	 * @var WPES_WPML
	 */
	public $wpes_wpml = false;

	/**
	 * WPES_Admin instance.
	 *
	 * @since 2.0
	 * @var WPES_Admin
	 */
	public $wpes_admin = false;

	/**
	 * Flag to include mime type query.
	 *
	 * @since 2.1
	 * @var boolean
	 */
	private $include_mime_type = false;

	/**
	 * Default Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// initiate admin classes.
		if ( is_admin() ) {
			$this->wpes_admin = new WPES_Admin();
			new WPES_Settings_CPT();
		}

		// Load settings.
		$this->wpes_settings = $this->wp_es_options();

		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! $this->preserved_ajax_actions() ) ) {
			// Only filter non admin requests!
			add_action( 'init', array( $this, 'wp_es_init' ) );
		}

		add_action( 'plugins_loaded', array( $this, 'wp_es_plugin_loaded' ) );
		add_action( 'widgets_init', array( $this, 'wp_es_register_widgets' ) );
	}

	/**
	 * Access class properties.
	 *
	 * @since 2.0
	 * @param string $name Property name.
	 * @return mixed Property value if exist else false.
	 */
	public function __get( $name ) {
		if ( isset( $this->$name ) ) {
			return $this->$name;
		}

		return false;
	}

	/**
	 * Check if property exist.
	 *
	 * @since 2.0
	 * @param string $name Property name.
	 * @return boolean true if exist else false.
	 */
	public function __isset( $name ) {
		if ( isset( $this->$name ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get class instance.
	 *
	 * @since 2.0
	 * @return WPES_Core Class object.
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get Default options.
	 *
	 * @since 1.0
	 */
	public function default_options() {
		$settings = array(
			'disabled'       => false,
			'wc_search'      => false,
			'title'          => true,
			'content'        => true,
			'excerpt'        => true,
			'meta_keys'      => array(),
			'taxonomies'     => array(),
			'authors'        => false,
			'post_types'     => array( 'post', 'page' ),
			'exclude_date'   => '',
			'posts_per_page' => '',
			'terms_relation' => 1,
			'orderby'        => '',
			'order'          => 'DESC',
			'exact_match'    => 'no',
			'media_types'    => array(),
		);
		return $settings;
	}

	/**
	 * Get plugin options.
	 *
	 * @since 1.0
	 */
	public function wp_es_options() {

		if ( ! empty( $this->wpes_settings ) ) {
			return $this->wpes_settings;
		}

		if ( ! empty( $_REQUEST['wpessid'] ) ) {
			$wpessid = intval( $_REQUEST['wpessid'] );

			// Set the current setting ID only if setting post exist for backend OR option value exist for front-end.
			if ( ( is_admin() && get_post_type( $wpessid ) === 'wpes_setting' ) || ( ! is_admin() && ! empty( get_option( "wp_es_options_$wpessid" ) ) ) ) {
				$this->current_setting_id = $wpessid;
				$this->option_key_name    = 'wp_es_options_' . $this->current_setting_id;
			}
		}

		$settings = wp_parse_args( get_option( $this->option_key_name ), $this->default_options() );

		return $settings;
	}

	/**
	 * Load plugin text domain.
	 *
	 * @since 1.0.1
	 */
	public function wp_es_plugin_loaded() {
		// Load the search form class when all plugins are loaded.
		$this->wpes_search_form = new WPES_Search_Form();

		// Include WC class if WC is active.
		if ( class_exists( 'WooCommerce' ) && ! empty( $this->wpes_settings['wc_search'] ) ) {
			require_once WPES_INTEGRATIONS_PATH . '/class-wpes-wc.php';
			$this->wpes_wc = new WPES_WC();
		}

		// Include WPML class if WPML is active.
		if ( class_exists( 'SitePress' ) ) {
			require_once WPES_INTEGRATIONS_PATH . '/class-wpes-wpml.php';
			$this->wpes_wpml = new WPES_WPML();
		}

		// Load plugin text domain.
		load_plugin_textdomain( 'wp-extended-search', false, dirname( plugin_basename( WPES_DIR . 'wp-es.php' ) ) . '/languages' );
	}

	/**
	 * Register the search widget.
	 *
	 * @since 2.0
	 */
	public function wp_es_register_widgets() {
		register_widget( 'WPES_Search_Widget' );
	}

	/**
	 * Init function.
	 *
	 * @since 1.0
	 * @return NULL
	 */
	public function wp_es_init() {
		$enabled = ! $this->wpes_settings['disabled'];

		/**
		 * Filter plugin's all action hooks to enabled or disabled.
		 *
		 * @since 1.0.1
		 * @param bool true to enable or false to disable.
		 */
		if ( ! apply_filters( 'wpes_enabled', $enabled ) ) {
			return;
		}

		// Filter to modify search query.
		add_filter( 'posts_search', array( $this, 'wp_es_custom_query' ), 500, 2 );

		// Action for modify query arguments.
		add_action( 'pre_get_posts', array( $this, 'wp_es_pre_get_posts' ), 500 );

		// Call the init function so wpes_enabled filter can work.
		if ( ! empty( $this->wpes_wc ) ) {
			$this->wpes_wc->init();
		}
	}

	/**
	 * Add post type in where clause of wp query.
	 *
	 * @since 1.0
	 * @param object $query wp_query object.
	 */
	public function wp_es_pre_get_posts( $query ) {
		if ( $this->is_search( $query ) ) {

			// Set post types.
			if ( ! empty( $this->wpes_settings['post_types'] ) ) {
				if ( isset( $_GET['post_type'] ) && in_array( esc_attr( $_GET['post_type'] ), (array) $this->wpes_settings['post_types'], true ) ) {
					$query->query_vars['post_type'] = (array) esc_attr( $_GET['post_type'] );
				} else {
					$query->query_vars['post_type'] = (array) $this->wpes_settings['post_types'];
				}
			}

			// Set date query to exclude resutls.
			if ( ! empty( $this->wpes_settings['exclude_date'] ) ) {
				$query->set(
					'date_query',
					array(
						array(
							'after' => $this->wpes_settings['exclude_date'],
						),
					)
				);
			}

			// Set posts page page.
			$posts_per_page = intval( $this->wpes_settings['posts_per_page'] ); // Putting in extra line just to get rid off from WP svn pre-commit hook error.
			if ( ! empty( $posts_per_page ) ) {
				$query->set( 'posts_per_page', $posts_per_page );
			}

			// If searching for attachment type then set post status to inherit.
			if ( is_array( $query->get( 'post_type' ) ) && in_array( 'attachment', $query->get( 'post_type' ), true ) ) {
				$query->set( 'post_status', array( 'publish', 'inherit' ) );
				if ( is_user_logged_in() ) { // Since we are chaning the default post status we need to take care of private post status.
					$query->set( 'post_status', array( 'publish', 'inherit', 'private' ) );
					$query->set( 'perm', 'readable' ); // Check if current user can read private posts.
				}

				if ( ! empty( $this->wpes_settings['media_types'] ) ) {
					$this->include_mime_type = true;
					// If there is any mime type set in WP_Query already then remove it.
					if ( $query->get( 'post_mime_type' ) ) {
						$query->set( 'post_mime_type', false );
					}
				}
			}

			// Set orderby.
			if ( ! empty( $this->wpes_settings['orderby'] ) ) {
				$query->set( 'orderby', $this->wpes_settings['orderby'] );
			}

			// Set results order.
			if ( in_array( $this->wpes_settings['order'], array( 'DESC', 'ASC' ), true ) && 'DESC' !== $this->wpes_settings['order'] ) {
				$query->set( 'order', $this->wpes_settings['order'] );
			}

			// Set exact match.
			if ( 'yes' === $this->wpes_settings['exact_match'] ) {
				$query->set( 'exact', true );
				$query->set( 'sentence', true );
			}
		}
	}

	/**
	 * Core function return the custom query.
	 *
	 * @since 1.0
	 * @global Object $wpdb WordPress db object.
	 * @param string $search Search query.
	 * @param object $wp_query WP query.
	 * @return string $search Search query.
	 */
	public function wp_es_custom_query( $search, $wp_query ) {
		global $wpdb;

		if ( ! $this->is_search( $wp_query ) ) {
			return $search; // Do not proceed if does not match our search conditions.
		}

		$q         = $wp_query->query_vars;
		$n         = ! empty( $q['exact'] ) ? '' : '%';
		$search    = '';
		$searchand = '';

		/**
		 * Filter the term relation type OR/AND.
		 *
		 * @since 1.2
		 * @param NULL
		 */
		$terms_relation_type = apply_filters( 'wp_es_terms_relation_type', null );

		if ( ! in_array( $terms_relation_type, array( 'AND', 'OR' ), true ) ) {
			$terms_relation_type = ( intval( $this->wpes_settings['terms_relation'] ) === 2 ) ? 'OR' : 'AND';
		}

		foreach ( (array) $q['search_terms'] as $term ) {

			$term = $n . $wpdb->esc_like( $term ) . $n;

			// change query as per plugin settings.
			$or = '';
			if ( ! empty( $this->wpes_settings ) ) {
				$search .= "{$searchand} (";

				// if post title search is enabled.
				if ( ! empty( $this->wpes_settings['title'] ) ) {
					$search .= $wpdb->prepare( "($wpdb->posts.post_title LIKE %s)", $term );
					$or      = ' OR ';
				}

				// if content search is enabled.
				if ( ! empty( $this->wpes_settings['content'] ) ) {
					$search .= $or;
					$search .= $wpdb->prepare( "($wpdb->posts.post_content LIKE %s)", $term );
					$or      = ' OR ';
				}

				// if excerpt search is enabled.
				if ( ! empty( $this->wpes_settings['excerpt'] ) ) {
					$search .= $or;
					$search .= $wpdb->prepare( "($wpdb->posts.post_excerpt LIKE %s)", $term );
					$or      = ' OR ';
				}

				// if post meta search is enabled.
				if ( ! empty( $this->wpes_settings['meta_keys'] ) ) {
					$meta_key_or = '';

					foreach ( $this->wpes_settings['meta_keys'] as $key_slug ) {
						$search     .= $or . $meta_key_or;
						$search     .= $wpdb->prepare( '(espm.meta_key = %s AND espm.meta_value LIKE %s)', $key_slug, $term );
						$or          = '';
						$meta_key_or = ' OR ';
					}

					$or = ' OR ';
				}

				// if taxonomies search is enabled.
				if ( ! empty( $this->wpes_settings['taxonomies'] ) ) {
					$tax_or = '';

					foreach ( $this->wpes_settings['taxonomies'] as $tax ) {
						$search .= $or . $tax_or;
						$search .= $wpdb->prepare( '(estt.taxonomy = %s AND est.name LIKE %s)', $tax, $term );
						$or      = '';
						$tax_or  = ' OR ';
					}

					$or = ' OR ';
				}

				// If authors search is enabled.
				if ( ! empty( $this->wpes_settings['authors'] ) ) {
					$search .= $or;
					$search .= $wpdb->prepare( '(esusers.display_name LIKE %s)', $term );
				}

				$search .= ')';
			} else {
				// If plugin settings not available return the default query.
				$search .= $searchand;
				$search .= $wpdb->prepare( "(($wpdb->posts.post_title LIKE %s) OR ($wpdb->posts.post_content LIKE %s) OR ($wpdb->posts.post_excerpt LIKE %s))", $term, $term, $term );
			}

			$searchand = " $terms_relation_type ";
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		// Maybe add mime type query.
		$search = $this->add_mime_type_where( $search );

		// Join Table.
		add_filter( 'posts_join_request', array( $this, 'wp_es_join_table' ) );

		// Request distinct results.
		add_filter( 'posts_distinct_request', array( $this, 'wp_es_distinct' ) );

		/**
		 * Filter search query return by plugin.
		 *
		 * @since 1.0.1
		 * @param string $search SQL query.
		 * @param object $wp_query global wp_query object.
		 */
		return apply_filters( 'wpes_posts_search', $search, $wp_query ); // phew :P All done, Now return everything to wp.
	}

	/**
	 * Join tables.
	 *
	 * @since 1.0
	 * @global Object $wpdb WPDB object.
	 * @param string $join query for join.
	 * @return string $join query for join.
	 */
	public function wp_es_join_table( $join ) {
		global $wpdb;

		// join post meta table.
		if ( ! empty( $this->wpes_settings['meta_keys'] ) ) {
			$join .= " LEFT JOIN $wpdb->postmeta espm ON ($wpdb->posts.ID = espm.post_id) ";
		}

		// join taxonomies table.
		if ( ! empty( $this->wpes_settings['taxonomies'] ) ) {
			$join .= " LEFT JOIN $wpdb->term_relationships estr ON ($wpdb->posts.ID = estr.object_id) ";
			$join .= " LEFT JOIN $wpdb->term_taxonomy estt ON (estr.term_taxonomy_id = estt.term_taxonomy_id) ";
			$join .= " LEFT JOIN $wpdb->terms est ON (estt.term_id = est.term_id) ";
		}

		// Joint the users table.
		if ( ! empty( $this->wpes_settings['authors'] ) ) {
			$join .= " LEFT JOIN $wpdb->users esusers ON ($wpdb->posts.post_author = esusers.ID) ";
		}

		return $join;
	}

	/**
	 * Request distinct results.
	 *
	 * @since 1.0
	 * @param string $distinct DISTINCT Keyword.
	 * @return string $distinct
	 */
	public function wp_es_distinct( $distinct ) {
		$distinct = 'DISTINCT';
		return $distinct;
	}

	/**
	 * Add mime type SQL to search query.
	 *
	 * @since 2.1
	 * @global Object $wpdb WPDB object.
	 * @param string $search Search SQL.
	 * @return string Search SQL with mime type query.
	 */
	private function add_mime_type_where( $search ) {
		if ( true === $this->include_mime_type ) {
			global $wpdb;
			$mime_types = esc_sql( $this->wpes_settings['media_types'] );
			array_push( $mime_types, '' );
			$mime_types = implode( "','", $mime_types );
			$search    .= " AND $wpdb->posts.post_mime_type IN ('$mime_types') ";
		}

		return $search;
	}

	/**
	 * Check if it is WordPress core or some plugin Ajax action.
	 *
	 * @since 1.1.2
	 * @return boolean TRUE if it core Ajax request else false.
	 */
	public function preserved_ajax_actions() {
		$preserved_actions = array(
			'query-attachments',
			'menu-quick-search',
			'acf/fields',
			'elementor_ajax',
			'woocommerce_json_search_pages',
		);

		$current_action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : false;

		foreach ( $preserved_actions as $action ) {
			if ( strpos( $current_action, $action ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if it is bbPress page.
	 *
	 * @since 1.2
	 * @return boolean TRUE if bbPress search else FALSE.
	 */
	public function is_bbpress_search() {
		if ( function_exists( 'is_bbpress' ) ) {
			return is_bbpress();
		}

		return false;
	}

	/**
	 * Check if the query for the search and should be altered by WPES.
	 *
	 * @since 2.0.2
	 * @param WP_Query $query WP_Query object.
	 * @return boolean true if query satisfied search conditions else false.
	 */
	public function is_search( $query = false ) {
		// If empty set the current global query.
		if ( empty( $query ) ) {
			global $wp_query;
			$query = $wp_query;
		}

		if ( ! empty( $query->is_search ) && ! empty( $query->get( 's' ) ) && empty( $query->get( 'suppress_filters' ) ) && empty( $query->get( 'disable_wpes' ) ) && ! $this->is_bbPress_search() ) {

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return isset( $_REQUEST['wpessid'] ); // Only alter REST query results when wpessid is set.
			}

			return true;
		}

		return false;
	}
}
