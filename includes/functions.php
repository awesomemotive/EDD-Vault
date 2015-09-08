<?php
/**
 * Functions
 *
 * @package     EDD\Vault\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Check if an item is in the vault
 *
 * @since       1.0.0
 * @param       int $item_id The ID of the download or tag to check
 * @param       string $type The type of item we are checking
 * @return      mixed
 */
function edd_vault_is_stored( $item_id = false, $type = 'download' ) {
	$return = false;

	if( $type == 'download' ) {
		$status = get_post_meta( $item_id, '_edd_vault', true );

		$return = array(
			'download'          => false,
			'download_tag'      => false,
			'download_category' => false
		);

		// Download status
		if( $status && $status == 'true' ) {
			$return['download'] = true;
		}

		$terms = get_option( 'edd_vault_term_status' );

		// Category status
		$cats = wp_get_post_terms( $item_id, 'download_category' );

		if( ! empty( $cats ) ) {
			foreach( $cats as $cat ) {
				if( array_key_exists( $cat->term_id, $terms['download_category'] ) ) {
					$return['download_category'] = true;
				}
			}
		}

		// Tag status
		$tags = wp_get_post_terms( $item_id, 'download_tag' );

		if( ! empty( $tags ) ) {
			foreach( $tags as $tag ) {
				if( array_key_exists( $tag->term_id, $terms['download_tag'] ) ) {
					$return['download_tag'] = true;
				}
			}
		}
	} else {
		$terms = get_option( 'edd_vault_term_status' );

		if( is_array( $terms ) && array_key_exists( $item_id, $terms[$type] ) ) {
			$return = true;
		}
	}
	
	return $return;
}


/**
 * Set item vault status
 *
 * @since       1.0.0
 * @param       int $post_id The post ID to set status for
 * @param       string $action Whether to add or remove the item
 * @return      mixed
 */
function edd_vault_set_status( $post_id, $action = 'add' ) {
	// Verify that the item IS a download
	if( get_post_type( $post_id ) != 'download' ) {
		return false;
	}

	$status = 'true';

	if( $action == 'remove' ) {
		$status = 'false';
	}

	$status = update_post_meta( $post_id, '_edd_vault', $status );

	return $status;
}


/**
 * Set tag vault status
 *
 * @since       1.0.0
 * @param       int $tag_id The tag ID to set status for
 * @param       string $tax The taxonomy of this tag
 * @param       string $action Whether to add or remove the item
 * @return      mixed
 */
function edd_vault_set_term_status( $tag_id, $tax, $action = 'add' ) {
	$terms = get_option( 'edd_vault_term_status' );

	// Setup array if it doesn't exist
	if( ! $terms ) {
		$terms = array(
			'download_category' => array(),
			'download_tag'      => array()
		);
	}

	if( $action == 'add' ) {
		if( ! array_key_exists( $tag_id, $terms[$tax] ) ) {
			$terms[$tax][$tag_id] = (int) $tag_id;
		}
	} else {
		if( array_key_exists( $tag_id, $terms[$tax] ) ) {
			unset( $terms[$tax][$tag_id] );
		}
	}

	update_option( 'edd_vault_term_status', $terms );
}
