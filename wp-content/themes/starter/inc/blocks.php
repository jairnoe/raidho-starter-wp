<?php
/**
 * ACF block registration.
 *
 * Name blocks by reusable composition (hero, media-text, project-grid),
 * never by page (index-hero, about-hero). Prefer one block + ACF variants.
 *
 * Never use category: 'layout' — it is not a core inserter category.
 * Use the theme category registered below, or a core one (design, media, text).
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register a theme block category so ACF blocks appear in the inserter.
 *
 * @param array<int, array<string, mixed>> $categories Existing categories.
 * @return array<int, array<string, mixed>>
 */
function starter_block_categories(array $categories): array
{
	array_unshift(
		$categories,
		[
			'slug'  => 'starter',
			'title' => __('Site Starter', 'starter'),
			'icon'  => null,
		]
	);

	return $categories;
}
add_filter('block_categories_all', 'starter_block_categories');

/**
 * Register theme ACF blocks.
 *
 * Add entries as you create folders under /blocks/{slug}/{slug}.php.
 * Use category "starter" (or a core category). Client-facing titles/instructions only.
 */
function starter_register_acf_blocks(): void
{
	if (! function_exists('acf_register_block_type')) {
		return;
	}

	$blocks = [
		// Example (do not ship client-specific blocks in the starter):
		// [
		// 	'name'            => 'media-text',
		// 	'title'           => __('Media + Text', 'starter'),
		// 	'description'     => __('Image beside text. Choose layout in the sidebar.', 'starter'),
		// 	'render_template' => STARTER_DIR . '/blocks/media-text/media-text.php',
		// 	'category'        => 'starter',
		// 	'icon'            => 'align-pull-left',
		// 	'keywords'        => ['media', 'text'],
		// 	'mode'            => 'edit',
		// 	'supports'        => [
		// 		'align'  => false,
		// 		'mode'   => true,
		// 		'jsx'    => false,
		// 		'anchor' => true,
		// 	],
		// ],
	];

	foreach ($blocks as $block) {
		acf_register_block_type($block);
	}
}
add_action('acf/init', 'starter_register_acf_blocks');
