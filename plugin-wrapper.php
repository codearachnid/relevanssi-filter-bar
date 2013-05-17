<?php

/**
 * Plugin Name: Relevanssi Filter Bar
 * Plugin URI: http://codearachnid.github.io/relevanssi-filter-bar
 * Description: An addon to the Relevanssi search engine that enables a filter bar for display through widget, shortcode or template tag.
 * Version: 1.0
 * Author: Timothy Wood (@codearachnid)
 * Author URI: http://www.codearachnid.com
 * Author Email: tim@imaginesimplicity.com
 * Text Domain: 'relevanssi-filter-bar' 
 * License:
 * 
 *     Copyright 2013 Imagine Simplicity (tim@imaginesimplicity.com)
 *     License: GNU General Public License v3.0
 *     License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * @package relevanssi-filter-bar
 * @category search
 * @author codearachnid
 * 
 */

if ( !defined( 'ABSPATH' ) )
	die( '-1' );

if ( ! class_exists( 'relevanssi_filter_bar' ) ) {
	class relevanssi_filter_bar {

		private static $_instance;

		public $dir;
		public $path;
		public $url;
		public $plugin_data;

		const MIN_WP_VERSION = '3.5';

		function __construct() {

			// register lazy autoloading
			spl_autoload_register( 'self::lazy_loader' );

			$this->plugin_data = get_plugin_data( __FILE__ );

			$this->path = $this->get_plugin_path();
			$this->dir = $this->get_plugin_basename();
			$this->url = plugins_url() . '/' . $this->dir;

			// require updater if in admin
			if (is_admin()) {
				require_once( $this->path . 'github-update/updater.php' );
				$config = array(
					'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
					'proper_folder_name' => 'plugin-name', // this is the name of the folder your plugin lives in
					'api_url' => 'https://api.github.com/repos/username/repository-name', // the github API url of your github repo
					'raw_url' => 'https://raw.github.com/username/repository-name/master', // the github raw url of your github repo
					'github_url' => 'https://github.com/username/repository-name', // the github url of your github repo
					'zip_url' => 'https://github.com/username/repository-name/zipball/master', // the zip url of the github repo
					'sslverify' => true // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
					'requires' => '3.0', // which version of WordPress does your plugin require?
					'tested' => '3.3', // which version of WordPress is your plugin tested up to?
					'readme' => 'README.md', // which file to use as the readme for the version number
					'access_token' => '', // Access private repositories by authorizing under Appearance > Github Updates when this example plugin is installed
					);
				new WP_GitHub_Updater($config);
			}

		}

		public static function lazy_loader( $class_name ) {

			$file = self::get_plugin_path() . 'classes/' . $class_name . '.php';

			if ( file_exists( $file ) )
				require_once $file;

		}

		public function get_plugin_basename(){
			return apply_filters( 'relevanssi_filter_bar/get_plugin_basename', plugin_basename( __FILE__ ) );
		}
		public function get_plugin_path() {
			return apply_filters( 'relevanssi_filter_bar/get_plugin_path', trailingslashit( dirname( __FILE__ ) ) );
		}

		/**
		* Check the minimum WP version and if TribeEvents exists
		*
		* @static
		* @return bool Whether the test passed
		*/
		public static function prerequisites() {;
			$pass = TRUE;
			$pass = $pass && version_compare( get_bloginfo( 'version' ), self::MIN_WP_VERSION, '>=' );
			return apply_filters( 'relevanssi_filter_bar/pre_check', $pass, get_bloginfo( 'version' ), self::MIN_WP_VERSION );
		}

		/**
		 * Provide appropriate fail notices if prereqs are not met used by outside loading methods
		 * 
		 * @static
		 * @return void
		 */
		public static function fail_notices() {
			if( apply_filters( 'relevanssi_filter_bar/show_fail_notices', TRUE ) )
				printf( '<div class="error"><p>%s</p></div>', 
					sprintf( __( '%1$s requires WordPress v%2$s or higher.', 'wp-plugin-framework' ), 
						$this->plugin_data['Name'], 
						self::MIN_WP_VERSION 
					));
		}

		/**
		 * Static singleton factory method
		 * 
		 * @static
		 * @return static $_instance instance
		 * @readlink http://eamann.com/tech/the-case-for-singletons/
		 */
		public static function instance() {
			if ( !isset( self::$_instance ) ) {
				$class_name = __CLASS__;
				self::$_instance = new $class_name;
			}
			return self::$_instance;
		}
	}

  /**
   * Instantiate class and set up WordPress actions.
   *
   * @return void
   */
	function load_relevanssi_filter_bar() {

		// we assume class_exists( 'relevanssi_filter_bar' ) is true
		if ( relevanssi_filter_bar::prerequisites() ) {

			// when plugin is activated let's load the instance to get the ball rolling
			add_action( 'init', array( 'relevanssi_filter_bar', 'instance' ), -100, 0 );

		} else {

			// let the user know prerequisites weren't met
			add_action( 'admin_head', array( 'relevanssi_filter_bar', 'fail_notices' ), 0, 0 );

		}
	}

	// high priority so that it's not too late for addon overrides
	add_action( 'plugins_loaded', 'load_relevanssi_filter_bar' );

}
