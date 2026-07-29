<?php
/**
 * General theme helpers.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Load a component from /components/{slug}/{slug}.php.
 *
 * @param string               $slug Component folder/file name.
 * @param array<string, mixed> $args Variables extracted into the component scope.
 */
function starter_component(string $slug, array $args = []): void
{
	$slug = sanitize_file_name($slug);
	$path = STARTER_DIR . '/components/' . $slug . '/' . $slug . '.php';

	if (! is_readable($path)) {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			echo '<!-- missing component: ' . esc_html($slug) . ' -->';
		}

		return;
	}

	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- intentional component API.
	extract($args, EXTR_SKIP);

	require $path;
}

/**
 * Barba namespace for data-barba-namespace (extend when you add CPTs).
 */
function starter_barba_namespace(): string
{
	if (is_front_page()) {
		return 'home';
	}

	if (is_page()) {
		$slug = get_post_field('post_name', get_queried_object_id());

		return is_string($slug) && $slug !== '' ? $slug : 'page';
	}

	return 'default';
}

/**
 * Read an ACF Options field with a PHP default.
 *
 * @param string $key     Field name.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function starter_option(string $key, mixed $default = '')
{
	if (function_exists('get_field')) {
		$value = get_field($key, 'option');

		if ($value !== null && $value !== '' && $value !== false) {
			return $value;
		}
	}

	return $default;
}

/**
 * Site contact from Site Settings (ACF Options) with coded defaults.
 *
 * @return array{
 *   phone: string,
 *   email: string,
 *   address_lines: list<string>,
 *   social: array{instagram: string, youtube: string}
 * }
 */
function starter_site_contact(): array
{
	$address = (string) starter_option(
		'address',
		"123 Example Street\nCity, Country\n00000"
	);

	$lines = array_values(
		array_filter(
			array_map('trim', preg_split('/\r\n|\r|\n/', $address) ?: [])
		)
	);

	if ($lines === []) {
		$lines = [
			'123 Example Street',
			'City, Country',
			'00000',
		];
	}

	return [
		'phone'         => (string) starter_option('phone', '+52 55 0000 0000'),
		'email'         => (string) starter_option('email', 'hello@example.com'),
		'address_lines' => $lines,
		'social'        => [
			'instagram' => (string) starter_option('instagram', ''),
			'youtube'   => (string) starter_option('youtube', ''),
		],
	];
}
