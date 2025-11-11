<?php
/**
 * Main initialization class.
 *
 * @package RT_SB_API
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

use RT\ApiForShopbuilder\Controllers\Api\RestApi;
use RT\ApiForShopbuilder\Controllers\Hooks\FilterHooks;
use RT\ApiForShopbuilder\Controllers\Hooks\ActionHooks;


if ( ! class_exists( RtInit::class ) ) {
	/**
	 * Main initialization class.
	 */
	final class RtInit {

		/**
		 * Store the singleton object.
		 *
		 * @var boolean
		 */
		private static $singleton = false;

		/**
		 * Create an inaccessible constructor.
		 */
		private function __construct() {
			$this->__init();
		}

		/**
		 * Fetch an instance of the class.
		 */
		public static function getInstance() {
			if ( false === self::$singleton ) {
				self::$singleton = new self();
			}

			return self::$singleton;
		}

		/**
		 * Class init
		 *
		 * @return void
		 */
		protected function __init() {
			ActionHooks::init();
			FilterHooks::init();
			new RestApi();
			$this->load_hooks();
		}

		/**
		 * Load hooks
		 *
		 * @return void
		 */
		private function load_hooks() {
			add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ], - 1 );
			add_action( 'init', [ $this, 'init_hooks' ], 0 );
		}

		/**
		 * Init hooks
		 *
		 * @return void
		 */
		public function init_hooks() {
			do_action( 'tpg_api_before_init', $this );

			$this->load_language();
		}

		/**
		 * I18n
		 *
		 * @return void
		 */
		public function load_language() {
			do_action( 'tpg_api_set_local', null );
			$locale = determine_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'api-for-shopbuilder' );
			unload_textdomain( 'api-for-shopbuilder' );
			load_textdomain( 'api-for-shopbuilder', WP_LANG_DIR . '/api-for-shopbuilder/api-for-shopbuilder-' . $locale . '.mo' );
			load_plugin_textdomain( 'api-for-shopbuilder', false, plugin_basename( dirname( RT_SB_API_PLUGIN_FILE ) ) . '/languages' );
		}

		/**
		 * Plugin loaded action
		 *
		 * @return void
		 */
		public function on_plugins_loaded() {
			do_action( 'tpg_api_loaded', $this );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( RT_SB_API_PLUGIN_FILE ) );
		}

		/**
		 * Plugin template path
		 *
		 * @return string
		 */
		public function plugin_template_path() {
			$plugin_template = $this->plugin_path() . '/templates/';

			return apply_filters( 'tlp_tpg_template_path', $plugin_template );
		}

		/**
		 * Default template path
		 *
		 * @return string
		 */
		public function default_template_path() {
			return apply_filters( 'tpg_api_default_template_path', untrailingslashit( plugin_dir_path( RT_SB_API_PLUGIN_FILE ) ) );
		}

		/**
		 * Nonce text
		 *
		 * @return string
		 */
		public static function nonceText() {
			return 'tpg_api_nonce_secret';
		}

		/**
		 * Nonce ID
		 *
		 * @return string
		 */
		public static function nonceId() {
			return 'tpg_api_nonce';
		}

		/**
		 * Get assets URI
		 *
		 * @param string $file File.
		 *
		 * @return string
		 */
		public function get_assets_uri( $file ) {
			$file = ltrim( $file, '/' );

			return trailingslashit( RT_SB_API_PLUGIN_URL . '/assets' ) . $file;
		}

		/**
		 * RTL check.
		 *
		 * @param string $file File.
		 *
		 * @return string
		 */
		public function tpg_can_be_rtl( $file ) {
			$file = ltrim( str_replace( '.css', '', $file ), '/' );

			if ( is_rtl() ) {
				$file .= '.rtl';
			}

			return trailingslashit( RT_SB_API_PLUGIN_URL . '/assets' ) . $file . '.min.css';
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function get_template_path() {
			return apply_filters( 'tpg_api_template_path', 'api-for-shopbuilder/' );
		}
	}

	/**
	 * Function for external use.
	 *
	 * @return RtInit
	 */
	function rtTPGApi() {
		return RtInit::getInstance();
	}

	// Init app.
	rtTPGApi();
}
