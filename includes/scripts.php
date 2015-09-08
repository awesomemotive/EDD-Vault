<?php
/**
 * Scripts
 *
 * @package     EDD\Wallet\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function edd_vault_admin_scripts() {
	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'edd-vault', EDD_VAULT_URL . 'assets/css/admin' . $suffix . '.css', EDD_VAULT_VER );
}
add_action( 'admin_enqueue_scripts', 'edd_vault_admin_scripts', 100 );


/**
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function edd_vault_scripts() {
	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'edd-vault', EDD_VAULT_URL . 'assets/css/edd-vault' . $suffix . '.css', EDD_VAULT_VER );
}
add_action( 'wp_enqueue_scripts', 'edd_vault_scripts' );
