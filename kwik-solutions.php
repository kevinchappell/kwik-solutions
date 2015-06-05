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

// Load the core.
require_once __DIR__ . '/inc/class.kwik-solutions.php';
kwiksolutions();
