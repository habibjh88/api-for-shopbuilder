<?php
/**
 * Filter Hooks class.
 *
 * @package RT_SB_API
 */

namespace RT\ApiForShopbuilder\Controllers\Hooks;

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
		add_filter( 'rtsb/builder/template_args', [ __CLASS__, 'rtsb_register_template_builder_args' ] );
		add_filter( 'post_row_actions', [ __CLASS__, 'rtsb_template_row_actions' ], 99, 2 );
	}

	/**
	 * Add template post type supports.
	 *
	 * @param array $args Post type args.
	 *
	 * @return array
	 */
	public static function rtsb_register_template_builder_args( $args ) {
		$args['supports'][] = 'thumbnail';
		$args['supports'][] = 'page-attributes';

		return $args;
	}

	/**
	 * Add layout ID to row actions.
	 *
	 * @param array    $actions Row actions.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return array
	 */
	public static function rtsb_template_row_actions( $actions, $post ) {
		global $pagenow, $typenow;
		if ( 'edit.php' === $pagenow && 'rtsb_builder' === $typenow ) {
			$actions['layout-id'] = "<span style='color:#000'>{$post->ID}</span>";
		}

		return $actions;
	}
}
