<?php
/**
 * Plugin Name: Starter Tunnel URLs
 * Description: Rewrites home/siteurl, assets, srcset, menus, and ACF links when the request comes through ngrok or Cloudflare Tunnel. Localhost / 127.0.0.1 unchanged. Dev-only.
 *
 * @package Starter
 */

declare(strict_types=1);

/**
 * Public tunnel base URL for this request, or null when local.
 */
function starter_tunnel_base_url(): ?string
{
	$host_header = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
	if (! is_string($host_header) || $host_header === '') {
		return null;
	}

	$host = strtolower(trim(explode(',', $host_header, 2)[0]));
	// wp-env may advertise :8888 on tunnel hosts; public tunnels only expose 443.
	$host = (string) preg_replace('/:(8888|8889)$/', '', $host);

	if (
		$host === ''
		|| str_contains($host, 'localhost')
		|| str_starts_with($host, '127.')
	) {
		return null;
	}

	$proto_header = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
	$proto        = is_string($proto_header) && $proto_header !== ''
		? strtolower(trim(explode(',', $proto_header, 2)[0]))
		: ((! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');

	if (
		str_contains($host, 'ngrok')
		|| str_contains($host, 'trycloudflare.com')
		|| str_contains($host, 'loca.lt')
	) {
		$proto = 'https';
	}

	return $proto . '://' . $host;
}

/**
 * Prefer tunnel URL over WP_HOME / WP_SITEURL constants from wp-env.
 *
 * @param mixed $value Current option value.
 * @return mixed
 */
function starter_tunnel_option_url($value)
{
	$base = starter_tunnel_base_url();

	return $base ?? $value;
}

/**
 * Rewrite absolute localhost URLs to the active tunnel origin.
 */
function starter_tunnel_rewrite_url(string $url): string
{
	$base = starter_tunnel_base_url();
	if ($base === null || $url === '') {
		return $url;
	}

	$rewritten = preg_replace(
		'#https?://(?:localhost|127\.0\.0\.1)(?::\d+)?#i',
		$base,
		$url
	);

	return is_string($rewritten) ? $rewritten : $url;
}

/**
 * @param mixed $url URL.
 * @return mixed
 */
function starter_tunnel_filter_url($url)
{
	return is_string($url) ? starter_tunnel_rewrite_url($url) : $url;
}

// Priority > default _config_wp_home / _config_wp_siteurl (10).
add_filter('pre_option_home', 'starter_tunnel_option_url', 100);
add_filter('pre_option_siteurl', 'starter_tunnel_option_url', 100);

add_filter('home_url', 'starter_tunnel_filter_url', 100);
add_filter('site_url', 'starter_tunnel_filter_url', 100);
add_filter('content_url', 'starter_tunnel_filter_url', 100);
add_filter('plugins_url', 'starter_tunnel_filter_url', 100);
add_filter('style_loader_src', 'starter_tunnel_filter_url', 100);
add_filter('script_loader_src', 'starter_tunnel_filter_url', 100);
add_filter('wp_get_attachment_url', 'starter_tunnel_filter_url', 100);

/**
 * Rewrite srcset candidates (browsers prefer these over src).
 *
 * @param array<int, array{url: string, descriptor: string, value: int}>|false $sources
 * @return array<int, array{url: string, descriptor: string, value: int}>|false
 */
function starter_tunnel_srcset($sources)
{
	if (! is_array($sources)) {
		return $sources;
	}

	foreach ($sources as &$source) {
		if (isset($source['url']) && is_string($source['url'])) {
			$source['url'] = starter_tunnel_rewrite_url($source['url']);
		}
	}
	unset($source);

	return $sources;
}
add_filter('wp_calculate_image_srcset', 'starter_tunnel_srcset', 100);

/**
 * @param array{0: string, 1: int, 2: int, 3: bool}|false $image
 * @return array{0: string, 1: int, 2: int, 3: bool}|false
 */
function starter_tunnel_attachment_image_src($image)
{
	if (! is_array($image) || ! isset($image[0]) || ! is_string($image[0])) {
		return $image;
	}

	$image[0] = starter_tunnel_rewrite_url($image[0]);

	return $image;
}
add_filter('wp_get_attachment_image_src', 'starter_tunnel_attachment_image_src', 100);

/**
 * Custom menu items often store absolute localhost URLs from seeds — prefer root-relative seeds.
 *
 * @param array<string, string> $atts Link attributes.
 * @return array<string, string>
 */
function starter_tunnel_nav_menu_link_attributes(array $atts): array
{
	if (isset($atts['href']) && is_string($atts['href'])) {
		$atts['href'] = starter_tunnel_rewrite_url($atts['href']);
	}

	return $atts;
}
add_filter('nav_menu_link_attributes', 'starter_tunnel_nav_menu_link_attributes', 100);

/**
 * ACF Link field values.
 *
 * @param mixed $value Field value.
 * @return mixed
 */
function starter_tunnel_acf_link_value($value)
{
	if (! is_array($value) || empty($value['url']) || ! is_string($value['url'])) {
		return $value;
	}

	$value['url'] = starter_tunnel_rewrite_url($value['url']);

	return $value;
}
add_filter('acf/format_value/type=link', 'starter_tunnel_acf_link_value', 100);

/**
 * Last-resort rewrite for HTML that still embeds localhost.
 *
 * @param string $html HTML.
 */
function starter_tunnel_rewrite_html(string $html): string
{
	$base = starter_tunnel_base_url();
	if ($base === null || $html === '') {
		return $html;
	}

	$rewritten = preg_replace(
		'#https?://(?:localhost|127\.0\.0\.1)(?::\d+)?#i',
		$base,
		$html
	);

	return is_string($rewritten) ? $rewritten : $html;
}

/**
 * @param string $content Post content.
 */
function starter_tunnel_the_content(string $content): string
{
	return starter_tunnel_rewrite_html($content);
}
add_filter('the_content', 'starter_tunnel_the_content', 100);
