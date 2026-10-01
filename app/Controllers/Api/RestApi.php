<?php
/**
 * Action Hooks class.
 *
 * @package RT_SB_API
 */

namespace RT\ApiForShopbuilder\Controllers\Api;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Fns;

/**
 * REST API class.
 *
 * @package RT_SB_API
 */
class RestApi {
	/**
	 * REST namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'rtsb/v1';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ], 15 );
		add_filter( 'rest_pre_serve_request', [ $this, 'send_cors_headers' ], 11, 3 );
	}

	/**
	 * Register rest routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'layouts',
			[
				'methods'             => [ 'GET', 'POST' ],
				'callback'            => [ $this, 'get_layouts' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Send cache-safe CORS headers for this plugin's routes only.
	 *
	 * A static wildcard origin is used instead of reflecting the request
	 * origin, so proxy-cached responses stay valid for every site.
	 *
	 * @param bool             $served  Whether the request has already been served.
	 * @param mixed            $result  Result to send to the client.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 *
	 * @return bool
	 */
	public function send_cors_headers( $served, $result, $request ) {
		if ( ! $request instanceof \WP_REST_Request || 0 !== strpos( $request->get_route(), '/' . self::REST_NAMESPACE . '/' ) || headers_sent() ) {
			return $served;
		}

		header_remove( 'Access-Control-Allow-Credentials' );
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Methods: GET, OPTIONS' );
		header( 'Access-Control-Allow-Headers: Content-Type' );

		return $served;
	}

	/**
	 * Get Layout.
	 *
	 * @param \WP_REST_Request $data Request.
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function get_layouts( $data ) {
		$send_data = [
			'layouts' => [],
			'message' => '',
		];
		if ( ! empty( $data['layout_id'] ) ) {
			unset( $send_data['layouts'] );
			$el_data          = get_post_meta( $data['layout_id'], '_elementor_data', true );
			$templaate_status = get_the_terms( $data['layout_id'], 'rtsb_status' );
			$status           = wp_list_pluck( $templaate_status, 'slug' );
			if ( ! empty( $el_data ) && ! ( 'no' === $data['has_pro'] && 'pro' === $status[0] ) ) {
				$send_data['data']    = $el_data;
				$send_data['success'] = 'ok';
			} else {
				$send_data['message'] = 'No data found';
				$send_data['success'] = 'error';
			}
			return rest_ensure_response( $send_data );
		}
		// TODO: Query for layouts.
		$args         = [
			'post_type'      => [ BuilderFns::$post_type_tb ],
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'orderby'        => [
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			],
		];
		$layout_query = new \WP_Query( $args );
		if ( $layout_query->have_posts() ) {
			while ( $layout_query->have_posts() ) {
				$layout_query->the_post();
				$pid                    = get_the_id();
				$img_url                = esc_url_raw( get_the_post_thumbnail_url( $pid, 'full' ) );
				$template_type          = BuilderFns::builder_type( $pid );
				$editor_type            = Fns::page_edit_with( $pid );
				$templaate_status       = get_the_terms( $pid, 'rtsb_status' );
				$status                 = wp_list_pluck( $templaate_status, 'slug' );
				$send_data['layouts'][] = [
					'id'            => $pid,
					'image_url'     => $img_url,
					'title'         => html_entity_decode( get_the_title() ),
					'post_class'    => join( ' ', get_post_class( null, $pid ) ),
					'preview_link'  => get_the_permalink( $pid ),
					'template_type' => $template_type,
					'editor_type'   => $editor_type,
					'status'        => ! empty( $status ) ? $status[0] : 'free',
					'menu_order'    => (int) get_post_field( 'menu_order', $pid ),
				];
				$send_data['success']   = 'ok';
			}
		} else {
			$send_data['success'] = 'error';
			$send_data['message'] = __( 'No posts found', 'api-for-shopbuilder' );
		}
		wp_reset_postdata();
		return rest_ensure_response( $send_data );
	}
}
