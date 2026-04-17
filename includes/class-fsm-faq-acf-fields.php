<?php
/**
 * FSM FAQ: Register ACF field groups (FAQ post type + Page FAQs relationship).
 *
 * Provides display_on_pages and faq_answer on FAQ, and optional page_faqs on parent post types
 * (default: Page; extend via fsm_faq_parent_post_types filter)
 * for bidirectional editing. No manual ACF setup required.
 *
 * One-time migration: removes any existing FAQ/Page FAQs field groups from the
 * database (by key) so the plugin's local groups are the single source of truth.
 * Post meta (faq_answer, display_on_pages) is unchanged; only the group definitions
 * move from DB to plugin code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Keys for FAQ and Page FAQs field groups — used for migration and registration. */
define( 'FSM_FAQ_ACF_GROUP_FAQS', 'group_68dd4428d3136' );
define( 'FSM_FAQ_ACF_GROUP_PAGE_FAQS', 'group_68f0076749dc4' );

/**
 * Post types that may appear in FAQ "Display On" (post_object) and that receive the
 * bidirectional "Page FAQs" field group. Defaults to page only; sites may append CPTs
 * (e.g. Divi project) via the fsm_faq_parent_post_types filter.
 *
 * @return string[] Sanitized post type slugs.
 */
function fsm_faq_get_parent_post_types() {
	$types = apply_filters( 'fsm_faq_parent_post_types', array( 'page' ) );
	if ( ! is_array( $types ) ) {
		$types = array( 'page' );
	}
	$types = array_map( 'sanitize_key', $types );
	$types = array_filter( array_unique( $types ) );
	return ! empty( $types ) ? array_values( $types ) : array( 'page' );
}

/**
 * One-time migration: remove native (DB) FAQ/Page FAQs field groups so plugin's
 * local groups are the single source. Runs before local registration.
 */
add_action( 'acf/init', 'fsm_faq_maybe_remove_native_faq_groups', 5 );
function fsm_faq_maybe_remove_native_faq_groups() {
	if ( ! function_exists( 'acf_get_field_group' ) ) {
		return;
	}
	if ( get_option( 'fsm_faq_acf_migrated', false ) ) {
		return;
	}
	$keys = array( FSM_FAQ_ACF_GROUP_FAQS, FSM_FAQ_ACF_GROUP_PAGE_FAQS );
	foreach ( $keys as $key ) {
		$group = acf_get_field_group( $key );
		if ( $group && ! empty( $group['ID'] ) ) {
			wp_delete_post( (int) $group['ID'], true );
		}
	}
	update_option( 'fsm_faq_acf_migrated', true );
}

add_action( 'acf/init', 'fsm_faq_register_field_groups', 10 );
function fsm_faq_register_field_groups() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$faq_parent_post_types = fsm_faq_get_parent_post_types();

	$page_faqs_location = array();
	foreach ( $faq_parent_post_types as $post_type_slug ) {
		$page_faqs_location[] = array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => $post_type_slug,
			),
		);
	}

	acf_add_local_field_group( array(
		'key'                   => FSM_FAQ_ACF_GROUP_FAQS,
		'title'                 => 'FAQs',
		'fields'                => array(
			array(
				'key'               => 'field_68dd4429b1ee9',
				'label'             => 'Answer',
				'name'              => 'faq_answer',
				'aria-label'        => '',
				'type'              => 'wysiwyg',
				'instructions'      => '',
				'required'          => 1,
				'conditional_logic' => 0,
				'wrapper'           => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'default_value'     => '',
				'tabs'              => 'all',
				'toolbar'           => 'full',
				'media_upload'      => 1,
				'delay'             => 0,
			),
			array(
				'key'                   => 'field_68dd445ab1eea',
				'label'                 => 'Display On',
				'name'                  => 'display_on_pages',
				'aria-label'            => '',
				'type'                  => 'post_object',
				'instructions'          => '',
				'required'              => 1,
				'conditional_logic'     => 0,
				'wrapper'               => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'post_type'             => $faq_parent_post_types,
				'post_status'           => '',
				'taxonomy'              => '',
				'return_format'          => 'id',
				'multiple'              => 1,
				'allow_null'             => 0,
				'bidirectional'          => 1,
				'bidirectional_target'   => array( 'field_68f00767a983d' ),
				'ui'                    => 1,
			),
		),
		'location'              => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'faq',
				),
			),
		),
		'menu_order'            => 0,
		'position'               => 'acf_after_title',
		'style'                  => 'seamless',
		'label_placement'        => 'top',
		'instruction_placement'  => 'label',
		'hide_on_screen'         => '',
		'active'                 => true,
		'description'             => '',
		'show_in_rest'           => 0,
	) );

	acf_add_local_field_group( array(
		'key'                   => FSM_FAQ_ACF_GROUP_PAGE_FAQS,
		'title'                 => 'Page FAQs',
		'fields'                => array(
			array(
				'key'                   => 'field_68f00767a983d',
				'label'                 => 'Page FAQs',
				'name'                  => 'page_faqs',
				'aria-label'            => '',
				'type'                  => 'relationship',
				'instructions'          => '',
				'required'              => 0,
				'conditional_logic'     => 0,
				'wrapper'               => array(
					'width' => '',
					'class' => '',
					'id'    => '',
				),
				'post_type'             => array( 'faq' ),
				'post_status'           => array( 'publish' ),
				'taxonomy'              => '',
				'filters'               => array( 'search' ),
				'return_format'          => 'id',
				'min'                   => '',
				'max'                   => '',
				'elements'              => '',
				'bidirectional'          => 1,
				'bidirectional_target'  => array( 'field_68dd445ab1eea' ),
				'ui'                    => 1,
			),
		),
		'location'              => $page_faqs_location,
		'menu_order'            => 0,
		'position'               => 'normal',
		'style'                  => 'default',
		'label_placement'        => 'top',
		'instruction_placement'  => 'label',
		'hide_on_screen'         => '',
		'active'                 => true,
		'description'             => '',
		'show_in_rest'           => 0,
	) );
}
