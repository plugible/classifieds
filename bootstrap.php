<?php
/**
 * Plugin Name: WP My Ads
 * Description: Classifieds Ads Plugin.
 * Text Domain: wpmyads
 * Version: 1.0.0
 * Plugin URI: https://www.github.com/plugible/classifieds
 * Author: Plugible
 * Author URI: https://plugible.com
 *
 * @package Plugible\Classifieds
 */

/**
 * Configuration.
 */
define( 'PLCL_ADS_PER_PAGE', apply_filters( 'plcl_ads_per_page', 8 ) );
define( 'PLCL_INBOX_MESSAGES_PER_PAGE', apply_filters( 'plcl_inbox_messages_per_page', 1 ) );

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
