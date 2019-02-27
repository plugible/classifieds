<?php

/**
 * Plugin Name: My Plugin
 * Description: My Plugin Description.
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
 * Create the plugin class.
 */
require 'class-starter.php';
class MyPlugin extends Starter {};

/**
 * Create a shortcut for ease of use.
 */
function myplugin() {
	return MyPlugin::get_instance();
}

/**
 * Fire plugin.
 */
myplugin();
