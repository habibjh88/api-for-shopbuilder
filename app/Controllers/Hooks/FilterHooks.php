<?php
/**
 * Filter Hooks class.
 *
 * @package RT_SB_API
 */

namespace RT\ThePostGridAPI\Controllers\Hooks;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Filter Hooks class.
 *
 * @package RT_SB_API
 */
class FilterHooks {
	/**
	 * Class init
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'rtsb_register_template_builder_args', [ __CLASS__, 'rtsb_register_template_builder_args' ] );
	}

	public static function rtsb_register_template_builder_args( $args ) {
		$args['supports'][] = 'thumbnail';
		return $args;
	}

}
