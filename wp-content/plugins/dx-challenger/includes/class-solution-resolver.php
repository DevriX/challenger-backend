<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SolutionConnectionResolver
 *
 */
class SolutionConnectionResolver extends WPGraphQL\Data\Connection\AbstractConnectionResolver {

	function get_items() {
		return $this->query;
	}

	function get_query_args() {
		
	}

	function get_query() {
		return [
			[
				'comment'   => 'asd',
				'id'        => 'asd',
				'link_code' => 'asd',
				'link_demo' => 'asd',
				'user_id'   => 0,
			]
		];
	}

	function should_execute() {
		return true;
	}

	function get_loader_name() {
		return 'solution';
	}

	function get_ids() {

	}

	function is_valid_offset( $offset ) {

	}

}