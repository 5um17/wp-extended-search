<?php
/**
 * Admin class of WP Extened Search
 *
 * @author 5um17
 */
class WP_ES_admin {
    
    /**
     * Default Constructor
     * @since 1.0
     */
    public function __construct(){
        
        add_action('admin_menu', array($this, 'WP_ES_admin_add_page'));
        add_action('admin_init', array($this, 'WP_ES_admin_init'));
        
        add_action('admin_enqueue_scripts', array($this, 'WP_ES_admin_scripts'));
        
        add_filter( 'plugin_row_meta', array($this, 'plugin_links'), 10, 2 );
	add_filter( 'plugin_action_links_' . WP_ES_Filename, array($this, 'plugin_action_links'));
    }

    /**
     * Add Admin page
     * @since 1.0
     */
    public function WP_ES_admin_add_page(){
        add_options_page('WP Extended Search Settings', 'Extended Search', 'manage_options', 'wp-es', array($this, 'wp_es_page'));
    }

    /**
     * Print admin page content
     * @since 1.0
     */
    public function wp_es_page(){ ?>
        <div class="wrap">
            
            <h2>WP Extended Search <?php _e('Settings', 'wp-extended-search'); ?></h2>

            <form method="post" action="options.php"><?php
                settings_fields('wp_es_option_group');	
                do_settings_sections('wp-es');
                submit_button(__('Save Changes'), 'primary', 'submit', false);
                echo '&nbsp;&nbsp;';
                submit_button(__('Reset to WP default'), 'secondary', 'reset', false); ?>
            </form>
            
        </div><?php
    }

    /**
     * Add Section settings and settings fields
     * @since 1.0
     */
    public function WP_ES_admin_init(){

        /* Register Settings */
        register_setting('wp_es_option_group', 'wp_es_options', array($this, 'wp_es_save'));

        /* Add Section */
        add_settings_section( 'wp_es_section_1', __('Select Fields to include in WordPress default Search', 'wp-extended-search' ), array($this, 'wp_es_section_content'), 'wp-es' );	
        add_settings_section( 'wp_es_section_misc', __('Miscellaneous Settings', 'wp-extended-search' ), array($this, 'wp_es_section_content_misc'), 'wp-es' );

        /* Add fields */
        add_settings_field( 'wp_es_title_and_post_content', __('General Search Setting', 'wp-extended-search'), array($this, 'wp_es_title_content_checkbox'), 'wp-es', 'wp_es_section_1' );
        add_settings_field( 'wp_es_list_custom_fields', __('Select Meta Key Names' , 'wp-extended-search'), array($this, 'wp_es_custom_field_name_list'), 'wp-es', 'wp_es_section_1' );
        add_settings_field( 'wp_es_list_taxonomies', __('Select Taxonomies' , 'wp-extended-search'), array($this, 'wp_es_taxonomies_settings'), 'wp-es', 'wp_es_section_1' );
        add_settings_field( 'wp_es_include_authors', __('Author Setting' , 'wp-extended-search'), array($this, 'wp_es_author_settings'), 'wp-es', 'wp_es_section_1' );
        add_settings_field( 'wp_es_list_post_types', __('Select Post Types' , 'wp-extended-search'), array($this, 'wp_es_post_types_settings'), 'wp-es', 'wp_es_section_1' );
        add_settings_field( 'wp_es_terms_relation_type', __('Terms Relation Type' , 'wp-extended-search'), array($this, 'wp_es_terms_relation_type'), 'wp-es', 'wp_es_section_misc', array('label_for' => 'es_terms_relation') );
	add_settings_field( 'wp_es_exact_search', __('Match the search term exactly' , 'wp-extended-search'), array($this, 'wp_es_exact_search'), 'wp-es', 'wp_es_section_misc' );
        add_settings_field( 'wp_es_exclude_older_results', __('Select date to exclude older results' , 'wp-extended-search'), array($this, 'wp_es_exclude_results'), 'wp-es', 'wp_es_section_misc', array('label_for' => 'es_exclude_date') );
        add_settings_field( 'wp_es_number_of_posts', __('Posts per page' , 'wp-extended-search'), array($this, 'wp_es_posts_per_page'), 'wp-es', 'wp_es_section_misc', array('label_for' => 'es_posts_per_page') );    
        add_settings_field( 'wp_es_search_results_order', __('Search Results Order' , 'wp-extended-search'), array($this, 'wp_es_search_results_order'), 'wp-es', 'wp_es_section_misc', array('label_for' => 'es_search_results_order') );
    }
    
