<?php
/**
 * Site Starter theme bootstrap.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('STARTER_VERSION', '0.1.0');
define('STARTER_DIR', get_template_directory());
define('STARTER_URI', get_template_directory_uri());

$starter_includes = [
	'/inc/setup.php',
	'/inc/enqueue.php',
	'/inc/icons.php',
	'/inc/helpers.php',
	'/inc/images.php',
	'/inc/acf.php',
	'/inc/blocks.php',
];

foreach ($starter_includes as $file) {
	$path = STARTER_DIR . $file;

	if (file_exists($path)) {
		require_once $path;
	}
}

/**
 * Autoload CPT and taxonomy registrars from dedicated folders.
 */
foreach (glob(STARTER_DIR . '/inc/post-types/*.php') ?: [] as $post_type_file) {
	require_once $post_type_file;
}

foreach (glob(STARTER_DIR . '/inc/taxonomies/*.php') ?: [] as $taxonomy_file) {
	require_once $taxonomy_file;
}
