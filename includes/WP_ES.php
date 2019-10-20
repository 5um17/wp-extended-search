<?php
/**
 * Main class of WP Extened Search
 *
 * @author 5um17
 */
class WP_ES {

    /* Defaults Variable */
    public $WP_ES_settings = '';

    /**
     * Default Constructor
     * @since 1.0
     */
    public function __construct() {
        
        $this->WP_ES_settings = $this->wp_es_options();
        
        if ( !is_admin() || (defined('DOING_AJAX') && DOING_AJAX && !$this->wp_core_actions()) ) {
            //Only filter non admin requests!
            add_action('init', array($this,'wp_es_init'));
        }
        
        add_action('plugins_loaded', array($this, 'wp_es_plugin_loaded'));
    }
    
    /**
     * Get Defualt options
     * @since 1.0
     */
    public function default_options() {
        $settings = array(
                'title'             =>  true,
                'content'           =>  true,
                'excerpt'           =>  true,
                'meta_keys'         =>  array(),
                'taxonomies'        =>  array(),
                'authors'           =>  false,
                'post_types'        =>  array('post', 'page'),
                'exclude_date'      => '',
                'posts_per_page'    => '',
                'terms_relation'    => 1,
                'orderby'	    => '',
                'order'		    => 'DESC',
                'exact_match'	    => 'no'
            );
        return $settings;
    }

    /**
     * Get plugin options
     * @since 1.0
     */
    public function wp_es_options() {
        $db_settings = get_option('wp_es_options');
        $settings = wp_parse_args($db_settings, $this->default_options());
        return $settings;
    }
    
    /**
     * Load plugin text domain
     * @since 1.0.1
     */
    public function wp_es_plugin_loaded() {
        load_plugin_textdomain( 'wp-extended-search', false, dirname( plugin_basename( WP_ES_DIR . 'wp-es.php' ) ) . '/languages' );
    }

    /**
     * Init function
     * @since 1.0
     * @return NULL
     */
    public function wp_es_init() {
        
        /**
         * Filter plugin's all action hooks to enabled or disabled
         * @since 1.0.1
         * @param bool true to enable or false to disable
         */
        if (!apply_filters('wpes_enabled', TRUE)) {
            return;
        }
        
        /* Filter to modify search query */
        add_filter( 'posts_search', array($this, 'wp_es_custom_query'), 500, 2 );
        
        /* Action for modify query arguments */
        add_action( 'pre_get_posts' , array($this, 'wp_es_pre_get_posts'), 500);
    }
    
    /**
     * Add post type in where clause of wp query
     * @since 1.0
     * @param object $query wp_query object
     */
    public function wp_es_pre_get_posts($query) {
        if (!empty($query->is_search) && !$this->is_bbPress_search()) {
	    
	    //Set post types
            if (!empty($this->WP_ES_settings['post_types'])) {
                if (isset($_GET['post_type']) && in_array(esc_attr($_GET['post_type']), (array) $this->WP_ES_settings['post_types'])) {
                    $query->query_vars['post_type'] = (array) esc_attr($_GET['post_type']);
                } else {
                    $query->query_vars['post_type'] = (array) $this->WP_ES_settings['post_types'];
                }
            }
	    
	    //Set date query to exclude resutls
            if (!empty($this->WP_ES_settings['exclude_date'])) {
                $query->set('date_query', array(
                    array(
                        'after' => $this->WP_ES_settings['exclude_date']
                        )
                ));
            }
	    
	    //Set posts page page
            $posts_per_page = intval($this->WP_ES_settings['posts_per_page']); //Putting in extra line just to get rid off from WP svn pre-commit hook error.
            if (!empty($posts_per_page)) {
                $query->set('posts_per_page', $posts_per_page);
            }
	    
	    //If searching for attachment type then set post status to inherit
	    if ( is_array( $query->get( 'post_type' ) ) && in_array( 'attachment', $query->get( 'post_type' ) ) ) {
		$query->set( 'post_status', array( 'publish', 'inherit' ) );
		if ( is_user_logged_in() ) {
		    $query->set( 'post_status', array( 'publish', 'inherit', 'private' ) );
		    $query->set( 'perm', 'readable' ); //Check if current user can read private posts
		}
	    }
	    
	    //Set orderby
	    if ( !empty( $this->WP_ES_settings['orderby'] ) ) {
		$query->set( 'orderby', $this->WP_ES_settings[ 'orderby' ] );
	    }
	    
	    //Set results order
	    if ( in_array( $this->WP_ES_settings['order'], array( 'DESC', 'ASC' ), true ) && $this->WP_ES_settings['order'] !== 'DESC' ) {
		$query->set( 'order', $this->WP_ES_settings[ 'order' ] );
	    }
	    
	    //Set exact match
	    if ( $this->WP_ES_settings[ 'exact_match' ] == 'yes' ) {
		$query->set( 'exact', true );
		$query->set( 'sentence', true );
	    }
        }
    }

