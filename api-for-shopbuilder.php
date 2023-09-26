<?php
/**
 * Plugin Name: API for Shopbuilder
 * Plugin URI: https://shopbuilderwp.com/
 * Description: This plugin created for make api
 * Author: RadiusTheme
 * Version: 1.0.0
 * Text Domain: api-for-shopbuilder
 * Domain Path: /languages
 * Author URI: https://radiustheme.com/
 *
 * @package RT_SB_API
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

define( 'RT_SB_API_VERSION', '1.0.0' );
define( 'RT_SB_API_AUTHOR', 'RadiusTheme' );
define( 'RT_SB_API_NAME', 'The Post Grid' );
define( 'RT_SB_API_PLUGIN_FILE', __FILE__ );
define( 'RT_SB_API_PLUGIN_PLUGIN_BASE', plugin_basename( RT_SB_API_PLUGIN_FILE ) );
define( 'RT_SB_API_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'RT_SB_API_PLUGIN_ACTIVE_FILE_NAME', plugin_basename( __FILE__ ) );
define( 'RT_SB_API_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'RT_SB_API_PLUGIN_SLUG', basename( dirname( __FILE__ ) ) );
define( 'RT_SB_API_LANGUAGE_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages' );


if ( ! class_exists( 'RtInit' ) ) {
	require_once 'app/RtInit.php';
}