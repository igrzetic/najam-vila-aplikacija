<?php
/**
 * Template Name: Custom Login (Blank)
 * Description: A blank page template that bypasses Astra header/footer to fully control layout.
 */

defined('ABSPATH') || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class('custom-login'); ?>>
  <?php
    // Render your custom login form markup
    echo include get_stylesheet_directory() . '/custom/login-form.php';
  ?>
  <?php wp_footer(); ?>
</body>
</html>
