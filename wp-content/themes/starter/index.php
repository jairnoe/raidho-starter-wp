<?php
/**
 * Main template — Hello world inside the Barba shell.
 *
 * @package Starter
 */

declare(strict_types=1);

get_header();
?>

<main>
	<h1><?php esc_html_e('Hello world', 'starter'); ?></h1>
</main>

<?php
get_footer();
