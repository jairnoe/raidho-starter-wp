<?php
/**
 * Theme footer + Barba shell close.
 *
 * Site footer goes BELOW the Barba wrapper so it persists across transitions.
 *
 * @package Starter
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
?>
	</div><!-- data-barba="container" -->
</div><!-- data-barba="wrapper" -->

<?php // Site footer components go here (outside Barba container). ?>

<?php wp_footer(); ?>
</body>
</html>
