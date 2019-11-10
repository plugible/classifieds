<?php
/**
 * Plugin Name: Classifieds by Plugible
 * Description: Classifieds by Plugible.
 * Text Domain: classifieds_by_plugible
 * Version: 1.0.0
 * Plugin URI: https://www.github.com/plugible/classifieds
 * Author: Plugible
 * Author URI: https://plugible.com
 *
 * @package Plugible\Classifieds
 */

/**
 * Composer stuff.
 */
require 'vendor/autoload.php';

/**
 * Helper function.
 */
function classifieds_by_plugible() {
	return ( new class extends Kadimi\WPStarter {} )::get_instance();
}

/**
 * Fire plugin.
 */
classifieds_by_plugible();
