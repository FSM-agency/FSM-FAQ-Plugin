<?php
/**
 * FSM FAQ: [fsm_display_faqs] shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode: [fsm_display_faqs]
 *
 * Description: Displays FAQ posts assigned to the page with valid schema markup.
 *
 * Attributes:
 * - None. Uses current post ID in the loop.
 *
 * Example Usage:
 * [fsm_display_faqs]
 *
 * @return string HTML output
 */
add_shortcode( 'fsm_display_faqs', 'fsm_display_faqs_shortcode' );
function fsm_display_faqs_shortcode() {
	$current_post_id = get_the_ID();

	if ( ! $current_post_id ) {
		return '';
	}

	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$cache_key     = 'fsm_faqs_' . absint( $current_post_id );
	$cached_output = wp_cache_get( $cache_key );

	if ( false !== $cached_output ) {
		return $cached_output;
	}

	$args = array(
		'post_type'                  => 'faq',
		'posts_per_page'            => -1,
		'meta_query'                => array(
			array(
				'key'     => 'display_on_pages',
				'value'   => '"' . absint( $current_post_id ) . '"',
				'compare' => 'LIKE',
			),
		),
		'orderby'                   => 'menu_order',
		'order'                     => 'ASC',
		'no_found_rows'             => true,
		'update_post_meta_cache'    => false,
		'update_post_term_cache'    => false,
	);

	$faq_query = new WP_Query( $args );

	if ( ! $faq_query->have_posts() ) {
		wp_reset_postdata();
		wp_cache_set( $cache_key, '', '', HOUR_IN_SECONDS );
		return '';
	}

	$html_output      = '<div class="et_pb_module et_pb_accordion et_pb_accordion_0_tb_body et_pb_text_align_left">';
	$schema_questions = array();
	$counter           = 0;

	while ( $faq_query->have_posts() ) {
		$faq_query->the_post();
		$question = get_the_title();
		$answer   = get_field( 'faq_answer' );

		if ( empty( $question ) || empty( $answer ) ) {
			continue;
		}

		$toggle_state_class = ( 0 === $counter ) ? 'et_pb_toggle_open' : 'et_pb_toggle_close';

		$html_output .= '<div class="et_pb_toggle et_pb_module et_pb_accordion_item ' . esc_attr( $toggle_state_class ) . '">';
		$html_output .= '<h3 class="et_pb_toggle_title">' . esc_html( $question ) . '</h3>';
		$html_output .= '<div class="et_pb_toggle_content clearfix">' . wp_kses_post( $answer ) . '</div>';
		$html_output .= '</div>';

		$schema_questions[] = array(
			'@type'          => 'Question',
			'name'           => esc_html( $question ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_kses_post( $answer ),
			),
		);
		$counter++;
	}
	wp_reset_postdata();

	$html_output .= '</div>';

	if ( empty( $schema_questions ) ) {
		wp_cache_set( $cache_key, '', '', HOUR_IN_SECONDS );
		return '';
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $schema_questions,
	);

	$final_output = '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	$final_output .= $html_output;

	wp_cache_set( $cache_key, $final_output, '', HOUR_IN_SECONDS );

	return $final_output;
}
