<?php
/**
 * Template Order class.
 *
 * Adds an editable "Order" column to the ShopBuilder template list
 * and saves the value to the native `menu_order` field via AJAX.
 *
 * @package RT_SB_API
 */

namespace RT\ApiForShopbuilder\Controllers\Admin;

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}

/**
 * Template Order class.
 *
 * @package RT_SB_API
 */
class TemplateOrder {

	/**
	 * Template post type.
	 *
	 * @var string
	 */
	const POST_TYPE = 'rtsb_builder';

	/**
	 * Column key.
	 *
	 * @var string
	 */
	const COLUMN = 'rtsb_order';

	/**
	 * AJAX action.
	 *
	 * @var string
	 */
	const AJAX_ACTION = 'rtsb_api_save_template_order';

	/**
	 * Script handle.
	 *
	 * @var string
	 */
	const HANDLE = 'rtsb-api-template-order';

	/**
	 * Class init.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', [ __CLASS__, 'add_column' ], 20 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', [ __CLASS__, 'sortable_column' ] );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ __CLASS__, 'save_order' ] );
	}

	/**
	 * Insert the Order column right before the date column.
	 *
	 * @param array $columns List table columns.
	 *
	 * @return array
	 */
	public static function add_column( $columns ) {
		$new_columns = [];

		foreach ( $columns as $key => $label ) {
			if ( 'date' === $key ) {
				$new_columns[ self::COLUMN ] = esc_html__( 'Order', 'api-for-shopbuilder' );
			}

			$new_columns[ $key ] = $label;
		}

		if ( ! isset( $new_columns[ self::COLUMN ] ) ) {
			$new_columns[ self::COLUMN ] = esc_html__( 'Order', 'api-for-shopbuilder' );
		}

		return $new_columns;
	}

	/**
	 * Make the Order column sortable by menu_order.
	 *
	 * @param array $columns Sortable columns.
	 *
	 * @return array
	 */
	public static function sortable_column( $columns ) {
		$columns[ self::COLUMN ] = 'menu_order';

		return $columns;
	}

	/**
	 * Render the Order column input.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 *
	 * @return void
	 */
	public static function render_column( $column, $post_id ) {
		if ( self::COLUMN !== $column ) {
			return;
		}

		printf(
			'<input type="number" class="rtsb-api-template-order small-text" min="0" step="1" value="%1$s" data-id="%2$s" aria-label="%3$s">',
			esc_attr( (int) get_post_field( 'menu_order', $post_id ) ),
			esc_attr( absint( $post_id ) ),
			esc_attr__( 'Template order', 'api-for-shopbuilder' )
		);
	}

	/**
	 * Enqueue the inline editor script on the template list screen.
	 *
	 * @param string $hook_suffix Current admin page.
	 *
	 * @return void
	 */
	public static function enqueue_scripts( $hook_suffix ) {
		if ( 'edit.php' !== $hook_suffix ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_script(
			self::HANDLE,
			\rtTPGApi()->get_assets_uri( 'js/template-order.js' ),
			[ 'jquery' ],
			RT_SB_API_VERSION,
			true
		);

		wp_register_style( self::HANDLE, false, [], RT_SB_API_VERSION );
		wp_enqueue_style( self::HANDLE );
		wp_add_inline_style( self::HANDLE, self::get_inline_css() );

		wp_localize_script(
			self::HANDLE,
			'rtsbApiTemplateOrder',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'action'  => self::AJAX_ACTION,
				'nonce'   => wp_create_nonce( \RtInit::nonceText() ),
			]
		);
	}

	/**
	 * Compact styles for the Order column.
	 *
	 * @return string
	 */
	private static function get_inline_css() {
		return '.wp-list-table .column-' . self::COLUMN . '{width:72px;}'
			. '.wp-list-table input.rtsb-api-template-order{width:52px;min-height:24px;height:24px;padding:0 2px 0 6px;font-size:12px;line-height:22px;border-radius:3px;}'
			. '.rtsb-api-template-order-status{font-size:12px;}';
	}

	/**
	 * AJAX: save a template's menu_order.
	 *
	 * @return void
	 */
	public static function save_order() {
		check_ajax_referer( \RtInit::nonceText(), 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		$order   = isset( $_POST['order'] ) ? absint( wp_unslash( $_POST['order'] ) ) : 0;

		if ( ! $post_id || self::POST_TYPE !== get_post_type( $post_id ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Invalid template.', 'api-for-shopbuilder' ) ],
				400
			);
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'You are not allowed to edit this template.', 'api-for-shopbuilder' ) ],
				403
			);
		}

		$result = wp_update_post(
			[
				'ID'         => $post_id,
				'menu_order' => $order,
			],
			true
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				[ 'message' => esc_html( $result->get_error_message() ) ],
				500
			);
		}

		wp_send_json_success(
			[
				'post_id' => $post_id,
				'order'   => (int) get_post_field( 'menu_order', $post_id ),
			]
		);
	}
}
