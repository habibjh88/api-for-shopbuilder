<?php
/**
 * Action Hooks class.
 *
 * @package RT_SB_API
 */

namespace RT\ThePostGridAPI\Controllers\Api;

use RadiusTheme\SB\Helpers\BuilderFns;
use RadiusTheme\SB\Helpers\Fns;

class GetElLayoutsV1 {
	public function __construct() {
		add_action( "rest_api_init", [ $this, 'register_post_route' ] );
	}

	public function register_post_route() {

		register_rest_route( 'rtsb/v1', 'layouts', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'get_layouts' ],
			'permission_callback' => function () {
				return true;
			}
		] );
	}

	/**
	 * Get Layout.
	 *
	 * @param $data
	 *
	 * @return \WP_Error|\WP_HTTP_Response|\WP_REST_Response
	 */
	public function get_layouts( $data ) {

		$send_data = [
			'layouts' => [],
			'message' => ''
		];

		if ( ! empty( $data['layout_id'] ) ) {
			unset( $send_data['layouts'] );
			$el_data = get_post_meta( $data['layout_id'], '_elementor_data', true );
			if ( ! empty( $el_data ) && 'yes' === $data['has_pro']) {
				$send_data['data']    = $el_data;
				$send_data['success'] = 'ok';
			} else {
				$send_data['message'] = 'Not data found';
				$send_data['success'] = 'error';
			}

			return rest_ensure_response( $send_data );
		}


		//TODO: Query for layouts
		$args = [
			'post_type'      => [ BuilderFns::$post_type_tb ],
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => "DESC"
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
				$status              = wp_list_pluck( $templaate_status, 'slug' );
				$send_data['layouts'][] = [
					"id"            => $pid,
					//"content"       => get_post_meta( $pid, '_elementor_data', true ), //get_the_content(),
					"image_url"     => $img_url,
					"title"         => html_entity_decode( get_the_title() ),
					"post_class"    => join( ' ', get_post_class( null, $pid ) ),
					"preview_link"  => get_the_permalink( $pid ),
					"template_type" => $template_type,
					"editor_type"   => $editor_type,
					"status"        => ! empty( $status ) ? $status[0] : 'free'
				];
				$send_data['success']   = 'ok';
			}
		} else {
			$send_data['success'] = 'error';
			$send_data['message'] = __( "No posts found", "api-for-shopbuilder" );
		}

		wp_reset_postdata();

		return rest_ensure_response( $send_data );
	}
}
