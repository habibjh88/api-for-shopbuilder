<?php
/**
 * Action Hooks class.
 *
 * @package RT_SB_API
 */
namespace RT\ThePostGridAPI\Controllers\Api;

class RestApi {
	/**
	 * Register rest route
	 */
	public function __construct() {
		new GetElLayoutsV1();
//		new GetLayoutsV1();
//		new CountLayouts();
	}
}