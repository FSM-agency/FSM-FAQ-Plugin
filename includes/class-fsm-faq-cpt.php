<?php
/**
 * FSM FAQ: Register FAQ post type.
 *
 * @see get_post_type_labels() for label keys.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'fsm_faq_cpt' );
function fsm_faq_cpt() {
	$labels = array(
		'name'               => _x( 'FAQs', 'Post type general name', 'fsm-faq' ),
		'singular_name'      => _x( 'FAQ', 'Post type singular name', 'fsm-faq' ),
		'menu_name'          => _x( 'FAQs', 'Admin Menu text', 'fsm-faq' ),
		'add_new'            => __( 'Add New', 'fsm-faq' ),
		'add_new_item'       => __( 'Add New FAQ', 'fsm-faq' ),
		'edit_item'          => __( 'Edit FAQ', 'fsm-faq' ),
		'view_item'          => __( 'View FAQ', 'fsm-faq' ),
		'all_items'          => __( 'All FAQs', 'fsm-faq' ),
		'search_items'       => __( 'Search FAQs', 'fsm-faq' ),
		'not_found'          => __( 'No FAQs found.', 'fsm-faq' ),
		'not_found_in_trash' => __( 'No FAQs found in Trash.', 'fsm-faq' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'   => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'          => false,
		'rewrite'             => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'supports'           => array( 'title' ),
		'menu_icon'          => 'dashicons-editor-help',
	);

	register_post_type( 'faq', $args );
}
