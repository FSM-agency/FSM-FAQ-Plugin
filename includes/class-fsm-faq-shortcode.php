<?php
/**
 * FSM FAQ: [fsm_display_faqs] and [fsm_display_generic_faqs] shortcodes.
 *
 * - [fsm_display_faqs]: Uses Divi markup when Divi is active; otherwise generic accordion.
 * - [fsm_display_generic_faqs]: Always uses generic accordion (theme-agnostic).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if Divi (or a Divi child theme) is the active theme.
 *
 * @return bool True if Divi or Divi child is active.
 * @since 1.1.0
 */
function fsm_faq_is_divi_active() {
	$theme    = wp_get_theme();
	$name     = $theme->get( 'Name' );
	$template = $theme->get_template();
	return ( 'Divi' === $name || 'Divi' === $template );
}

/**
 * Normalize typographic apostrophes (and their HTML entities) to ASCII so they survive
 * the_content/wp_kses and any filters that strip or replace U+2019 (e.g. property's → property s).
 * Covers: Unicode chars U+2018/U+2019 and entities &#8216;/&#8217;, &#x2018;/&#x2019;, &lsquo;/&rsquo;.
 *
 * @param string $text FAQ question title, answer body, or other content.
 * @return string Same content with typographic apostrophes replaced by ASCII apostrophe.
 * @since 1.1.0
 */
function fsm_faq_normalize_typographic_apostrophes( $text ) {
	if ( ! is_string( $text ) || '' === $text ) {
		return $text;
	}
	$replace = array(
		"\u{2019}", "\u{2018}",           // RIGHT/LEFT SINGLE QUOTATION MARK
		"&#8217;", "&#8216;",             // decimal entities
		"&#x2019;", "&#x2018;",           // hex entities (lowercase)
		"&#X2019;", "&#X2018;",           // hex entities (uppercase)
		'&rsquo;', '&lsquo;',             // named entities
	);
	return str_replace( $replace, "'", $text );
}

/**
 * Get FAQ items and schema data for a post. Shared by both shortcodes.
 *
 * Answer text in schema uses the_content + wp_kses_post so acceptedAnswer.text
 * includes the same HTML as the toggle. Keeping the HTML (not stripping to plain
 * text) helps bots/crawlers see the structure of the answer (paragraphs, lists,
 * etc.) rather than one run-on block.
 *
 * @param int $post_id Current page/post ID.
 * @return array{ items: array, schema_questions: array } Empty items/schema_questions on failure.
 * @since 1.1.0
 */
function fsm_faq_get_faq_data( $post_id ) {
	$result = array(
		'items'            => array(),
		'schema_questions' => array(),
	);

	if ( ! $post_id || ! function_exists( 'get_field' ) ) {
		return $result;
	}

	$args = array(
		'post_type'               => 'faq',
		'posts_per_page'          => -1,
		'meta_query'              => array(
			array(
				'key'     => 'display_on_pages',
				'value'   => '"' . absint( $post_id ) . '"',
				'compare' => 'LIKE',
			),
		),
		'orderby'                 => 'menu_order',
		'order'                   => 'ASC',
		'no_found_rows'           => true,
		'update_post_meta_cache'  => false,
		'update_post_term_cache'  => false,
	);

	$faq_query = new WP_Query( $args );

	if ( ! $faq_query->have_posts() ) {
		wp_reset_postdata();
		return $result;
	}

	while ( $faq_query->have_posts() ) {
		$faq_query->the_post();
		$question = fsm_faq_normalize_typographic_apostrophes( get_the_title() );
		$answer   = get_field( 'faq_answer' );

		if ( empty( $question ) || empty( $answer ) ) {
			continue;
		}

		// Normalize typographic apostrophes so they are not stripped/replaced by the_content or wp_kses.
		$answer = fsm_faq_normalize_typographic_apostrophes( $answer );

		$result['items'][] = array(
			'question' => $question,
			'answer'   => $answer,
		);

		// Schema answer: same the_content + wp_kses_post; HTML preserved so scrapers see structure (paragraphs, lists), not a run-on sentence.
		$result['schema_questions'][] = array(
			'@type'          => 'Question',
			'name'           => esc_html( $question ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_kses_post( apply_filters( 'the_content', $answer ) ),
			),
		);
	}
	wp_reset_postdata();

	return $result;
}

/**
 * Enqueue generic accordion CSS and JS (for fallback output).
 *
 * @since 1.1.0
 */
function fsm_faq_enqueue_generic_assets() {
	$url = plugin_dir_url( dirname( __FILE__ ) ) . 'assets/';
	wp_enqueue_style(
		'fsm-faq-accordion',
		$url . 'fsm-faq-accordion.css',
		array(),
		FSM_FAQ_VERSION
	);
	wp_enqueue_script(
		'fsm-faq-accordion',
		$url . 'fsm-faq-accordion.js',
		array(),
		FSM_FAQ_VERSION,
		true
	);
}

/**
 * Build Divi accordion markup (original behavior). No schema; caller adds it.
 *
 * Answer content is run through the_content so all WYSIWYG formatting (paragraphs,
 * lists, bold, links, etc.) and special characters (e.g. smart apostrophes) output
 * correctly in the toggle. Output is then passed through wp_kses_post for safety.
 *
 * @param array $items Array of { question, answer }.
 * @return string HTML.
 * @since 1.1.0
 */
