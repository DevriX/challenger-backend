<?php

function dx_challenger_challenge_meta_markup( $post ) {
  wp_nonce_field( 'challenge_meta', 'challenge_nounce' );

  $deadline   = get_post_meta( $post->ID, 'dx_challenge_deadline', true );
  $experience   = round( get_post_meta( $post->ID, 'dx_challenge_experience', true ) );
  $difficulty   = get_post_meta( $post->ID, 'dx_challenge_difficulty', true );

  if(empty($deadline)) {
    $deadline = 7;
  }

  if(empty($experience)) {
    $experience = '';
  }

  ?>

  <label for="dx_challenge_deadline">Deadline:</label>
  <input type="text" name="dx_challenge_deadline" id="dx_challenge_deadline" value="<?php echo esc_textarea( $deadline ) ?>" />
  <label for="dx_challenge_experience">Experience:</label>
  <input type="text" name="dx_challenge_experience" id="dx_challenge_experience" value="<?php echo esc_textarea( $experience ) ?>" />
  <label for="dx_challenge_difficulty">Difficulty:</label>
  <input type="text" name="dx_challenge_difficulty" id="dx_challenge_difficulty" value="<?php echo esc_textarea($difficulty) ?>" />

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

    if( array_key_exist( 'dx_challenge_deadline', $_POST ) ) {
      update_post_meta(
        $post_id,
        'dx_challenge_deadline',
        sanitize_text_field( wp_unslash( $_POST['dx_challenge_deadline'] ) )
      );
    }

    if( array_key_exist( 'dx_challenge_experience', $_POST ) ) {
      update_post_meta(
        $post_id,
        'dx_challenge_experience',
        sanitize_text_field( wp_unslash( $_POST['dx_challenge_experience'] ) )
      );
    }

    if( array_key_exist( 'dx_challenge_difficulty', $_POST ) ) {
      update_post_meta(
        $post_id,
        'dx_challenge_difficulty',
        sanitize_text_field( wp_unslash( $POST['dx_challenge_difficulty'] ) )
      );
    }
  }
}

add_action( 'init', 'dx_challenger_post_types' );
add_action( 'add_meta_boxes', 'dx_challenger_add_challenge_meta' );
add_action( 'save_post', 'dx_challenger_save_challenge_meta' );