<?php
/*
Plugin Name: Kwik Solutions
Plugin URI: http://kevin-chappell.com/kwik-solutions
Description: Display and manage your solutions and their logos. Works well for attributing resources or portfolio work.
Author: Kevin Chappell
Version: .5.5
Author URI: http://kevin-chappell.com
 */


define( 'K_SOLUTIONS_BASENAME', basename( dirname( __FILE__ ) ) );
define( 'K_SOLUTIONS_SETTINGS', preg_replace( '/-/', '_', K_SOLUTIONS_BASENAME ).'_settings' );
define( 'K_SOLUTIONS_URL', plugins_url( '', __FILE__ ) );
define( 'K_SOLUTIONS_PATH', dirname( __FILE__ ) );
define( 'K_SOLUTIONS_CPT', 'solutions' );

// Cleanup on deactivation
register_activation_hook( __FILE__, 'ks_activate' );
register_deactivation_hook( __FILE__, 'ks_deactivate' );

function ks_activate() {
	include_once K_SOLUTIONS_PATH . '/inc/class.kwik-solutions-settings.php';
	$settings = new KwikSolutionsSettings();
	$options = $settings->get_options();
	if ( ! empty( $options ) ) {
		return false;
	}
	$defaults = $settings->set_defaults();
	flush_rewrite_rules( false );
	add_option( K_SOLUTIONS_SETTINGS, $defaults, '' );
}

function ks_deactivate() {
	flush_rewrite_rules( false );
}


// Load the core.
require_once __DIR__ . '/inc/class.kwik-solutions.php';
kwiksolutions();