    /**
     * Core function return the custom query
     * @since 1.0
     * @global Object $wpdb WordPress db object
     * @param string $search Search query
     * @param object $wp_query WP query
     * @return string $search Search query
     */
    public function wp_es_custom_query( $search, $wp_query ) {
        global $wpdb;
        
        if ( empty( $search ) || !empty($wp_query->query_vars['suppress_filters']) ) {
            return $search; // skip processing - If no search term in query or suppress_filters is true
        }
        
        $q = $wp_query->query_vars;
        $n = !empty($q['exact']) ? '' : '%';
        $search = $searchand = '';
        
        /**
         * Filter the term relation type OR/AND
         * @since 1.2
         * @param NULL
         */
        $terms_relation_type = apply_filters('wp_es_terms_relation_type', NULL);
        
        if (!in_array($terms_relation_type, array('AND', 'OR'), TRUE)) {
            $terms_relation_type = (intval($this->WP_ES_settings['terms_relation']) === 2) ? 'OR' : 'AND';
        }
        
        foreach ((array)$q['search_terms'] as $term ) {
            
            $term = $n . $wpdb->esc_like( $term ) . $n;

            /* change query as per plugin settings */
            $OR = '';
            if (!empty($this->WP_ES_settings)) {
                $search .= "{$searchand} (";
                
                // if post title search is enabled
                if (!empty($this->WP_ES_settings['title'])) {
                    $search .= $wpdb->prepare("($wpdb->posts.post_title LIKE '%s')", $term);
                    $OR = ' OR ';
                }
                
                //if content search is enabled
                if (!empty($this->WP_ES_settings['content'])) {
                    $search .= $OR;
                    $search .= $wpdb->prepare("($wpdb->posts.post_content LIKE '%s')", $term);
                    $OR = ' OR ';
                }
                
                //if excerpt search is enabled
                if (!empty($this->WP_ES_settings['excerpt'])) {
                    $search .= $OR;
                    $search .= $wpdb->prepare("($wpdb->posts.post_excerpt LIKE '%s')", $term);
                    $OR = ' OR ';
                }

                // if post meta search is enabled
                if (!empty($this->WP_ES_settings['meta_keys'])) {
                    $meta_key_OR = '';

                    foreach ($this->WP_ES_settings['meta_keys'] as $key_slug) {
                        $search .= $OR;
                        $search .= $wpdb->prepare("$meta_key_OR (espm.meta_key = '%s' AND espm.meta_value LIKE '%s')", $key_slug, $term);
                        $OR = '';
                        $meta_key_OR = ' OR ';
                    }
                    
                    $OR = ' OR ';
                }
                
                // if taxonomies search is enabled
                if (!empty($this->WP_ES_settings['taxonomies'])) {
                    $tax_OR = '';
                    
                    foreach ($this->WP_ES_settings['taxonomies'] as $tax) {
                        $search .= $OR;
                        $search .= $wpdb->prepare("$tax_OR (estt.taxonomy = '%s' AND est.name LIKE '%s')", $tax, $term);
                        $OR = '';
                        $tax_OR = ' OR ';
                    }
                    
                    $OR = ' OR ';
                }
                
                // If authors search is enabled
                if (!empty($this->WP_ES_settings['authors'])) {
                    $search .= $OR;
                    $search .= $wpdb->prepare("(esusers.display_name LIKE '%s')", $term);
                }
                
                $search .= ")";
            } else {
                // If plugin settings not available return the default query
                $search .= $wpdb->prepare("{$searchand} (($wpdb->posts.post_title LIKE '%s') OR ($wpdb->posts.post_content LIKE '%s') OR ($wpdb->posts.post_excerpt LIKE '%s'))", $term, $term, $term);
            }

            $searchand = " $terms_relation_type ";
        }

        if ( ! empty( $search ) ) {
            $search = " AND ({$search}) ";
            if ( ! is_user_logged_in() )
                $search .= " AND ($wpdb->posts.post_password = '') ";
        }
        
        /* Join Table */
        add_filter('posts_join_request', array($this, 'wp_es_join_table'));

        /* Request distinct results */
        add_filter('posts_distinct_request', array($this, 'WP_ES_distinct'));
        
        /**
         * Filter search query return by plugin
         * @since 1.0.1
         * @param string $search SQL query
         * @param object $wp_query global wp_query object
         */
        return apply_filters('wpes_posts_search', $search, $wp_query); // phew :P All done, Now return everything to wp.
    }
    
    /**
     * Join tables
     * @since 1.0
     * @global Object $wpdb WPDB object
     * @param string $join query for join
     * @return string $join query for join
     */
    public function wp_es_join_table($join){
        global $wpdb;
        
        //join post meta table
        if (!empty($this->WP_ES_settings['meta_keys'])) {
            $join .= " LEFT JOIN $wpdb->postmeta espm ON ($wpdb->posts.ID = espm.post_id) ";
        }
        
        //join taxonomies table
        if (!empty($this->WP_ES_settings['taxonomies'])) {
            $join .= " LEFT JOIN $wpdb->term_relationships estr ON ($wpdb->posts.ID = estr.object_id) ";
            $join .= " LEFT JOIN $wpdb->term_taxonomy estt ON (estr.term_taxonomy_id = estt.term_taxonomy_id) ";
            $join .= " LEFT JOIN $wpdb->terms est ON (estt.term_id = est.term_id) ";
        }
        
        // Joint the users table
        if (!empty($this->WP_ES_settings['authors'])) {
            $join .= " LEFT JOIN $wpdb->users esusers ON ($wpdb->posts.post_author = esusers.ID) ";
        }
        
        return $join;
    }

    /**
     * Request distinct results
     * @since 1.0
     * @param string $distinct
     * @return string $distinct
     */
    public function WP_ES_distinct($distinct) {
        $distinct = 'DISTINCT';
        return $distinct;
    }
    
    /**
     * Check if it is WordPress core Ajax action
     * @since 1.1.2
     * @return boolean TRUE if it core Ajax request else false
     */
    public function wp_core_actions() {
        $wp_core_actions = array(
            'query-attachments',
	    'menu-quick-search'
        );
        
        $current_action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : false;
        
        if (in_array($current_action ,$wp_core_actions)) {
            return TRUE;
        }
        
        return FALSE;
    }
    
    /**
     * Check if it is bbPress page
     * @since 1.2
     * @return boolean TRUE if bbPress search else FALSE
     */
    public function is_bbPress_search() {
        if (function_exists('is_bbpress')) {
            return is_bbpress();
        }
        
        return FALSE;
    }
}