    /**
     * enqueue admin style and scripts
     * @since 1.0
     */
    public function WP_ES_admin_scripts($hook) {
        if ($hook == 'settings_page_wp-es') {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('wpes_jquery_ui', WP_ES_URL . 'assets/css/jQueryUI/jquery-ui.min.css');
            wp_enqueue_style('wpes_jquery_ui_theme', WP_ES_URL . 'assets/css/jQueryUI/jquery-ui.theme.min.css');
            wp_enqueue_style('wpes_admin_css', WP_ES_URL . 'assets/css/wp-es-admin.css');
        }
    }

    /**
     * Get all meta keys
     * @since 1.0
     * @global Object $wpdb WPDB object
     * @return Array array of meta keys
     */
    public function wp_es_fields() {
        global $wpdb;
        /**
         * Filter query for meta keys in admin options
         * @since 1.0.1
         * @param string SQL query
         */
        $wp_es_fields = $wpdb->get_results(apply_filters('wpes_meta_keys_query', "select DISTINCT meta_key from $wpdb->postmeta where meta_key NOT LIKE '\_%' ORDER BY meta_key ASC"));
        $meta_keys = array();

        if (is_array($wp_es_fields) && !empty($wp_es_fields)) {
            foreach ($wp_es_fields as $field){
                if (isset($field->meta_key)) {
                    $meta_keys[] = $field->meta_key;
                }
            }
        }
        
        /**
         * Filter results of SQL query for meta keys
         * @since 1.1
         * @param array $meta_keys array of meta keys
         */
        return apply_filters('wpes_meta_keys', $meta_keys);
    }

    /**
     * Validate input settings
     * @since 1.0
     * @global object $WP_ES Main class object
     * @param array $input input array by user
     * @return array validated input for saving
     */
    public function wp_es_save($input){
        global $WP_ES;
        $settings = $WP_ES->WP_ES_settings;
        
        if (isset($_POST['reset'])) {
            add_settings_error('wp_es_error', 'wp_es_error_reset', __('Settings has been changed to WordPress default search setting.', 'wp-extended-search'), 'updated');
            return $WP_ES->default_options();
        }
        
        if (!isset($input['post_types']) || empty($input['post_types'])) {
            add_settings_error('wp_es_error', 'wp_es_error_post_type', __('Select atleast one post type!', 'wp-extended-search'));
            return $settings;
        }
        
        if (empty($input['title']) && empty($input['content']) && empty($input['excerpt']) && empty($input['meta_keys']) && empty($input['taxonomies']) && empty($input['authors'])) {
            add_settings_error('wp_es_error', 'wp_es_error_all_empty', __('Select atleast one setting to search!', 'wp-extended-search'));
            return $settings;   
        }
        
        if (!empty($input['exclude_date']) && !strtotime($input['exclude_date'])) {
            add_settings_error('wp_es_error', 'wp_es_error_invalid_date', __('Date seems to be in invalid format!', 'wp-extended-search'));
            return $settings;
        }
        
        return $input;
    }

    /**
     * Section content before displaying search settings
     * @since 1.0
     */
    public function wp_es_section_content(){ ?>
        <em><?php _e('Every field have OR relation with each other. e.g. if someone search for "5um17" then search results will show those items which have "5um17" as meta value or taxonomy\'s term or in title or in content, whatever option is selected.', 'wp-extended-search'); ?></em><?php
    }
    
    /**
     * Section content before displaying Miscellaneous Settings
     * @since 1.1
     */
    public function wp_es_section_content_misc() {
        //to be used in futrue
    }

