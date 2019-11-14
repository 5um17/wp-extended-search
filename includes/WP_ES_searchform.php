<?php
/**
 * WPES Search forms
 *
 * @author 5um17
 */
class WP_ES_searchform {
    
    private $form_default_args = false;


    public function __construct() {
	$this->form_default_args = array(
	    'wpessid' => false,
	    'submit_button_label' => esc_attr_x('Search', 'submit button'),
	    'input_box_placeholder' => esc_attr_x('Search &hellip;', 'placeholder'),
	    'search_form_css_class' => '',
	    'search_button_css_class' => '',
	    'search_input_css_class' => '',
	    'aria_label' => ''
	);
	
	add_shortcode( 'wpes_search_form', array( $this, 'register_search_form_shortcode' ) );
    }
    
    public function __get( $name ) {
	if ( isset( $this->$name ) ) {
	    return $this->$name;
	}
	
	return false;
    }

    public function get_form_default_args( $get_default_value = false ) {
	$args_desc = array(
	    'wpessid' => __( 'Search setting ID', 'wp-extended-search' ),
	    'submit_button_label' => __( 'Label of search button', 'wp-extended-search' ),
	    'input_box_placeholder' => __( 'Placeholder value for search input box', 'wp-extended-search' ),
	    'search_form_css_class' => __( 'CSS class names on search form element', 'wp-extended-search' ),
	    'search_button_css_class' => __( 'CSS class names on search button element', 'wp-extended-search' ),
	    'search_input_css_class' => __( 'CSS class names on search input element', 'wp-extended-search' ),
	    'aria_label' => __( 'ARIA label for the search form', 'wp-extended-search' )
	);
	
	if ( !empty( $get_default_value ) ) {
	    return isset( $this->form_default_args[ $get_default_value ] ) ? $this->form_default_args[ $get_default_value ] : false;
	}
	
	return $args_desc;
    }

    public function register_search_form_shortcode( $atts ) {
	$atts = shortcode_atts( $this->form_default_args, $atts, 'wpes_search_form' );
	return $this->get_search_form( $atts );
    }

    public function get_search_form( $args = array() ) {
	
	$args = wp_parse_args( $args, $this->form_default_args );
	
	/**
	 * @link https://core.trac.wordpress.org/browser/tags/5.2/src/wp-includes/general-template.php#L171
	 */
	do_action('pre_get_search_form');

	$search_form_template = locate_template('searchform.php');
	if ('' != $search_form_template) {
	    ob_start();
	    require( $search_form_template );
	    $form = ob_get_clean();
	} else {
	    // Build a string containing an aria-label to use for the search form.
	    if (isset($args['aria_label']) && $args['aria_label']) {
		$aria_label = 'aria-label="' . esc_attr($args['aria_label']) . '" ';
	    } else {
		/*
		 * If there's no custom aria-label, we can set a default here. At the
		 * moment it's empty as there's uncertainty about what the default should be.
		 */
		$aria_label = '';
	    }
	    
	    $form = '<form ' . $this->get_form_id_attr( $args['wpessid'] ) . ' role="search" ' . $aria_label . 'method="get" class="search-form ' . $args['search_form_css_class'] . '" action="' . esc_url(home_url('/')) . '">
		<label>
		    <span class="screen-reader-text">' . _x('Search for:', 'label') . '</span>
		    <input type="search" class="search-field ' . $args['search_input_css_class'] . '" placeholder="' . $args['input_box_placeholder'] . '" value="' . get_search_query() . '" name="s" />
		</label>
		<input type="submit" class="search-submit ' . $args['search_button_css_class'] . '" value="' . $args['submit_button_label'] . '" />' .
		$this->get_wpessid_hidden_field( $args['wpessid'] ) .
	    '</form>';
	}

	/**
	 * @link https://core.trac.wordpress.org/browser/tags/5.2/src/wp-includes/general-template.php#L171
	 */
	$result = apply_filters('get_search_form', $form);

	if (null === $result) {
	    $result = $form;
	}

	return $result;
    }
    
    public function get_wpessid_hidden_field( $wpessid = false ) {
	if ( !empty( $wpessid ) ) {
	    return "<input type='hidden' value='$wpessid' name='wpessid' />";
	}
	
	return '';
    }
    
    public function get_form_id_attr( $wpessid = false ) {
	if ( !empty( $wpessid ) ) {
	    return "id='wpes-form-$wpessid'";
	}
	
	return '';
    }
}
