<?php
/**
 * WPML compatibility
 *
 * @package WPES\Integrations
 * @author 5um17
 */

defined( 'ABSPATH' ) || exit();

/**
 * WPML compatibility class.
 */
class WPES_WPML {

	/**
	 * Class constructor.
	 * Register WPML actions.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_filter( 'icl_lang_sel_copy_parameters', array( $this, 'ls_copy_query_str' ) );
		add_action( 'admin_head', array( $this, 'remove_wpml_meta_box' ), 99 );
	}

	/**
	 * Check if given WPML is active.
	 *
	 * @since 2.0
	 * @param string $addon Addon name: ST, TM, WCML.
	 * @return boolean true if active else false.
	 */
	public function is_addon_active( $addon = '' ) {
		$addons = array(
			'ST'   => 'WPML_ST_VERSION',
			'TM'   => 'WPML_TM_VERSION',
			'WCML' => 'WCML_VERSION',
		);

		if ( isset( $addons[ $addon ] ) ) {
			return defined( $addons[ $addon ] ) ? true : false;
		}

		// If addon name is not matched return false.
		return false;
	}

	/**
	 * Translate or register string using WPML API.
	 *
	 * @since 2.0
	 * @param string  $context Context name.
	 * @param string  $str_value Original string.
	 * @param string  $str_name String Name.
	 * @param boolean $register Optional. Pass true to register or false to translate.
	 * @param boolean $lang Optional. Translate the string in given language.
	 * @return string Original or translated string.
	 */
	public function translate_or_register_string( $context, $str_value, $str_name, $register = false, $lang = null ) {
		if ( ! empty( $register ) ) {
			do_action( 'wpml_register_single_string', $context, $str_name, $str_value );
			return $str_value;
		} else {
			return apply_filters( 'wpml_translate_single_string', $str_value, $context, $str_name, $lang );
		}
	}

	/**
	 * Copy wpessid parameter to translated URLs in language switcher.
	 *
	 * @since 2.0
	 * @param array $parameters Original parameters to copy.
	 * @return array $parameters Updated parameters.
	 */
	public function ls_copy_query_str( $parameters ) {
		if ( ! is_array( $parameters ) ) {
			return array( 'wpessid' );
		}

		if ( ! in_array( 'wpessid', $parameters, true ) ) {
			array_push( $parameters, 'wpessid' );
			return $parameters;
		}

		return $parameters;
	}

	/**
	 * Remove WPML metabox.
	 * We don't need to translate the setting CPT so remove it.
	 *
	 * @since 2.0
	 */
	public function remove_wpml_meta_box() {
		remove_meta_box( 'icl_div_config', 'wpes_setting', 'normal' );
	}
}
