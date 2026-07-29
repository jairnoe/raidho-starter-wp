<?php
/**
 * Plugin Name: Fix IPv6 localhost canonical redirect
 * Description: Stops redirect_canonical from sending [::1]:8888 → http://[::1]/ (port 80). Prefer http://127.0.0.1:8888. Opt-in via this mu-plugin.
 *
 * @package Starter
 */

declare(strict_types=1);

add_filter(
	'redirect_canonical',
	static function ($redirect_url, string $requested_url) {
		if (! is_string($redirect_url) || $redirect_url === '') {
			return $redirect_url;
		}

		if (
			preg_match('#^https?://\[::1\](?::80)?(?:/|$)#i', $redirect_url)
			&& ! str_contains($redirect_url, ':8888')
			&& ! str_contains($redirect_url, ':8889')
		) {
			return false;
		}

		$host = $_SERVER['HTTP_HOST'] ?? '';
		if (is_string($host) && (str_contains($host, '[::1]') || $host === '::1')) {
			return false;
		}

		return $redirect_url;
	},
	10,
	2
);
