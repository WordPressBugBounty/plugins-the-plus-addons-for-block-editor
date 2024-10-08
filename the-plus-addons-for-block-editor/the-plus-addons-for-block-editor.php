<?php
/*
* Plugin Name: Nexter Blocks
* Plugin URI: https://nexterwp.com/nexter-blocks/
* Description: Highly customizable WordPress Gutenberg blocks to build professional websites with top-notch performance and sleek design. Includes 40+ FREE WordPress Blocks.
* Version: 4.0.1
* Author: POSIMYTH
* Author URI: https://posimyth.com
* Tested up to: 6.6
* Text Domain: tpgb
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

defined( 'TPGB_VERSION' ) or define( 'TPGB_VERSION', '4.0.1' );
define( 'TPGB_FILE__', __FILE__ );

define( 'TPGB_PATH', plugin_dir_path( __FILE__ ) );
define( 'TPGB_BASENAME', plugin_basename(__FILE__) );
define( 'TPGB_BDNAME', basename( dirname(__FILE__)) );
define( 'TPGB_URL', plugins_url( '/', __FILE__ ) );
define( 'TPGB_ASSETS_URL', TPGB_URL );
define( 'TPGB_INCLUDES_URL', TPGB_PATH.'includes/' );
define( 'TPGB_CATEGORY', 'tpgb' );

if ( ! version_compare( PHP_VERSION, '5.6.40', '>=' ) ) {
	add_action( 'admin_notices', 'tpgb_check_php_version' );
} elseif ( ! version_compare( get_bloginfo( 'version' ), '4.7.1', '>=' ) ) {
	add_action( 'admin_notices', 'tpgb_check_wp_version' );
} else {
	require_once 'plus-block-loader.php';
}

/**
 * Nexter Blocks check minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 1.0.0
 *
 * @return void
 */
function tpgb_check_php_version() {	
	/* translators: Nexter Blocks requires PHP version %s+. The plugin is currently not running. Please update to the latest PHP version. */
	$check_message      = sprintf( esc_html__( 'Nexter Blocks requires PHP version %s+. The plugin is currently not running. Please update to the latest PHP version.', 'tpgb' ), '5.6.40' );
	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );
	echo wp_kses_post( $display_message );
}

/**
 * Nexter Blocks check minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 1.0.0
 *
 * @return void
 */
function tpgb_check_wp_version() {	
	/* translators: Nexter Blocks requires at least WordPress version %s+. Because you’re using an older version, the plugin is currently not running. Please update WordPress to the latest version. */
	$check_message      = sprintf( esc_html__( 'Nexter Blocks requires at least WordPress version %s+. Because you’re using an older version, the plugin is currently not running. Please update WordPress to the latest version.', 'tpgb' ), '4.7.1' );
	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );
	echo wp_kses_post( $display_message );
}

/* 
 * Nexter Blocks Plugin Update Message
 * @since 1.1.3
 */
add_action('in_plugin_update_message-the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php','tpgb_plugin_update_message',10,2);
function tpgb_plugin_update_message( $data, $response ){			
	if( isset( $data['upgrade_notice'] ) && !empty($data['upgrade_notice']) ) {
		printf(
			'<div class="update-message">%s</div>',
			wpautop( $data['upgrade_notice'] )
		);
	}
}
?>