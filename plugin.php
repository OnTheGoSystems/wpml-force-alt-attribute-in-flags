<?php
/*
Plugin Name: WPML Force alt attribute in flags
Plugin URI: http://wpml.org
Description: Always add the alt attribute to all language flags, even when not necessary
Version: 0.0.1
Author: Andrea Sciamanna
Author URI: https://www.onthegosystems.com/team/andrea-sciamanna/
*/

namespace WPML\Core;

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once __DIR__ . '/src/ForceAltAttributeInFlags.php';

$wpml_notify_on_post_update = new ForceAltAttributeInFlags();
$wpml_notify_on_post_update->init_hooks();
