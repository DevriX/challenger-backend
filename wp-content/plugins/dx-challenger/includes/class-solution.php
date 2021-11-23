<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Solution
 *
 * All of solution functionalities goes here
 *
 */
class Solution {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'graphql_register_types', array( $this, 'dx_solution_mutation' ) );
		add_action( 'graphql_register_types', array( $this, 'dx_challenger_solution_graphql' ) );
	}

	/* Add solutions to GraphQL */
	function dx_challenger_solution_graphql() {
		register_graphql_object_type(
			'Solution',
			[
				'description' => __( 'Solution', 'dx-challenger' ),
				'fields'      => [
					'id' => [
						'type'        => 'Number',
						'description' => 'Solution id'
					],
					'challenge_id'  => [
						'type'        => 'Number',
						'description' => 'Challenge id'
					],
					'user_id'       => [
						'type'        => 'Number',
						'description' => 'User id'
					],
					'link_demo' => [
						'type' => 'String',
						'description' => 'Solution demo'
					],
					'link_code' => [
						'type' => 'String',
						'description' => 'Solution code'
					],
					'comment' => [
						'type' => 'String',
						'description' => 'Comment'
					]
				],
			]
		);

		register_graphql_field(
			'RootQuery',
			'solutions',
			[
				'description' => __( 'Return solutions', 'dx-challenger' ),
				'args' => [
					'id' => [
						'type' => 'Number',
					],
				],
				'type'        => [ 'list_of' => 'solution' ],
				'resolve'     => function($source, $args) {
					global $wpdb;
					$table_name = $wpdb->prefix . 'challenger_solutions';
					$sql = "SELECT * FROM $table_name";
					if ( isset( $args['id'] )) {
						$id = $args['id'];
						$sql = "SELECT * FROM $table_name WHERE id = $id";
					}
					$solutions = $wpdb->get_results($sql);

					return $solutions;
				}
			]
		);
		/**
		 * Connection from Solution to Users
		 */
		register_graphql_connection(
			[
				'fromType'       => 'Solution',
				'toType'         => 'User',
				'fromFieldName'  => 'author',
				'oneToOne'       => true,
				'resolve'        => function ( $source, $args, $context, $info ) {
					$resolver = new WPGraphQL\Data\Connection\UserConnectionResolver( $source, $args, $context, $info );
					$resolver->set_query_arg( 'p', [ $source ] );
					return $resolver->one_to_one()->get_connection();
				}
			]
		);
	}
	
	/* Add Solution mutation to GraphQL */
	function dx_solution_mutation() {
		register_graphql_mutation(
			'submitSolution',
			array(
				'inputFields' => array(
					'challengeId' => array(
						'type' => 'Number',
						'description' => __( 'Challenge id', 'dx-challenger' )
					),
					'userId' => array(
						'type' => 'Number',
						'description' => __( 'User id', 'dx-challenger' )
					),
					'linkDemo' => array(
						'type' => 'String',
						'description' => __( "Link to solution's demo", 'dx-challenger' )
					),
					'linkCode' => array(
						'type' => 'String',
						'description' => __( 'Repository link', 'dx-challenger' )
					),
					'comment' => array(
						'type' => 'String',
						'description' => __( 'Comment', 'dx-challenger' )
					),
				),

				'outputFields' => array(
					'challengeId' => array(
						'type' => 'Number',
						'description' => __( 'Challenge id', 'dx-challenger'),
						'resolve' => function($payload) {
							return $payload['challengeId'];
						}
					),
					'userId' => array(
						'type' => 'Number',
						'description' => __( 'User id', 'dx-challenger'),
						'resolve' => function($payload) {
							return $payload['userId'];
						}
					),
					'linkDemo' => array(
						'type' => 'String',
						'description' => __( "Link to solution's demo", 'dx-challenger' ),
						'resolve' => function($payload) {
							return $payload['linkDemo'];
						}
					),
					'linkCode' => array(
						'type' => 'String',
						'description' => __( 'Repository link', 'dx-challenger' ),
						'resolve' => function($payload) {
							return $payload['linkCode'];
						}
					),
					'comment' => array(
						'type' => 'String',
						'description' => __( 'Comment', 'dx-challenger' ),
						'resolve' => function($payload) {
							return $payload['comment'];
						}
					)
				),
					
				'mutateAndGetPayload' => function( $input, $context, $info ) {
					global $wpdb;     
					$table_name = $wpdb->prefix . 'challenger_solutions';

					$data = array(
						'challenge_id' => $input['challengeId'],
						'user_id' => $input['userId'],
						'link_demo' => $input['linkDemo'],
						'link_code' => $input['linkCode'],
						'comment' => $input['comment']
					);

					$wpdb->insert($table_name, $data);

					return $input;
				}
				
			),
		);
	}

}
