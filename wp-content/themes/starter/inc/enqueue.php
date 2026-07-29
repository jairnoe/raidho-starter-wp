<?php
/**
 * Styles and scripts.
 *
 * Global by default (starter convention):
 * - Theme CSS (`output.css`)
 * - Barba.js + `page-transitions.js` (SPA-like fades; needs GSAP)
 *
 * Lazy / per-feature:
 * - GSAP / ScrollTrigger — `starter_enqueue_gsap()` / `starter_enqueue_gsap(true)`
 * - Swiper (when added) — same register + enqueue pattern
 *
 * Shell: header/footer outside [data-barba="container"] so chrome persists.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Enqueue front-end CSS + Barba page transitions.
 */
function starter_enqueue_assets(): void
{
	$css_path = STARTER_DIR . '/assets/css/output.css';

	wp_enqueue_style(
		'starter-main',
		STARTER_URI . '/assets/css/output.css',
		[],
		file_exists($css_path) ? (string) filemtime($css_path) : STARTER_VERSION
	);

	starter_enqueue_page_transitions();
}
add_action('wp_enqueue_scripts', 'starter_enqueue_assets');

/**
 * Barba + page-transitions (default of the starter — not per-page opt-in).
 */
function starter_enqueue_page_transitions(): void
{
	$barba_path = STARTER_DIR . '/assets/js/barba.umd.js';
	$app_path   = STARTER_DIR . '/assets/js/page-transitions.js';

	if (! file_exists($barba_path) || ! file_exists($app_path)) {
		return;
	}

	// Fades need GSAP; ScrollTrigger stays lazy.
	starter_enqueue_gsap();

	wp_enqueue_script(
		'starter-barba',
		STARTER_URI . '/assets/js/barba.umd.js',
		[],
		(string) filemtime($barba_path),
		true
	);

	wp_enqueue_script(
		'starter-page-transitions',
		STARTER_URI . '/assets/js/page-transitions.js',
		['starter-barba', 'starter-gsap'],
		(string) filemtime($app_path),
		true
	);
}

/**
 * Register GSAP (+ optional ScrollTrigger).
 */
function starter_register_gsap(): void
{
	$gsap_path = STARTER_DIR . '/assets/js/gsap.min.js';
	$st_path   = STARTER_DIR . '/assets/js/ScrollTrigger.min.js';

	if (! file_exists($gsap_path)) {
		return;
	}

	wp_register_script(
		'starter-gsap',
		STARTER_URI . '/assets/js/gsap.min.js',
		[],
		(string) filemtime($gsap_path),
		true
	);

	if (file_exists($st_path)) {
		wp_register_script(
			'starter-scrolltrigger',
			STARTER_URI . '/assets/js/ScrollTrigger.min.js',
			['starter-gsap'],
			(string) filemtime($st_path),
			true
		);
	}
}
add_action('wp_enqueue_scripts', 'starter_register_gsap', 5);

/**
 * Enqueue GSAP on the current request (also used by page transitions).
 *
 * @param bool $with_scroll_trigger Also enqueue ScrollTrigger.
 */
function starter_enqueue_gsap(bool $with_scroll_trigger = false): void
{
	starter_register_gsap();

	wp_enqueue_script('starter-gsap');

	if ($with_scroll_trigger) {
		wp_enqueue_script('starter-scrolltrigger');
	}
}
