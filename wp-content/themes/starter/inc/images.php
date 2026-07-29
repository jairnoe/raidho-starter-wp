<?php
/**
 * Image helpers.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * URI for a theme asset under /assets/.
 *
 * @param string $relative Path relative to the theme assets directory.
 */
function starter_asset(string $relative): string
{
	$relative = ltrim($relative, '/');

	return STARTER_URI . '/assets/' . $relative;
}

/**
 * Render a theme static image from /assets/images/.
 *
 * @param string $filename Image filename.
 * @param string $alt      Alt text.
 * @param string $class    Optional CSS classes.
 */
function starter_theme_image(string $filename, string $alt = '', string $class = ''): void
{
	$src = starter_asset('images/' . ltrim($filename, '/'));
	?>
	<img
		src="<?php echo esc_url($src); ?>"
		alt="<?php echo esc_attr($alt); ?>"
		<?php echo $class !== '' ? ' class="' . esc_attr($class) . '"' : ''; ?>
		decoding="async"
	>
	<?php
}
