<?php
/**
 * Header Template
 *
 * Here we setup all logic and XHTML that is required for the header section of all screens.
 * <html <?php language_attributes(); ?>>
 * @package WooFramework
 * @subpackage Template
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> prefix="og: http://ogp.me/ns#">
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>" />
<title><?php woo_title(); ?></title>
<?php woo_meta(); ?>
<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>" />
<?php wp_head(); ?>
<?php woo_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php woo_top(); ?>
<div id="wrapper">

	<div id="inner-wrapper">

	<?php woo_header_before(); ?>

	<header id="header" class="col-full">

		<?php woo_header_inside(); ?>

	</header>
	<?php woo_header_after(); ?>