<?php
/**
 * Plugin Name: MyPlugin
 * Description: MyPlugin Description.
 * Text Domain: myplugin
 * Version: 1.0.0
 * Plugin URI: https://www.github.com/kadimi/starter
 * GitHub Plugin URI: https://github.com/kadimi/starter
 * Author: Nabil Kadimi
 * Author URI: https://kadimi.com
 *
 * @package MyPlugin
 */

/**
 * Composer stuff.
 */
require 'vendor/autoload.php';

/**
 * Helper function.
 */
function myplugin() {
	return ( new class extends Kadimi\WPStarter {} )::get_instance();
}

/**
 * Fire plugin.
 */
myplugin();
