<?php
/**
 * Download actions
 *
 * @package     EDD\Vault\Admin\Downloads\Actions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add actions
 *
 * @since       1.0.0
 * @param       array $actions The current actions
 * @param       object $item The current item
 * @return      array $actions The updated actions
 */
function edd_vault_dashboard_actions( $actions, $item ) {
	$type = false;

	// If this is a post, but not a download, bail
	if( property_exists( $item, 'post_type' ) ) {
		if( $item->post_type != 'download' ) {
			return $actions;
		} else {
			$type   = 'download';
			$id     = $item->ID;
			$query  = 'edit.php';
		}
	}

	if( ! $type ) {
		$type   = $item->taxonomy;
		$id     = $item->term_id;
		$query  = 'edit-tags.php';
	}

	// Build the URL
	$stored = edd_vault_is_stored( $id, $type );
	if( $stored[$type] == true ) {
		$class  = 'vault-remove';
		$label  = __( 'Remove From Vault', 'edd-vault' );
		$args   = array(
			'edd-action'    => 'vault_remove',
			'item_id'       => $id,
			'item_type'     => $type
		);
	} else {
		$class  = 'vault-add';
		$label  = __( 'Add To Vault', 'edd-vault' );
		$args   = array(
			'edd-action'    => 'vault_add',
			'item_id'       => $id,
			'item_type'     => $type
		);
	}

	$url = esc_url( wp_nonce_url( add_query_arg( $args ), 'edd-vault-nonce' ) );

	$actions[$class] = '<a href="' . $url . '">' . $label . '</a>';

	return $actions;
}
add_filter( 'post_row_actions', 'edd_vault_dashboard_actions', 10, 2 );
add_filter( 'download_tag_row_actions', 'edd_vault_dashboard_actions', 10, 2 );
add_filter( 'download_category_row_actions', 'edd_vault_dashboard_actions', 10, 2 );
