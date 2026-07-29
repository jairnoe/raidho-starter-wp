<?php
/**
 * Plugin Name: ACF textdomain notice (quiet)
 * Description: Silences WP 6.7+ _load_textdomain_just_in_time notice for the acf domain (old Pro builds). Prefer a recent ACF Pro; this is temporary/opt-in.
 *
 * @package Starter
 */

declare(strict_types=1);

add_filter(
	'doing_it_wrong_trigger_error',
	static function (bool $trigger, string $function, string $message): bool {
		if ($function === '_load_textdomain_just_in_time' && str_contains($message, 'acf')) {
			return false;
		}

		return $trigger;
	},
	10,
	3
);
