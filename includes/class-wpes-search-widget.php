<?php
/**
 * Register search widget.
 *
 * @package WPES/Classes
 * @author 5um17
 */

defined( 'ABSPATH' ) || exit();

/**
 * WPES search form widget class.
 */
class WPES_Search_Widget extends WP_Widget {
	/**
	 * String names to translate.
	 *
	 * @since 2.0
	 * @var array
	 */
	private $_translatable_keys = array(
		'submit_button_label'   => 'Search Button Label',
		'input_box_placeholder' => 'Search Bar Placeholder',
		'aria_label'            => 'Form Aria Label',
	);

	/**
	 * Widget constructor.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		$options = array(
			'classname'   => 'wp_es_search_form_widget',
			'description' => __( 'WP Extended Search Form', 'wp-extended-search' ),
		);

		parent::__construct( false, 'WPES ' . __( 'Search Form', 'wp-extended-search' ), $options );
	}

	/**
	 * Translate widget strings using WPML.
	 *
	 * @since 2.0
	 * @param object  $instance Widget instance.
	 * @param boolean $register Weather to register or translate.
	 * @return NULL
	 */
	private function maybe_translate_strings( $instance, $register = false ) {
		// Transalete only if WPML and ST are active.
		if ( WPES()->wpes_wpml instanceof WPES_WPML && WPES()->wpes_wpml->is_addon_active( 'ST' ) ) {
			foreach ( $this->_translatable_keys as $key => $string_name ) {
				if ( ! empty( $instance[ $key ] ) ) {
					$instance[ $key ] = WPES()->wpes_wpml->translate_or_register_string( 'Widgets', $instance[ $key ], $string_name . ' - ' . $this->number, $register );
				}
			}
		}

		return $instance;
	}

	/**
	 * Display widget method.
	 *
	 * @since 2.0
	 * @param array  $args Array of widget arguments.
	 * @param object $instance Widget instance.
	 * @return NULL
	 */
	public function widget( $args, $instance ) {
		// Returns blank string if current setting ID does belong to widget.
		if ( ! empty( WPES()->current_setting_id ) && WPES()->current_setting_id != $instance['wpessid'] ) { // phpcs:ignore loose comparison
			return '';
		}

		// Check if we need to translate strings.
		$instance = $this->maybe_translate_strings( $instance );

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		wpes_search_form( $instance );

		echo $args['after_widget'];

	}

	/**
	 * Widget update method
	 *
	 * @since 2.0
	 * @param object $new_instance New widget instance.
	 * @param object $old_instance Old widget instance.
	 * @return object updated widget instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		foreach ( WPES()->wpes_search_form->get_form_default_args() as $key => $key_desc ) {
			if ( 'wpessid' === $key && get_post_type( intval( $new_instance[ $key ] ) ) === 'wpes_setting' ) {
				$instance[ $key ] = intval( $new_instance[ $key ] );
			}
			$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
		}

		// Check if we need to register strings for translation.
		$this->maybe_translate_strings( $instance, true );

		return $instance;
	}

	/**
	 * Widget form
	 *
	 * @since 2.0
	 * @param object $instance Current widget instance.
	 */
	public function form( $instance ) {

		/* WP 5.8 fix */
		if ( function_exists( 'wp_use_widgets_block_editor' ) && wp_use_widgets_block_editor() ) {
			echo __( 'WPES is not compatible with block-based widgets yet.', 'wp-extended-search' ) . ' ';
			if ( defined( 'CLASSIC_AND_BLOCK_WIDGETS_FILENAME' ) ) {
				/* translators: %s: Link to widgets screen. */
				printf( __( 'You can access the <a href="%s">classic widgets</a> screen to manage the widgets.', 'wp-extended-search' ), admin_url( 'widgets.php?cw=1' ) );
			} else {
				/* translators: %s: WordPress plugin anchor tag. */
				printf( __( 'You can install %s plugin to enable classic widgets screen without losing new widgets screen.', 'wp-extended-search' ), '<a href="https://wordpress.org/plugins/classic-widgets-with-block-based-widgets/" rel="nofollow">Classic Widgets with Block-based Widgets</a>' );
			}
			echo ' ' . __( 'Also, you can send a request in the plugin support forum if you like WPES to provide support for block-based widgets.', 'wp-extended-search' );
			return;
		}

		$instance     = wp_parse_args( (array) $instance, array_merge( WPES()->wpes_search_form->form_default_args, array( 'title' => '' ) ) );
		$all_settings = WPES()->wpes_admin->get_all_setting_names(); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<?php

		foreach ( WPES()->wpes_search_form->get_form_default_args() as $key => $key_desc ) {
			if ( 'wpessid' === $key ) {
				continue;
			}
			?>
			<p>
				<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $key_desc; ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $instance[ $key ] ); ?>" />
			</p>
			<?php
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'wpessid' ); ?>"><?php _e( 'Setting Name:' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'wpessid' ); ?>" name="<?php echo $this->get_field_name( 'wpessid' ); ?>">
				<option value=""><?php _e( 'Global (default)', 'wp-extended-search' ); ?></option>
				<?php
				foreach ( $all_settings as $setting_name ) {
					?>
					<option <?php selected( $setting_name->ID, $instance['wpessid'] ); ?> value="<?php echo $setting_name->ID; ?>"><?php echo get_the_title( $setting_name ); ?></option>
					<?php
				}
				?>
			</select>
		</p>
		<p class="help">
		<?php
			/* translators: %s: URL */
			printf( __( 'To add new setting click <a href="%s">here</a>.', 'wp-extended-search' ), admin_url( 'post-new.php?post_type=wpes_setting' ) );
		?>
		</p>
		<?php
	}
}
