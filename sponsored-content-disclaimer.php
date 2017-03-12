<?php
/*
Plugin Name: Sponsored Content Disclaimer
Plugin URI: https://github.com/citymomsblog/sponsored-content-disclaimer
Description: Add a custom disclaimer message to any blog post. The message can be displayed either above or below the content. Add custom CSS to provide a consistent style across your blog.
Version: 1.0
Author: John Lane
Author URI: http://www.citymomsblog.com
License: GPLv2 or later
*/

// don't load directly
if (!defined('ABSPATH')) die('-1');
define( 'SCD_FILE', __FILE__ );

if( is_admin() ) {
	require_once dirname( SCD_FILE ) . '/includes/scd-backend.php';
} else {
	require_once dirname( SCD_FILE ) . '/includes/scd-frontend.php';
}
