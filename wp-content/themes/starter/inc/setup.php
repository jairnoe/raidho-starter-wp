<?php
/**
 * Theme supports.
 *
 * Keep minimal; add menus, image sizes, and logo support when the UI needs them.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register theme supports.
 */
function starter_setup(): void
{
	load_theme_textdomain('starter', STARTER_DIR . '/languages');

	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	]);
}
add_action('after_setup_theme', 'starter_setup');

/**
 * Flush rewrite rules when the theme is switched so CPT archives resolve.
 * Register new CPT/taxonomy callbacks here as you add them.
 */
function starter_after_switch_theme(): void
{
	flush_rewrite_rules();
}
add_action('after_switch_theme', 'starter_after_switch_theme');

/**
 * Use the classic editor for field-heavy CPTs so ACF groups are obvious.
 * Pages can keep the block editor.
 *
 * Add CPT slugs to this list when you create field-heavy post types.
 *
 * @param bool   $use_block_editor Whether the post type uses the block editor.
 * @param string $post_type        Post type slug.
 */
function starter_disable_block_editor_for_cpts(bool $use_block_editor, string $post_type): bool
{
	$classic_editor_post_types = [
		// e.g. 'publication',
	];

	if (in_array($post_type, $classic_editor_post_types, true)) {
		return false;
	}

	return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'starter_disable_block_editor_for_cpts', 10, 2);
