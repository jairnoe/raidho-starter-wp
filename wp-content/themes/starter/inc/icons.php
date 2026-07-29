<?php
/**
 * Inline SVG icon helper.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Return an inline SVG icon from /icons/{name}.svg.
 *
 * Icons ship without fixed fills so Tailwind fill-* / size-* classes work.
 *
 * @param string $name  Icon filename without extension.
 * @param string $class Optional CSS classes injected on the root <svg>.
 */
function starter_get_icon(string $name, string $class = ''): string
{
	$name = sanitize_file_name($name);
	$path = STARTER_DIR . '/icons/' . $name . '.svg';

	if (! is_readable($path)) {
		if (defined('WP_DEBUG') && WP_DEBUG) {
			return '<!-- missing icon: ' . esc_html($name) . ' -->';
		}

		return '';
	}

	$svg = (string) file_get_contents($path);

	if ($class !== '') {
		$class_attr = ' class="' . esc_attr($class) . '"';

		if (preg_match('/\sclass="/', $svg)) {
			$svg = preg_replace('/\sclass="[^"]*"/', $class_attr, $svg, 1) ?? $svg;
		} else {
			$svg = preg_replace('/<svg\b/', '<svg' . $class_attr, $svg, 1) ?? $svg;
		}
	}

	return trim($svg);
}

/**
 * Echo an inline SVG icon.
 *
 * @param string $name  Icon filename without extension.
 * @param string $class Optional CSS classes.
 */
function starter_icon(string $name, string $class = ''): void
{
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme SVG with escaped class.
	echo starter_get_icon($name, $class);
}
