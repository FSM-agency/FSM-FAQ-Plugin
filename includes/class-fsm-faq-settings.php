<?php
/**
 * FSM FAQ: Settings page (brand colors, toggle icons, accordion behavior, schema mode)
 * and the front-end styling engine that turns saved options into scoped CSS.
 *
 * Settings are stored in a single option array (fsm_faq_settings) and applied to both
 * the Divi toggle markup and the generic accordion fallback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option key holding all FAQ display settings. */
define( 'FSM_FAQ_SETTINGS_OPTION', 'fsm_faq_settings' );

/**
 * Default settings. The default icon library depends on whether Divi is active
 * (ET Modules on Divi sites; bundled SVG elsewhere).
 *
 * @return array<string,mixed> Default settings.
 * @since 1.1.0
 */
function fsm_faq_get_default_settings() {
	$is_divi = function_exists( 'fsm_faq_is_divi_active' ) ? fsm_faq_is_divi_active() : false;

	return array(
		'toggle_bg'       => '#f4f4f4',
		'toggle_bg_hover' => '#e8e8e8',
		'toggle_bg_open'  => '#e0e0e0',
		'toggle_text'     => '#333333',
		'icon_color'      => '#666666',
		'border_color'    => '#cccccc',
		'border_width'    => 0,
		'icon_library'    => $is_divi ? 'et_modules' : 'svg',
		'icon_pair'       => 'plus_minus',
		'icon_size'       => 16,
		'first_open'      => '1',
		'allow_close'     => '1',
		'border_radius'   => 0,
		'item_spacing'    => 0,
		'schema_mode'     => 'shortcode',
	);
}

/**
 * Map a stored icon library to one that does not load a third-party CDN.
 *
 * Legacy "fontawesome" values become ET Modules on Divi and bundled SVG elsewhere.
 *
 * @param string $library Saved or submitted library key.
 * @return string 'et_modules' or 'svg'.
 * @since 1.1.3
 */
function fsm_faq_normalize_icon_library( $library ) {
	if ( 'et_modules' === $library || 'svg' === $library ) {
		return $library;
	}

	$is_divi = function_exists( 'fsm_faq_is_divi_active' ) ? fsm_faq_is_divi_active() : false;
	return $is_divi ? 'et_modules' : 'svg';
}

/**
 * Get merged settings (saved values over defaults).
 *
 * @return array<string,mixed> Effective settings.
 * @since 1.1.0
 */
function fsm_faq_get_settings() {
	$defaults = fsm_faq_get_default_settings();
	$saved    = get_option( FSM_FAQ_SETTINGS_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$settings                 = wp_parse_args( $saved, $defaults );
	$settings['icon_library'] = fsm_faq_normalize_icon_library( $settings['icon_library'] );
	return $settings;
}

/**
 * Short hash of current settings, used to bust the FAQ HTML cache when styling changes.
 *
 * @return string 8-char hash.
 * @since 1.1.0
 */
function fsm_faq_settings_hash() {
	return substr( md5( maybe_serialize( fsm_faq_get_settings() ) ), 0, 8 );
}

/**
 * Allowed values for select-type settings. Keys are option keys.
 *
 * @return array<string,string[]>
 * @since 1.1.0
 */
function fsm_faq_settings_allowed_values() {
	return array(
		'icon_library' => array( 'et_modules', 'svg' ),
		'icon_pair'    => array( 'plus_minus', 'chevron', 'caret', 'angle', 'none' ),
		'schema_mode'  => array( 'shortcode', 'seo_plugin', 'off' ),
	);
}

/* -------------------------------------------------------------------------
 * Admin: menu, registration, sanitize, and page render
 * ---------------------------------------------------------------------- */

/**
 * Register the FAQs -> Settings submenu page.
 *
 * @since 1.1.0
 */
add_action( 'admin_menu', 'fsm_faq_register_settings_page' );
function fsm_faq_register_settings_page() {
	$hook = add_submenu_page(
		'edit.php?post_type=faq',
		__( 'FAQ Settings', 'fsm-faq' ),
		__( 'Settings', 'fsm-faq' ),
		'manage_options',
		'fsm-faq-settings',
		'fsm_faq_render_settings_page'
	);

	if ( $hook ) {
		add_action( 'load-' . $hook, 'fsm_faq_settings_page_loaded' );
	}
}

/**
 * Flag used by the asset enqueue to know we are on the settings screen.
 *
 * @since 1.1.0
 */
function fsm_faq_settings_page_loaded() {
	add_action( 'admin_enqueue_scripts', 'fsm_faq_enqueue_settings_assets' );
}

/**
 * Enqueue the color picker and settings admin script on the settings screen.
 *
 * @since 1.1.0
 */
function fsm_faq_enqueue_settings_assets() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script(
		'fsm-faq-admin-settings',
		plugin_dir_url( dirname( __FILE__ ) ) . 'assets/fsm-faq-admin-settings.js',
		array( 'wp-color-picker', 'jquery' ),
		FSM_FAQ_VERSION,
		true
	);
}

/**
 * Register the setting and sanitize callback.
 *
 * @since 1.1.0
 */
add_action( 'admin_init', 'fsm_faq_register_settings' );
function fsm_faq_register_settings() {
	register_setting(
		'fsm_faq_settings_group',
		FSM_FAQ_SETTINGS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'fsm_faq_sanitize_settings',
			'default'           => fsm_faq_get_default_settings(),
		)
	);
}
