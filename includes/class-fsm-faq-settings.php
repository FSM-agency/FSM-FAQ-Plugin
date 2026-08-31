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