function fsm_faq_render_divi_markup( $items ) {
	if ( empty( $items ) ) {
		return '';
	}

	$html = '<div class="et_pb_module et_pb_accordion et_pb_accordion_0_tb_body et_pb_text_align_left">';
	$i   = 0;
	foreach ( $items as $item ) {
		$answer_content = apply_filters( 'the_content', $item['answer'] );
		$answer_content = fsm_faq_normalize_typographic_apostrophes( $answer_content );
		// Output apostrophe as entity so it survives any post-shortcode processing (e.g. Divi) that strips the raw character.
		$answer_content = str_replace( "'", '&#39;', $answer_content );
		$toggle_state_class = ( 0 === $i ) ? 'et_pb_toggle_open' : 'et_pb_toggle_close';
		$html .= '<div class="et_pb_toggle et_pb_module et_pb_accordion_item ' . esc_attr( $toggle_state_class ) . '">';
		$html .= '<h3 class="et_pb_toggle_title">' . esc_html( $item['question'] ) . '</h3>';
		$html .= '<div class="et_pb_toggle_content clearfix">' . wp_kses_post( $answer_content ) . '</div>';
		$html .= '</div>';
		$i++;
	}
	$html .= '</div>';
	return $html;
}

/**
 * Build generic accordion markup (W3Schools-style). No schema; caller adds it.
 * Each question wraps in .fsm-faq-accordion__item (button + panel) for card chrome.
 *
 * Answer content is run through the_content so all WYSIWYG formatting (paragraphs,
 * lists, bold, links, etc.) and special characters (e.g. smart apostrophes) output
 * correctly in the toggle. Output is then passed through wp_kses_post for safety.
 *
 * @param array $items Array of { question, answer }.
 * @return string HTML.
 * @since 1.1.0
 */
function fsm_faq_render_generic_markup( $items ) {
	if ( empty( $items ) ) {
		return '';
	}

	fsm_faq_enqueue_generic_assets();

	$block_id = 'fsm-faq-' . uniqid();
	$html     = '<div class="fsm-faq-accordion" id="' . esc_attr( $block_id ) . '">';
	$index   = 0;
	foreach ( $items as $item ) {
		$answer_content = apply_filters( 'the_content', $item['answer'] );
		$answer_content = fsm_faq_normalize_typographic_apostrophes( $answer_content );
		// Output apostrophe as entity so it survives any post-shortcode processing that strips the raw character.
		$answer_content = str_replace( "'", '&#39;', $answer_content );
		$btn_id   = $block_id . '-btn-' . $index;
		$panel_id = $block_id . '-panel-' . $index;
		$html    .= '<div class="fsm-faq-accordion__item">';
		$html    .= '<button type="button" id="' . esc_attr( $btn_id ) . '" class="fsm-faq-accordion__btn" aria-expanded="false" aria-controls="' . esc_attr( $panel_id ) . '">';
		$html    .= '<h3 class="fsm-faq-accordion__title">' . esc_html( $item['question'] ) . '</h3>';
		$html    .= '</button>';
		$html    .= '<div id="' . esc_attr( $panel_id ) . '" class="fsm-faq-accordion__panel" role="region" aria-labelledby="' . esc_attr( $btn_id ) . '">';
		$html    .= '<div class="fsm-faq-accordion__panel-inner">' . wp_kses_post( $answer_content ) . '</div>';
		$html    .= '</div>';
		$html    .= '</div>';
		$index++;
	}
	$html .= '</div>';
	return $html;
}

/**
 * Shortcode: [fsm_display_faqs]
 *
 * Description: Displays FAQ posts assigned to the page. Uses Divi markup when Divi is active; otherwise generic accordion. Includes FAQ schema.
 *
 * Attributes: None. Uses current post ID in the loop.
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

	$cache_key     = 'fsm_faqs_' . absint( $current_post_id ) . '_v' . FSM_FAQ_VERSION;
	$cached_output = wp_cache_get( $cache_key );

	if ( false !== $cached_output ) {
		return $cached_output;
	}

	$data = fsm_faq_get_faq_data( $current_post_id );

	if ( empty( $data['items'] ) ) {
		wp_cache_set( $cache_key, '', '', HOUR_IN_SECONDS );
		return '';
	}

	$use_divi = fsm_faq_is_divi_active();
	$html     = $use_divi
		? fsm_faq_render_divi_markup( $data['items'] )
		: fsm_faq_render_generic_markup( $data['items'] );

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $data['schema_questions'],
	);

	$final_output = '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	$final_output .= $html;

	wp_cache_set( $cache_key, $final_output, '', HOUR_IN_SECONDS );

	return $final_output;
}

/**
 * Shortcode: [fsm_display_generic_faqs]
 *
 * Description: Displays FAQ posts assigned to the page using the generic accordion (theme-agnostic). Use on non-Divi sites or when you want accordion behavior without Divi. Includes FAQ schema.
 *
 * Attributes: None. Uses current post ID in the loop.
 *
 * Example Usage:
 * [fsm_display_generic_faqs]
 *
 * @return string HTML output
 */
add_shortcode( 'fsm_display_generic_faqs', 'fsm_display_generic_faqs_shortcode' );
function fsm_display_generic_faqs_shortcode() {
	$current_post_id = get_the_ID();

	if ( ! $current_post_id ) {
		return '';
	}

	$cache_key     = 'fsm_faqs_generic_' . absint( $current_post_id ) . '_v' . FSM_FAQ_VERSION;
	$cached_output = wp_cache_get( $cache_key );

	if ( false !== $cached_output ) {
		return $cached_output;
	}

	$data = fsm_faq_get_faq_data( $current_post_id );

	if ( empty( $data['items'] ) ) {
		wp_cache_set( $cache_key, '', '', HOUR_IN_SECONDS );
		return '';
	}

	$html = fsm_faq_render_generic_markup( $data['items'] );

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $data['schema_questions'],
	);

	$final_output = '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	$final_output .= $html;

	wp_cache_set( $cache_key, $final_output, '', HOUR_IN_SECONDS );

	return $final_output;
}