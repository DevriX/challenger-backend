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

if ( ! defined( 'DXP_DIR' ) ) {
	define( 'DXP_DIR', dirname( __FILE__ ) . '/' );
}

/**
 * 
 */
class DX_Challenger {

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'include_classes' ), 10 );
		add_action( 'init', array( $this, 'init' ), 20 );
	}

	/**
	 * This function includes all the classes/traits on the plugins_loaded with priority
	 * 10 in order to be easily overwritten.
	 */
	public function include_classes() {
		require_once DXP_DIR . 'includes/class-challenge.php';
		require_once DXP_DIR . 'includes/class-solution.php';
	}

	/**
	 * This function initializes the classes/traits on initialization of WordPress.
	 */
	public function init() {
		new Challenge();
		new Solution();
	}

}

new DX_Challenger();

register_activation_hook( __FILE__, 'dx_challenger_on_install' );
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
			challenge_id mediumint(9) NOT NULL,
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
			challenge_id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_voted mediumint(9) NOT NULL,
			PRIMARY KEY  (challenge_id)
			) $charset_collate;";

		dbDelta( $voting_sql );
	}

	add_option( 'dx_challenger_version', $dx_challenger_version );
}
