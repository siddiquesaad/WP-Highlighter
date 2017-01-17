<?php
/*
Plugin Name: WP Highlighter
Version: 1.0.2
Description: Show backdrop and highlight any element with related classes on page.
Author: saadsiddique.ca
Author URI: hello@saadsiddique.ca
Text Domain: wp-highlighter
*/
define( 'WP_HIGHLIGHTER_VERSION', '1.0.2' );

define( 'WP_HIGHLIGHTER_FILE', __FILE__ );

// Load all plugin classes(functionality)
include_once( dirname( __FILE__ ) . '/src/boot.php' );

$wp_highlighter_class = new \ss_wp_highlighter\Boot();
$wp_highlighter_class::init();