    /**
     * Default settings checkbox
     * @since 1.0
     * @global object $WP_ES
     */
    public function wp_es_title_content_checkbox(){ 
        global $WP_ES;
        $settings = $WP_ES->WP_ES_settings; ?>

        <input type="hidden" name="wp_es_options[title]" value="0" />
        <input <?php checked($settings['title']); ?> type="checkbox" id="estitle" name="wp_es_options[title]" value="1" />&nbsp;
        <label for="estitle"><?php _e('Search in Title', 'wp-extended-search'); ?></label>
        <br />
        <input type="hidden" name="wp_es_options[content]" value="0" />
        <input <?php checked($settings['content']); ?> type="checkbox" id="escontent" name="wp_es_options[content]" value="1" />&nbsp;
        <label for="escontent"><?php _e('Search in Content', 'wp-extended-search'); ?></label>
        <br />
        <input type="hidden" name="wp_es_options[excerpt]" value="0" />
        <input <?php checked($settings['excerpt']); ?> type="checkbox" id="esexcerpt" name="wp_es_options[excerpt]" value="1" />&nbsp;
        <label for="esexcerpt"><?php _e('Search in Excerpt', 'wp-extended-search'); ?></label><?php
    }

    /**
     * Meta keys checkboxes
     * @since 1.0
     * @global object $WP_ES
     */
    public function wp_es_custom_field_name_list() {
        global $WP_ES;

        $meta_keys = $this->wp_es_fields();
        if (!empty($meta_keys)) { ?>
            <div class="wpes-meta-keys-wrapper"><?php
                foreach ((array)$meta_keys as $meta_key) { ?>
                    <p>
                        <input <?php echo $this->wp_es_checked($meta_key, $WP_ES->WP_ES_settings['meta_keys']); ?> type="checkbox" id="<?php echo $meta_key; ?>" name="wp_es_options[meta_keys][]" value="<?php echo $meta_key; ?>" />
                        <label for="<?php echo $meta_key; ?>"><?php echo $meta_key; ?></label>&nbsp;&nbsp;&nbsp;
                    </p><?php
                } ?>
            </div><?php
        } else { ?>
            <em><?php _e('No meta key found!', 'wp-extended-search'); ?></em><?php
        }

    }
    
    /**
     * Taxonomies checboxes
     * @since 1.0
     * @global object $WP_ES
     */
    public function wp_es_taxonomies_settings() {
        global $WP_ES;
        
        /**
         * Filter taxonomies arguments
         * @since 1.0.1
         * @param array arguments array
         */
        $tax_args = apply_filters('wpes_tax_args', array(
            'show_ui' => TRUE,
            'public' => TRUE
        ));
        
        /**
         * Filter taxonomy list return by get_taxonomies function
         * @since 1.1
         * @param $all_taxonomies Array of taxonomies
         */
        $all_taxonomies = apply_filters('wpes_tax', get_taxonomies($tax_args, 'objects'));
        if (is_array($all_taxonomies) && !empty($all_taxonomies)) {
            foreach ($all_taxonomies as $tax_name => $tax_obj) { ?>
                <input <?php echo $this->wp_es_checked($tax_name, $WP_ES->WP_ES_settings['taxonomies']); ?> type="checkbox" value="<?php echo $tax_name; ?>" id="<?php echo 'wp_es_' . $tax_name; ?>" name="wp_es_options[taxonomies][]" />&nbsp;
                <label for="<?php echo 'wp_es_' . $tax_name; ?>"><?php echo !empty($tax_obj->labels->name) ? $tax_obj->labels->name : $tax_name; ?></label><br /><?php
            }
        } else { ?>
            <em><?php _e('No public taxonomy found!', 'wp-extended-search'); ?></em><?php
        }
    }
    
    /**
     * Author settings meta box
     * @since 1.1
     * @global object $WP_ES
     */
    public function wp_es_author_settings() {
        global $WP_ES; ?>
        <input name="wp_es_options[authors]" type="hidden" value="0" />
        <input id="wpes_inlcude_authors" <?php checked($WP_ES->WP_ES_settings['authors']); ?> type="checkbox" value="1" name="wp_es_options[authors]" />
        <label for="wpes_inlcude_authors"><?php _e('Search in Author display name', 'wp-extended-search'); ?></label>
        <p class="description"><?php _e('If checked then it will display those results whose Author "Display name" match the search terms.', 'wp-extended-search'); ?></p><?php
    }

