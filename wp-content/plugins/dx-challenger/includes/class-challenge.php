<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Challenge
 *
 * All of challenge functionalities goes here
 *
 */
class Challenge {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->dx_challenger_post_types();
		add_action( 'add_meta_boxes', array( $this, 'dx_challenger_add_challenge_meta' ) );
		add_action( 'save_post', array( $this, 'dx_challenger_save_challenge_meta' ) );
		add_action( 'personal_options_update', array( $this, 'dx_challenger_save_user_meta' ) );
		add_action( 'edit_user_profile_update', array( $this, 'dx_challenger_save_user_meta' ) );
		add_action( 'show_user_profile', array( $this, 'dx_challenger_user_meta' ) );
		add_action( 'edit_user_profile', array( $this, 'dx_challenger_user_meta' ) );

		// GraphQL
		add_action( 'graphql_register_types', array( $this, 'dx_challenger_challenge_graphql' ) );


	}

	/**
	 * Registers challenge CPT
	 */
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

	/**
	 * Challenge metaboxes
	 */
	function dx_challenger_add_challenge_meta() {
		add_meta_box(
			'dx-challenger-challenge-meta',
			'Challenge Details',
			'dx_challenger_challenge_meta_markup',
			'challenge'
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
		<input type="text" name="dx_challenge_deadline" placeholder="yyyy/mm/dd" id="dx_challenge_deadline" value='<?php echo esc_textarea( $deadline ); ?>'>
		<label for="dx_challenge_experience">Experience:</label>
		<input type="number" name="dx_challenge_experience" id="dx_challenge_experience" value="<?php echo esc_textarea( $experience ); ?>" />
		<label for="dx_challenge_difficulty">Difficulty:</label>
		<input type="number" min="1" max="10" name="dx_challenge_difficulty" id="dx_challenge_difficulty" value="<?php echo esc_textarea( $difficulty ); ?>" />
		<?php
	}

	/**
	 * Save challenge meta
	 */
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

	/**
	 * Below are GraphQL functions
	 */

	/**
	 * Add Challenge Meta Fields To GraphQL
	 * Adds Connection to solutions
	 */
	function dx_challenger_challenge_graphql() {
		/* Add deadline */
		register_graphql_field(
			'Challenge',
			'deadline',
			array(
				'type'    => 'String',
				'resolve' => function( $post ) {
					global $post;
					$deadline = get_post_meta( $post->ID, 'dx_challenge_deadline', true );
					return empty( $deadline ) ? "" : $deadline;
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

		/**
		 * Connection for solutions
		 */
		register_graphql_connection(
			[
				'fromType'       => 'Challenge',
				'toType'         => 'Solution',
				'fromFieldName'  => 'solutions',
				'oneToOne'       => true,
				'resolve'        => function ( $source, $args, $context, $info ) {
					$resolver = new SolutionConnectionResolver( $source, $args, $context, $info );
					return $resolver->get_connection();
				}
			]
		);
	}
}
