<?php
/**
 * Plugin Name: WP My Ads
 * Description: A Classifieds Ads Plugin.
 * Text Domain: wpmyads
 * Version: 1.0.0
 * Plugin URI: https://kadimi.com/
 * Author: Plugible
 * Author URI: https://kadimi.com/
 *
 * @package Plugible\WPMyAds
 */

/**
 * Configuration.
 */
define( 'PLCL_ADS_PER_PAGE', apply_filters( 'plcl_ads_per_page', 20 ) );
define( 'PLCL_INBOX_MESSAGES_PER_PAGE', apply_filters( 'plcl_inbox_messages_per_page', 50 ) );

/**
 * Composer stuff.
 */
require 'vendor/autoload.php';

/**
 * The plugin class.
 */
require 'class-wpmyads.php';

/**
 * Helper function.
 */
function wpmyads() {
	return ( new WPMyAds )::get_instance();
}

/**
 * Fire plugin.
 */
wpmyads();
