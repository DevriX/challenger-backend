<?php
/**
 * Plugin Name:     Dx Challenger
 * Plugin URI:      https://devrix.com
 * Description:     Challenger plugin
 * Author:          DevriX
 * Author URI:      https://devrix.com
 * Text Domain:     dx-challenger
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Dx_Challenger
 */

$text_domain = 'dx-challenger';

function dx_challenger_post_types() {
	register_post_type(
		'challenge',
		array(
			'public'              => true,
			'icon'                => 'list-view',
			'has_archive'         => 'true',
			'supports'            => array( 'title', 'editor' ),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'rewrite'             => array(
				'slug' => 'challenge',
			),
			'show_in_rest'        => true,
			'labels'              => array(
				'name'          => 'Challenges',
				'add_new_item'  => 'Add New Challenge',
				'edit_item'     => 'Edit Challenge',
				'all_items'     => 'All Challenges',
				'singular_name' => 'Challenge',
			),
			'show_in_graphql'     => true,
			'graphql_single_name' => 'challenge',
			'graphql_plural_name' => 'challenges',
		)
	);
}



function dx_challenger_challenge_meta_markup( $post ) {
	wp_nonce_field( 'challenge_meta', 'challenge_nounce' );

	$deadline   = get_post_meta( $post->ID, 'dx_challenge_deadline', true );
	$experience = round( get_post_meta( $post->ID, 'dx_challenge_experience', true ) );
	$difficulty = get_post_meta( $post->ID, 'dx_challenge_difficulty', true );

	if ( empty( $experience ) ) {
		$experience = '';
	}

	?>

	<label for="dx_challenge_deadline">Deadline:</label>
	<input type="number" value="7" name="dx_challenge_deadline" id="dx_challenge_deadline" value='<?php echo esc_textarea( $deadline ); ?>'>
	<label for="dx_challenge_experience">Experience:</label>
  <input type="number" name="dx_challenge_experience" id="dx_challenge_experience" value="<?php echo esc_textarea( $experience ); ?>" />
  <label for="dx_challenge_difficulty">Difficulty:</label>
  <input type="number" min="1" max="10" name="dx_challenge_difficulty" id="dx_challenge_difficulty" value="<?php echo esc_textarea( $difficulty ); ?>" />
  
	<?php
}

function dx_challenger_add_challenge_meta() {
	add_meta_box(
		'dx-challenger-challenge-meta',
		'Challenge Details',
		'dx_challenger_challenge_meta_markup',
		'challenge'
	);
}

function dx_challenger_save_challenge_meta( $post_id ) {
	if ( wp_verify_nonce( $_POST['challenge_nounce'], 'challenge_meta' ) ) {

		if ( array_key_exists( 'dx_challenge_deadline', $_POST ) ) {
			update_post_meta(
				$post_id,
				'dx_challenge_deadline',
				sanitize_text_field( wp_unslash( $_POST['dx_challenge_deadline'] ) )
			);
		}

		if ( array_key_exists( 'dx_challenge_experience', $_POST ) ) {
			update_post_meta(
				$post_id,
				'dx_challenge_experience',
				sanitize_text_field( wp_unslash( $_POST['dx_challenge_experience'] ) )
			);
		}

		if ( array_key_exists( 'dx_challenge_difficulty', $_POST ) ) {
			update_post_meta(
				$post_id,
				'dx_challenge_difficulty',
				sanitize_text_field( wp_unslash( $_POST['dx_challenge_difficulty'] ) )
			);
		}
	}
}


/* Add Challenge Meta Fields To GraphQL */
function dx_challenger_challenge_meta_fields() {
	/* Add deadline */
	register_graphql_field(
		'Challenge',
		'deadline',
		array(
			'type'    => 'Number',
			'resolve' => function( $post ) {
				global $post;
				$deadline = get_post_meta( $post->ID, 'dx_challenge_deadline', true );
				return empty( $deadline ) ? 0 : $deadline;
			},
		)
	);

	/* Add experience */
	register_graphql_field(
		'Challenge',
		'experience',
		array(
			'type'    => 'Number',
			'resolve' => function( $post ) {
				global $post;
				$experience   = round( get_post_meta( $post->ID, 'dx_challenge_experience', true ) );
				return empty( $experience ) ? 0 : $experience;
			},
		)
	);

	/* Add difficulty */
	register_graphql_field(
		'Challenge',
		'difficulty',
		array(
			'type'    => 'Number',
			'resolve' => function( $post ) {
				global $post;
				$difficulty   = get_post_meta( $post->ID, 'dx_challenge_difficulty', true );
				return empty( $difficulty ) ? 0 : $difficulty;
			},
		)
	);
}

