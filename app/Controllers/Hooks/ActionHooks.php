<?php
/**
 * Action Hooks class.
 *
 * @package RT_SB_API
 */

namespace RT\ApiForShopbuilder\Controllers\Hooks;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Action Hooks class.
 */
class ActionHooks {
	/**
	 * Class init.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'rtsb_template_status_taxonomies' ], 10 );
	}

	/**
	 * Create two taxonomies, genres and writers for the post type "book".
	 *
	 * @see register_post_type() for registering custom post types.
	 */
	public static function rtsb_template_status_taxonomies() {
		// Add new taxonomy, make it hierarchical (like categories).
		$labels = [
			'name'              => _x( 'Status', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Status', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Status', 'textdomain' ),
			'all_items'         => __( 'All Status', 'textdomain' ),
			'parent_item'       => __( 'Parent Status', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Status:', 'textdomain' ),
			'edit_item'         => __( 'Edit Status', 'textdomain' ),
			'update_item'       => __( 'Update Status', 'textdomain' ),
			'add_new_item'      => __( 'Add New Status', 'textdomain' ),
			'new_item_name'     => __( 'New Status Name', 'textdomain' ),
			'menu_name'         => __( 'Status', 'textdomain' ),
		];
		$args   = [
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [ 'slug' => 'rtsb-status' ],
		];
		register_taxonomy( 'rtsb_status', [ 'rtsb_builder' ], $args );
	}
}