    /**
     * Post type checkboexes
     * @since 1.0
     * @global object $WP_ES
     */
    public function wp_es_post_types_settings() {
        global $WP_ES;

        /**
         * Filter post type arguments
         * @since 1.0.1
         * @param array arguments array
         */
        $post_types_args = apply_filters('wpes_post_types_args', array(
            'show_ui' => TRUE,
            'public' => TRUE
        ));
        
        /**
         * Filter post type array return by get_post_types function
         * @since 1.1
         * @param array $all_post_types Array of post types
         */
        $all_post_types = apply_filters('wpes_post_types', get_post_types($post_types_args, 'objects'));
        
        if (is_array($all_post_types) && !empty($all_post_types)) {
            foreach ($all_post_types as $post_name => $post_obj) { ?>
                <input <?php echo $this->wp_es_checked($post_name, $WP_ES->WP_ES_settings['post_types']); ?> type="checkbox" value="<?php echo $post_name; ?>" id="<?php echo 'wp_es_' . $post_name; ?>" name="wp_es_options[post_types][]" />&nbsp;
                <label for="<?php echo 'wp_es_' . $post_name; ?>"><?php echo isset($post_obj->labels->name) ? $post_obj->labels->name : $post_name; ?></label><br /><?php
            }
        } else { ?>
            <em><?php _e('No public post type found!', 'wp-extended-search'); ?></em><?php
        }
    }
    
    /**
     * Terms relation type meta box
     * @since 1.1
     * @global object $WP_ES
     */
    public function wp_es_terms_relation_type() {
        global $WP_ES; ?>
	<select <?php echo $this->wp_es_disabled( $WP_ES->WP_ES_settings['exact_match'] , 'yes' ); ?> id="es_terms_relation" name="wp_es_options[terms_relation]">
            <option <?php selected($WP_ES->WP_ES_settings['terms_relation'], 1); ?> value="1"><?php _e('AND', 'wp-extended-search'); ?></option>
            <option <?php selected($WP_ES->WP_ES_settings['terms_relation'], 2); ?> value="2"><?php _e('OR', 'wp-extended-search'); ?></option>
        </select>
        <p class="description"><?php
	    if ( $WP_ES->WP_ES_settings['exact_match'] == 'yes' ) {
		_e('This option is disabled because you have selected "Match the search term exactly".  When using the exact match option, the sentence is not broken into terms instead the whole sentence is matched thus this option has no meaning.', 'wp-extended-search');
	    } else {
		_e('Type of query relation between search terms. e.g. someone searches for "my query" then define the relation between "my" and "query". The default value is AND.', 'wp-extended-search');
	    } ?>
	</p><?php
    }
    
    /**
     * Exclude older results
     * @since 1.0.2
     * @global object $WP_ES
     */
    public function wp_es_exclude_results() {
        global $WP_ES; ?>
        <script type="text/javascript">jQuery(document).ready(function (){ jQuery('#es_exclude_date').datepicker({ maxDate: new Date(), changeYear: true, dateFormat: "MM dd, yy" }); });</script>
        <input class="regular-text" type="text" value="<?php echo esc_attr($WP_ES->WP_ES_settings['exclude_date']); ?>" name="wp_es_options[exclude_date]" id="es_exclude_date" />
        <p class="description"><?php _e('Contents will not appear in search results older than this date OR leave blank to disable this feature.', 'wp-extended-search'); ?></p><?php
    }
    
    /**
     * Posts per search results page
     * @since 1.1
     * @global object $WP_ES
     */
    public function wp_es_posts_per_page() {
        global $WP_ES; ?>
        <input min="-1" class="small-text" type="number" value="<?php echo esc_attr($WP_ES->WP_ES_settings['posts_per_page']); ?>" name="wp_es_options[posts_per_page]" id="es_posts_per_page" />
        <p class="description"><?php _e('Number of posts to display on search result page OR leave blank for default value.', 'wp-extended-search'); ?></p><?php
    }
    
