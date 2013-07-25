<?php
/* ------------------------------------- */
/* PAGE/POST/PORTFOLIO OPTIONS */
/* ------------------------------------- */

// Prefix for Page/Post/Portfolio Options
$prefix="paws_";

// Load the Page Option Meta Fields
$custom_meta_fields=array();
require_once(PAWS_FUNCTIONS . '/page_options/theme_page_custom_meta.php');
require_once(PAWS_FUNCTIONS . '/page_options/theme_post_custom_meta.php');

// Generate Page/Post/Portfolio Options
add_action('admin_init', 'init_page_options');
function init_page_options() {
	add_meta_box("page-options", "Page Options", "show_custom_page_meta_box", "page", "normal", "high");
	add_meta_box("post-options", "Post Options", "show_custom_post_meta_box", "post", "normal", "high");
}

// Include the Page Option Framework Functions
require_once(PAWS_FUNCTIONS . '/page_options/theme_page_options_functions.php');
?>