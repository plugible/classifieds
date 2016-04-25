<?php
/**
 * Plugin Name: Starter
 * Plugin URI: https://www.github.com/kadimi/starter
 * Description: Starter plugin.
 * Version: 1.0.0
 * Author: Nabil Kadimi
 * Author URI: http://kadimi.com
 * Text Domain: starter
 * License: GPL2
 *
 * @package starter
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * Starter class.
 */
class Starter {

	/**
	 * Class instance
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Plugin data.
	 *
	 * @var Array
	 */
	protected $plugin_data;

	/**
	 * Plugin directory path.
	 *
	 * @var String
	 */
	protected $plugin_dir_path;

	/**
	 * Plugin directory ULR.
	 *
	 * @var String
	 */
	protected $plugin_dir_url;

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Cloner
	 */
	public function __clone() {
	}

	/**
	 * Returns a new or the existing instance of this class
	 *
	 * @return Object
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new static;
			add_action('init', function() {
				self::$instance->init();
			} );
		}
		return self::$instance;
	}

	/**
	 * Initializes plugin
	 */
	protected function init() {
		$this->plugin_data = get_plugin_data( __FILE__ );
		$this->plugin_dir_path = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( __FILE__ );
		$this->plugin_slug = self::camel_case_to_snake_case( __CLASS__ );
		$this->autoload();
		$this->activate();
		// $this->require_plugin('Piklist');
	}

	/**
	 * Requires Composer generated autoload file
	 */
	protected function autoload() {
		$autoload_file_path = $this->plugin_dir_path . 'vendor/autoload.php';
		if ( file_exists( $autoload_file_path ) ) {
			require $autoload_file_path;
		} else {
			deactivate_plugins( __FILE__ );
			wp_die( sprintf( __( 'Plugin <strong>%s</strong> not installed yet, run the `<strong><code>composer install</code></strong>` command on a terminal from within the plugin directory and activate the plugin again from the <a href="%s">plugins page</a>.', $this->plugin_data['TextDomain'] ), $this->plugin_data['Name'], admin_url( 'plugins.php' ) ) ); // XSS OK.
		}
	}

	/**
	 * Runs on plugin actication
	 */
	protected function activate() {
		register_activation_hook( __FILE__, function() {
			set_transient( $this->plugin_slug, 1, self::in( '15 minutes' ) );
		});
	}

	/**
	 * Requires a plugin
	 *
	 * @param  String $name    Plugin name.
	 * @param  Array  $options TGMPA compatible options.
	 */
	protected function require_plugin( $name, $options = [] ) {
		add_action('tgmpa_register', function() use ( $name, $options ) {
			$options['name'] = $name;
			$options['slug'] = ! empty( $options['slug'] )
				? $options['slug']
				: strtolower( preg_replace( '/[^\w\d]+/', '-', $name ) );
			$options['required'] = true;
			tgmpa( [ $options ] );
		});
	}

	/**
	 * Converts a string from camelCase to snake_case
	 *
	 * @param  String $str camelCase.
	 * @return String      snake_case.
	 */
	static function camel_case_to_snake_case( $str ) {
		preg_match_all( '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $str, $matches );
		foreach ( $matches[0] as &$match ) {
			if ( strtoupper( $match ) == $match ) {
				$match = strtolower( $match );
			} else {
				$match = lcfirst( $match );
			}
		}
		return implode( '_', $matches[0] );
	}

	/**
	 * Returns number of seconds to given time
	 *
	 * @param  string $time Time.
	 * @return int          Seconds to time.
	 */
	static function in( $time ) {
		return strftime( $time ) - time();
	}
}

$starter = Starter::get_instance();
