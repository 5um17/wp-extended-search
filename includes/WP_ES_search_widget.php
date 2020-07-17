<?php
/**
 * WPES search form widget
 *
 * @author 5um17
 */
class WP_ES_search_widget extends WP_Widget {
    
    public function __construct() {
	$options = array(
	    'classname' => 'wp_es_search_form_widget',
	    'description' => __( 'WP Extended Search Form', 'wp-extended-search' ),
	);
	
	parent::__construct( false , 'WPES ' . __( 'Search Form', 'wp-extended-search' ), $options );
    }
    
    private function translate_strings( $instance, $register = false ) {
	$translatable_keys = array(
	    'submit_button_label'   =>	'Search Button Label',
	    'input_box_placeholder' =>	'Search Bar Placeholder',
	    'aria_label'	    =>	'Form Aria Label'
	);
	
	foreach ( $translatable_keys as $key => $string_name ) {
	    if ( !empty( $instance[ $key ] ) ) {
		if ( !empty( $register ) ) {
		    do_action( 'wpml_register_single_string', 'Widgets', $string_name, $instance[ $key ] );
		} else {
		    $instance[ $key ] = apply_filters( 'wpml_translate_single_string', $instance[ $key ], 'Widgets', $string_name );
		}
	    }
	}
	
	return $instance;
    }

    public function widget( $args, $instance ) {
	
	if ( !empty( WPES()->current_setting_id ) && WPES()->current_setting_id != $instance['wpessid'] ) {
	    return '';
	}
	
	if ( WPES()->is_WPML_active( 'ST' ) ) {
	    $instance = $this->translate_strings( $instance );
	}
	
	$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

	/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
	$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

	echo $args['before_widget'];

	if ( $title ) {
	    echo $args['before_title'] . $title . $args['after_title'];
	}

	WPES_search_form( $instance );

	echo $args['after_widget'];
	
    }
    
    public function update( $new_instance, $old_instance ) {
	$instance = $old_instance;
	$instance['title'] = sanitize_text_field( $new_instance['title'] );
	
	foreach ( WPES()->WP_ES_searchform->get_form_default_args() as $key => $key_desc ) {
	    if ( $key === 'wpessid' && get_post_type( intval( $new_instance[ $key ] ) ) === 'wpes_setting' ) {
		$instance[ $key ] = intval( $new_instance[ $key ] );
	    }
	    $instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
	}
	
	if ( WPES()->is_WPML_active( 'ST' ) ) {
	    $this->translate_strings( $instance, true );
	}

	return $instance;
    }
    
    public function form( $instance ) {
	$instance = wp_parse_args( (array) $instance, array_merge( WPES()->WP_ES_searchform->form_default_args, array( 'title' => '' ) ) );
	$all_settings = WPES()->WP_ES_admin->get_all_setting_names(); ?>

	<p>
	    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
	</p><?php
	
	foreach ( WPES()->WP_ES_searchform->get_form_default_args() as $key => $key_desc ) {
	    if ( $key === 'wpessid' ) {
		continue;
	    } ?>
	    <p>
		<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $key_desc; ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $instance[ $key ] ); ?>" />
	    </p><?php
	} ?>
	    
	<p>
	    <label for="<?php echo $this->get_field_id('wpessid'); ?>"><?php _e('Setting Name:'); ?></label>
	    <select class="widefat" id="<?php echo $this->get_field_id('wpessid'); ?>" name="<?php echo $this->get_field_name('wpessid'); ?>">
		<option value=""><?php _e('Global (default)', 'wp-extended-search'); ?></option><?php
		foreach ($all_settings as $setting_name) { ?>
		    <option <?php selected( $setting_name->ID, $instance['wpessid'] ); ?> value="<?php echo $setting_name->ID; ?>"><?php echo get_the_title($setting_name); ?></option><?php
		} ?>
	    </select>
	</p>
	<p class="help"><?php printf( __( 'To add new setting click <a href="%s">here</a>.', 'wp-extended-search' ), admin_url('post-new.php?post_type=wpes_setting') ); ?></p><?php
    }
}