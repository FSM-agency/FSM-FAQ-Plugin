<?php
/**
 * FSM FAQ: GitHub-based plugin updates via Plugin Update Checker (PUC).
 *
 * Requires: Plugin Update Checker in vendor/plugin-update-checker/ or plugin-update-checker-5.6/
 * Optional: define( 'FSM_FAQ_GITHUB_REPO', 'https://github.com/FSM-agency/FSM-FAQ-Plugin/' );
 *
 * @link https://github.com/YahnisElsts/plugin-update-checker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$puc_path = FSM_FAQ_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';
if ( ! file_exists( $puc_path ) ) {
	$puc_path = FSM_FAQ_PATH . 'plugin-update-checker-5.6/plugin-update-checker.php';
}
if ( ! file_exists( $puc_path ) || ! defined( 'FSM_FAQ_GITHUB_REPO' ) || ! FSM_FAQ_GITHUB_REPO ) {
	return;
}

require_once $puc_path;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$fsm_faq_update_checker = PucFactory::buildUpdateChecker(
	FSM_FAQ_GITHUB_REPO,
	FSM_FAQ_PATH . 'fsm-faq.php',
	'fsm-faq'
);

$fsm_faq_update_checker->setBranch( apply_filters( 'fsm_faq_update_branch', 'main' ) );

if ( defined( 'FSM_FAQ_GITHUB_TOKEN' ) && FSM_FAQ_GITHUB_TOKEN ) {
	$fsm_faq_update_checker->setAuthentication( FSM_FAQ_GITHUB_TOKEN );
}