/* Add solutions to GraphQL */
add_action( 'graphql_register_types', function() {

	register_graphql_object_type(
		'Solution',
		[
			'description' => __( 'Solution', $text_domain ),
			'fields'      => [
				'id' => [
					'type'        => 'Number',
					'description' => 'Solution id'
				],
				'challenge_id'  => [
					'type'        => 'String',
					'description' => 'Challenge id'
				],
				'user_id'       => [
					'type'        => 'String',
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
			'description' => __( 'Return solutions', $text_domain ),
			'args' => [
				'id' => [
					'type' => 'Number',
				],
				'challenge_id' => [
					'type' => 'String'
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

				if( isset( $args['challenge_id'] )) {
					$challenge_id = $args['challenge_id'];
					$sql = "SELECT * FROM $table_name WHERE challenge_id = $challenge_id";
				}

				$solutions = $wpdb->get_results($sql);

				return $solutions;
			}
		]
	);
});



/* Add Solution mutation to GraphQL */
function dx_solution_mutation() {
	register_graphql_mutation(
		'submitSolution',
		array(
			'inputFields' => array(
				'challengeId' => array(
					'type' => 'String',
					'description' => __( 'Challenge id', $text_domain )
				),
				'userId' => array(
					'type' => 'String',
					'description' => __( 'User id', $text_domain )
				),
				'linkDemo' => array(
					'type' => 'String',
					'description' => __( "Link to solution's demo", $text_domain )
				),
				'linkCode' => array(
					'type' => 'String',
					'description' => __( 'Repository link', $text_domain )
				),
				'comment' => array(
					'type' => 'String',
					'description' => __( 'Comment', $text_domain )
				),
			),

			'outputFields' => array(
				'challengeId' => array(
					'type' => 'String',
					'description' => __( 'Challenge id', $text_domain),
					'resolve' => function($payload) {
						return $payload['challengeId'];
					}
				),
				'userId' => array(
					'type' => 'String',
					'description' => __( 'User id', $text_domain),
					'resolve' => function($payload) {
						return $payload['userId'];
					}
				),
				'linkDemo' => array(
					'type' => 'String',
					'description' => __( "Link to solution's demo", $text_domain ),
					'resolve' => function($payload) {
						return $payload['linkDemo'];
					}
				),
				'linkCode' => array(
					'type' => 'String',
					'description' => __( 'Repository link', $text_domain ),
					'resolve' => function($payload) {
						return $payload['linkCode'];
					}
				),
				'comment' => array(
					'type' => 'String',
					'description' => __( 'Comment', $text_domain ),
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

add_action( 'graphql_register_types', 'dx_solution_mutation' );

/* Add votes query to GraphQL */
add_action( 'graphql_register_types', function() {

	register_graphql_object_type(
		'Vote',
		[
			'description' => __( 'Vote', $text_domain ),
			'fields'      => [
				'count'  => [
					'type'        => 'Number',
					'description' => 'Votes count'
				],
			],
		]
	);

	register_graphql_field(
		'RootQuery',
		'votes',
		[
			'description' => __( 'Return solution votes', $text_domain ),
			'args' => [
				'solutionId' => [
					'type' => 'Number',
				],
			],
			'type'        => 'Vote',
			'resolve'     => function($source, $args) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'challenger_voting';
				$solution_id = $args['solutionId'];

				$sql = "SELECT COUNT(solution_id) as 'count' FROM $table_name WHERE solution_id = $solution_id";

				$votes = $wpdb->get_results($sql);

				return array('count' => $votes[0]->count);
			}
		]
	);
});

/* Add Vote mutation to GraphQL */
function dx_voting_mutation() {
	register_graphql_mutation(
		'submitVote',
		array(
			'inputFields' => array(
				'challengeId' => array(
					'type' => 'String',
					'description' => __( 'Challenge id', $text_domain )
				),
				'userVoted' => array(
					'type' => 'String',
					'description' => __( 'User id', $text_domain )
				),
				'solutionId' => array(
					'type' => 'Number',
					'description' => __( 'Solution id', $text_domain )
				),
			),

			'outputFields' => array(
				'challengeId' => array(
					'type' => 'String',
					'description' => __( 'Challenge id', $text_domain),
					'resolve' => function($payload) {
						return $payload['challengeId'];
					}
				),
				'userVoted' => array(
					'type' => 'Number',
					'description' => __( 'User id', $text_domain),
					'resolve' => function($payload) {
						return $payload['userVoted'];
					}
				),
				'solutionId' => array(
					'type' => 'Number',
					'description' => __( 'Solution id', $text_domain)
				),
				'isDuplicateVote' => array(
					'type' => 'Boolean',
					'description' => __( 'Is duplicate vote', $text_domain)
				),
				'voteAdded' => array(
					'type' => 'Boolean',
					'description' => __( 'Is vote successfully added', $text_domain )
				)
			),
				
			'mutateAndGetPayload' => function( $input, $context, $info ) {
				global $wpdb;     
  			$table_name = $wpdb->prefix . 'challenger_voting';

				$user_voted = $input['userVoted'];
				$solution_id = $input['solutionId'];
				$challengeId = $input['challengeId'];

				$sql = "SELECT * from $table_name WHERE solution_id = $solution_id AND user_voted = $user_voted";
				$voted = $wpdb->get_results($sql);

				$vote_added = false;
				if(empty($voted)) {
					$data = array(
						'challenge_id' => $challengeId,
						'user_voted' => $user_voted,
						'solution_id' => $solution_id
					);
	
					$query_status = $wpdb->insert($table_name, $data);
					$vote_added = $query_status != false;
				}

				$result = array(
					'challengeId' => $challengeId,
					'userVoted' => $user_voted,
					'solutionId' => $solution_id,
					'isDuplicateVote' => $voted,
					'voteAdded' => $vote_added
				);

				return $result;
			}
			
		),
  );
}

add_action( 'graphql_register_types', 'dx_voting_mutation' );

/* User Meta */
function dx_challenger_user_meta( $user ) {
	global $user_ID;
	?>

  <h2>Challenger</h2>
	
  <table class="form-table">
	<tbody>
	<tr>
	  <th>Class</th>
	  <td>
		<?php
		$selected_category = get_user_meta( $user_ID, 'category', true );
		wp_dropdown_categories(
			array(
				'show_option_none' => 'Select category',
				'hide_if_empty'    => false,
				'hide_empty'       => 0,
				'selected'         => $selected_category,
				'name'             => 'category',
				'id'               => 'category',
			)
		);
		?>

	</select>
	  </td>
	</tr>
	  <tr>
		<th>Experience</th>
		<td><span>7</span></td>
	  </tr>
	  <tr class="user-description-wrap">
		<th><label for="talents">Talents</label></th>
		<td>
		<textarea name="talents" id="talents"><?php echo get_user_meta( $user_ID, 'talents', true ); ?></textarea>
	  </td>
	  </tr>
	  <tr>
		<th>Rank</th>
		<td><span>5</span></td>
	  </tr>
	</tbody>
  </table>

	<?php
}

function dx_challenger_save_user_meta( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	update_user_meta( $user_id, 'talents', sanitize_text_field( $_POST['talents'] ) );
	update_user_meta( $user_id, 'category', sanitize_text_field( $_POST['category'] ) );

}

global $dx_challenger_version;
$dx_challenger_version;

function dx_challenger_on_install() {
	global $wpdb;
	global $dx_challenger_version;

	$solutions_table = $wpdb->prefix . 'challenger_solutions';
	$voting_table    = $wpdb->prefix . 'challenger_voting';

	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	if ( $wpdb->get_var( "show tables like '$solutions_table'" ) != $solutions_table ) {
		$sql = "CREATE TABLE $solutions_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			challenge_id text NOT NULL,
			user_id mediumint(9) NOT NULL,
			link_demo text,
			link_code text NOT NULL,
			comment text,
			PRIMARY KEY  (id)
			) $charset_collate;";

			dbDelta( $sql );
	}

	if ( $wpdb->get_var( "show tables like '$voting_table'" ) != $voting_table ) {
		$voting_sql = "CREATE TABLE $voting_table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			challenge_id text NOT NULL,
			user_voted mediumint(9) NOT NULL,
			solution_id text NOT NULL,
			PRIMARY KEY  (id)
			) $charset_collate;";

		dbDelta( $voting_sql );
	}

	add_option( 'dx_challenger_version', $dx_challenger_version );
}

register_activation_hook( __FILE__, 'dx_challenger_on_install' );

add_action( 'personal_options_update', 'dx_challenger_save_user_meta' );
add_action( 'edit_user_profile_update', 'dx_challenger_save_user_meta' );


add_action( 'show_user_profile', 'dx_challenger_user_meta' );
add_action( 'edit_user_profile', 'dx_challenger_user_meta' );


add_action( 'init', 'dx_challenger_post_types' );
add_action( 'add_meta_boxes', 'dx_challenger_add_challenge_meta' );
add_action( 'save_post', 'dx_challenger_save_challenge_meta' );

add_action( 'graphql_register_types', 'dx_challenger_challenge_meta_fields' );
