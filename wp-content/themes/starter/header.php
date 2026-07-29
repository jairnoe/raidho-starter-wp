<?php
/**
 * Theme header + Barba shell open.
 *
 * Site chrome (nav) goes ABOVE the Barba wrapper so it persists across transitions.
 * Content lives inside data-barba="container".
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>

<body <?php body_class('flex flex-col min-h-screen'); ?>>
  <?php wp_body_open(); ?>

  <?php // Site header/nav components go here (outside Barba container). ?>

  <div data-barba="wrapper" class="flex flex-1 flex-col min-h-0">
    <div data-barba="container" data-barba-namespace="<?php echo esc_attr(starter_barba_namespace()); ?>"
      class="flex flex-1 flex-col min-h-0">