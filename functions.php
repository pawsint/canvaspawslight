<?php
define('PAWS_FUNCTIONS', get_stylesheet_directory() . '/functions');
define('PAWS_THEME', get_template_directory_uri() );
define('PAWS_JAVASCRIPT', get_stylesheet_directory_uri() . '/js');
define('PAWS_SHORTCODES', get_stylesheet_directory_uri() . '/shortcodes');
define('PAWS_CSS', get_stylesheet_directory_uri() . '/css');
define('PAWS_TYPE', get_stylesheet_directory_uri() . '/font');
define('PAWS_FUNCTIONS_DIR', get_stylesheet_directory_uri() . '/functions');
//--------------------------------------------------------------------------------------------------------------------------
// LOAD PARENT STYLE SHEET
//--------------------------------------------------------------------------------------------------------------------------
add_action('wp_head','load_parent_style',0);
	function load_parent_style() {
		wp_register_style('parent-theme',get_bloginfo('template_directory').'/style.css');
 		wp_enqueue_style('parent-theme');
	}
//--------------------------------------------------------------------------------------------------------------------------
// LOAD ADDITIONAL FUNCTIONS
//--------------------------------------------------------------------------------------------------------------------------
$template = get_option('template');
if ( $template == 'canvas' ) {
	require_once( 'class-canvas-advanced-addons-extended.php' );
	global $canvas_advanced_addons;
	$canvas_advanced_addons = new Canvas_Advanced_Addons_Extended( __FILE__ );
}

if (is_admin()){
	require_once(PAWS_FUNCTIONS . '/page_options/theme_page_options.php');
}


?>