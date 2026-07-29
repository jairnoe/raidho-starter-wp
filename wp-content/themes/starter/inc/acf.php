<?php
/**
 * Advanced Custom Fields configuration.
 *
 * Happy path: ACF Pro (Options Page, Gallery, Repeater, etc.).
 * Field groups live in /acf-json. Free ACF is emergency-only.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Point ACF Local JSON at the theme acf-json directory.
 *
 * @param string $path Default ACF save path.
 */
function starter_acf_json_save_point(string $path): string
{
	return STARTER_DIR . '/acf-json';
}
add_filter('acf/settings/save_json', 'starter_acf_json_save_point');

/**
 * Load ACF Local JSON from the theme acf-json directory.
 *
 * @param list<string> $paths Existing load paths.
 * @return list<string>
 */
function starter_acf_json_load_point(array $paths): array
{
	$paths[] = STARTER_DIR . '/acf-json';

	return $paths;
}
add_filter('acf/settings/load_json', 'starter_acf_json_load_point');

/**
 * Register ACF Options page (requires ACF Pro).
 */
function starter_register_acf_options_page(): void
{
	if (! function_exists('acf_add_options_page')) {
		return;
	}

	acf_add_options_page(
		[
			'page_title' => __('Site Settings', 'starter'),
			'menu_title' => __('Site Settings', 'starter'),
			'menu_slug'  => 'starter-site-settings',
			'capability' => 'edit_theme_options',
			'redirect'   => false,
			'position'   => 59,
			'icon_url'   => 'dashicons-admin-generic',
		]
	);
}
add_action('acf/init', 'starter_register_acf_options_page');

/**
 * Admin notice when ACF Pro is missing.
 */
function starter_acf_missing_notice(): void
{
	if (function_exists('acf_add_options_page') || ! current_user_can('activate_plugins')) {
		return;
	}

	echo '<div class="notice notice-warning"><p>';
	echo esc_html__(
		'Site Starter: copy Advanced Custom Fields Pro into wp-content/plugins/advanced-custom-fields-pro/ and restart wp-env (see README). Free ACF is emergency-only.',
		'starter'
	);
	echo '</p></div>';
}
add_action('admin_notices', 'starter_acf_missing_notice');
