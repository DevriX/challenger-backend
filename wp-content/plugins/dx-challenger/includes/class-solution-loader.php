<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}



class SolutionLoader extends WPGraphQL\Data\Loader\AbstractDataLoader {

	public function loadKeys( array $keys ) {

		if ( empty( $keys ) ) {
			return $keys;
		}

		error_log(print_r($keys, true));
		return array(
			'comment'   => 'asd',
			'id'        => 'asd',
			'link_code' => 'asd',
			'link_demo' => 'asd',
			'user_id'   => 0,	
		);
	}

}