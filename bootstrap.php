<?php
/**
 * Plugin Name: Classifieds by Plugible
 * Description: Classifieds by Plugible.
 * Text Domain: classifieds-by-plugible
 * Version: 1.0.0
 * Plugin URI: https://www.github.com/plugible/classifieds
 * Author: Plugible
 * Author URI: https://plugible.com
 * Plugin Type: Piklist
 *
 * @package Plugible\Classifieds
 */

/**
 * Composer stuff.
 */
require 'vendor/autoload.php';

/**
 * The plugin class.
 */
require 'class-classifieds.php';

/**
 * Helper function.
 */
function classifieds_by_plugible() {
	return ( new ClassifiedsByPlugible )::get_instance();
}

/**
 * Fire plugin.
 */
classifieds_by_plugible();
