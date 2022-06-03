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

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Challenge
 *
 * All of challenge functionalities goes here
 */
class Challenge {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->dx_challenger_post_types();
		add_action( 'add_meta_boxes', array( $this, 'dx_challenger_add_challenge_meta' ) );
		add_action( 'save_post_challenge', array( $this, 'dx_challenger_save_challenge_meta' ) );
		add_action( 'personal_options_update', array( $this, 'dx_challenger_save_user_meta' ) );
		add_action( 'edit_user_profile_update', array( $this, 'dx_challenger_save_user_meta' ) );
		add_action( 'show_user_profile', array( $this, 'dx_challenger_user_meta' ) );
		add_action( 'edit_user_profile', array( $this, 'dx_challenger_user_meta' ) );
	}

	/**
	 * Registers challenge CPT
	 *
	 * @action   Runs in contructor.
	 * @priority NULL
	 *
	 * @return void
	 */
	public function dx_challenger_post_types() {
		register_post_type(
			'challenge',
			array(
				'public'       => true,
				'icon'         => 'list-view',
				'has_archive'  => 'true',
				'supports'     => array( 'title', 'editor' ),
				'taxonomies'   => array( 'category', 'post_tag' ),
				'rewrite'      => array(
					'slug' => 'challenge',
				),
				'show_in_rest' => true,
				'labels'       => array(
					'name'          => 'Challenges',
					'add_new_item'  => 'Add New Challenge',
					'edit_item'     => 'Edit Challenge',
					'all_items'     => 'All Challenges',
					'singular_name' => 'Challenge',
				),
			)
		);
	}

	/**
	 * Challenge metaboxes
	 *
	 * @action   add_meta_boxes
	 * @priority 10
	 *
	 * @return void
	 */
	public function dx_challenger_add_challenge_meta() {
		add_meta_box(
			'dx-challenger-challenge-meta',
			'Challenge Details',
			array( $this, 'dx_challenger_challenge_meta_markup' ),
			'challenge'
		);
	}

	/**
	 * Challenge metaboxes markup
	 *
	 * @param  object $post WP_Post object.
	 * @return void
	 */
	public function dx_challenger_challenge_meta_markup( $post ) {
		wp_nonce_field( 'challenge_meta', 'challenge_nounce' );

		$deadline   = get_post_meta( $post->ID, 'dx_challenge_deadline', true );
		$experience = get_post_meta( $post->ID, 'dx_challenge_experience', true );
		$difficulty = get_post_meta( $post->ID, 'dx_challenge_difficulty', true );

		if ( ! empty( $experience ) ) {
			$experience = round( $experience );
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
	 *
	 * @action   save_post
	 * @priority 10
	 *
	 * @param  int $post_id Post ID.
	 * @return void
	 */
	public function dx_challenger_save_challenge_meta( $post_id ) {
		if ( ! empty( $_POST['challenge_nounce'] ) && ! wp_verify_nonce( $_POST['challenge_nounce'], 'challenge_meta' ) ) { // phpcs:ignore
			return;
		}

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

	/**
	 * Adds custom user meta fields
	 *
	 * @action   show_user_profile
	 * @action   edit_user_profile
	 * @priority 10
	 *
	 * @param  object $user WP_User object.
	 * @return void
	 */
	public function dx_challenger_user_meta( $user ) {
		wp_nonce_field( 'challenge_user_meta', 'challenge_user_nonce' );
		?>
		<h2>Challenger</h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th>Class</th>
					<td>
						<?php
						$selected_category = get_user_meta( $user->ID, 'category', true );
						wp_dropdown_categories(
							array(
								'show_option_none' => 'Select category',
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
						<textarea name="talents" id="talents"><?php echo esc_html( get_user_meta( $user->ID, 'talents', true ) ); ?></textarea>
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

	/**
	 * Saves custom user meta fields
	 *
	 * @action   personal_options_update
	 * @action   edit_user_profile_update
	 * @priority 10
	 *
	 * @param  int $user_id User ID.
	 * @return void|bool Void if user meta is saved, false if not.
	 */
	public function dx_challenger_save_user_meta( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		if ( false === check_admin_referer( 'challenge_user_meta', 'challenge_user_nonce' ) ) {
			return false;
		}

		if ( isset( $_POST['talents'] ) ) {
			update_user_meta( $user_id, 'talents', sanitize_text_field( wp_unslash( $_POST['talents'] ) ) );
		}
		if ( isset( $_POST['category'] ) ) {
			update_user_meta( $user_id, 'category', sanitize_text_field( wp_unslash( $_POST['category'] ) ) );
		}

	}
}