    /**
     * Search results order
     * @since 1.3
     * @global object $WP_ES
     */
    public function wp_es_search_results_order() { 
	global $WP_ES; ?>
	<select id="es_search_results_order" name="wp_es_options[orderby]">
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], ''); ?> value=""><?php _e('Relevance', 'wp-extended-search'); ?></option>
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], 'date'); ?> value="date"><?php _e('Date', 'wp-extended-search'); ?></option>
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], 'modified'); ?> value="modified"><?php _e('Last Modified Date', 'wp-extended-search'); ?></option>
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], 'title'); ?> value="title"><?php _e('Post Title', 'wp-extended-search'); ?></option>
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], 'name'); ?> value="name"><?php _e('Post Slug', 'wp-extended-search'); ?></option>
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], 'type'); ?> value="type"><?php _e('Post Type', 'wp-extended-search'); ?></option>
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], 'comment_count'); ?> value="comment_count"><?php _e('Number of Comments', 'wp-extended-search'); ?></option>
	    <option <?php selected($WP_ES->WP_ES_settings['orderby'], 'rand'); ?> value="rand"><?php _e('Random', 'wp-extended-search'); ?></option>
	</select>
	<p class="description"><?php echo sprintf(__('Sort search results based on metadata of items. The default value is %1$sRelevance%2$s.', 'wp-extended-search'), '<a href="https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters">', '</a>'); ?></p>
	<br />
	<label><input <?php echo $this->wp_es_checked($WP_ES->WP_ES_settings['order'], array('DESC')); ?> type="radio" value="DESC" name="wp_es_options[order]" /><?php _e('Descending', 'wp-extended-search') ?></label>
	<label><input <?php echo $this->wp_es_checked($WP_ES->WP_ES_settings['order'], array('ASC')); ?> type="radio" value="ASC" name="wp_es_options[order]" /><?php _e('Ascending', 'wp-extended-search') ?></label>
	<p class="description"><?php _e('Order the sorted search items in Descending or Ascending. Default is Descending.', 'wp-extended-search'); ?></p><?php
    }
    
    /**
     * Select exact or partial term matching
     * @since 1.3
     * @global object $WP_ES
     */
    public function wp_es_exact_search() {
	global $WP_ES; ?>
	<label><input <?php echo $this->wp_es_checked($WP_ES->WP_ES_settings['exact_match'], array('yes')); ?> type="radio" value="yes" name="wp_es_options[exact_match]" /><?php _e('Yes', 'wp-extended-search'); ?></label>
	<label><input <?php echo $this->wp_es_checked($WP_ES->WP_ES_settings['exact_match'], array('no')); ?> type="radio" value="no" name="wp_es_options[exact_match]" /><?php _e('No', 'wp-extended-search'); ?></label>
	<p class="description"><?php _e('Whether to match search term exactly or partially e.g. If someone search "Word" it will display items matching "WordPress" or "Word" but if you select Yes then it will display items only matching "Word". The default value is No.', 'wp-extended-search'); ?></p><?php
    
    }

    /**
     * return checked if value exist in array
     * @since 1.0
     * @param mixed $value value to check against array
     * @param array $array haystack array
     * @return string checked="checked" or blank string
     */
    public function wp_es_checked($value = false, $array = array()) {
        if (in_array($value, $array, true)) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        
        return $checked;
    }
    
    /**
     * Return disabled if both values are equal
     * @since 1.3
     * @param mixed $first_value First value to compare
     * @param mixed $second_value Second value to compare
     * @return string disabled="disabled" or blank string
     */
    public function wp_es_disabled( $first_value, $second_value = true ) {
	if ( $first_value == $second_value ) {
	    return 'disabled="disabled"';
	}
	
	return '';
    }

    /**
     * Add docs and other links to plugin row meta
     * @since 1.2
     * @param array $links The array having default links for the plugin
     * @param string $file The name of the plugin file
     * @return array $links array with newly added links
     */
    public function plugin_links($links, $file) {
	if ( $file !== WP_ES_Filename ) {
            return $links;
        }
        
        if (is_array($links)) {
            $links[] = '<a href="https://www.secretsofgeeks.com/2014/09/wordpress-search-tags-and-categories.html" target="_blank">'
                    . __('Docs', 'wp-extended-search')
                    . '</a>';
            $links[] = '<a href="https://wordpress.org/plugins/search/5um17/" target="_blank">'
                    . __('More Plugins', 'wp-extended-search')
                    . '</a>';
        }
	return $links;
    }
    
    /**
     * Add setting link to plugin action list.
     * @since 1.3
     * @param array $links action links
     * @return array $links new action links
     */
    public function plugin_action_links( $links ) {
	if ( is_array( $links ) ) {
            $links[] = '<a href="' . admin_url( 'options-general.php?page=wp-es' ) . '">'
                    . __( 'Settings', 'wp-extended-search' )
                    . '</a>';
        }
	
	return $links;
    }
}