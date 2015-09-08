<?php
/**
 * Actions
 *
 * @package     EDD\Vault\Actions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add an item to the vault
 *
 * @since       1.0.0
 * @return      void
 */
function edd_vault_add() {
	if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd-vault-nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd-vault' ), __( 'Error', 'edd-vault' ), array( 'response' => 403 ) );
	}

	$args = wp_parse_args( $_GET );

	// Bail if an invalid ID was passed
	if( ! isset( $args['item_id'] ) || ! is_numeric( $args['item_id'] ) ) {
		return;
	}

	// Add the item(s) to the vault
	if( $args['item_type'] == 'download' ) {
		edd_vault_set_status( $args['item_id'] );
	} else {
		// Mark the tag as in the vault
		edd_vault_set_term_status( $args['item_id'], $args['item_type'] );
	}
	
	wp_safe_redirect( add_query_arg( array( 'edd-action' => false, 'item_id' => false, 'item_type' => false, '_wpnonce' => false, 'post_type' => 'download', 'edd_vault_message' => $args['item_type'] . '-added' ) ) );
	exit;
}
add_action( 'edd_vault_add', 'edd_vault_add' );


/**
 * Remove an item from the vault
 *
 * @since       1.0.0
 * @return      void
 */
function edd_vault_remove() {
	if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd-vault-nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'edd-vault' ), __( 'Error', 'edd-vault' ), array( 'response' => 403 ) );
	}

	$args = wp_parse_args( $_GET );
	
	// Bail if an invalid ID was passed
	if( ! isset( $args['item_id'] ) || ! is_numeric( $args['item_id'] ) ) {
		return;
	}

	// Remove the item(s) from the vault
	if( $args['item_type'] == 'download' ) {
		edd_vault_set_status( $args['item_id'], 'remove' );
	} else {
		// Mark the tag as not in the vault
		edd_vault_set_term_status( $args['item_id'], $args['item_type'], 'remove' );
	}

	wp_safe_redirect( add_query_arg( array( 'edd-action' => false, 'item_id' => false, 'item_type' => false, '_wpnonce' => false, 'post_type' => 'download', 'edd_vault_message' => $args['item_type'] . '-removed' ) ) );
	exit;
}
add_action( 'edd_vault_remove', 'edd_vault_remove' );


/**
 * Handle template redirects
 *
 * @since       1.0.0
 * @return      void
 */
function edd_vault_download_redirect() {
	if( is_single() && ! current_user_can( 'edit_products' ) ) { 
		$id = get_the_ID();

		if( get_post_type( $id ) == 'download' ) {
			$status = array_values( edd_vault_is_stored( $id ) );

			if( in_array( true, $status ) ) {
				$redirect = edd_get_option( 'vault_redirect', false );
				
				if( $redirect ) {
					$redirect = get_permalink( $redirect );
				} else {
					$redirect = home_url();
				}
				
				wp_safe_redirect( $redirect );
				exit;
			}
		}
	}
}
add_action( 'template_redirect', 'edd_vault_download_redirect' );


/**
 * Override edd_pre_add_to_cart
 *
 * @since       1.0.0
 * @param       int $download_id The ID of a given download
 * @param       array $options The options for this download
 * @return      void
 */
function edd_vault_override_add_to_cart( $download_id, $options ) {
	$status = array_values( edd_vault_is_stored( $download_id ) );

	if( in_array( true, $status ) ) {
		$redirect = edd_get_option( 'vault_redirect', false );
				
		if( $redirect ) {
			$redirect = get_permalink( $redirect );
		} else {
			$redirect = home_url();
		}
				
		wp_safe_redirect( $redirect );
		exit;
	}
}
add_action( 'edd_pre_add_to_cart', 'edd_vault_override_add_to_cart', 200, 2 );



/**
 * Add status to stats meta box
 *
 * @since       1.0.0
 * @global      object $post The WordPress post object
 * @return      void
 */
function edd_vault_stats_meta_box() {
	global $post;
	
	$status = edd_vault_is_stored( $post->ID );

	if( $status['download'] ) {
		$download_status    = ' edd-vault-status-stored';
		$download_title     = sprintf( __( '%s Is In Vault', 'edd-vault' ), edd_get_label_singular() );
		$button_title       = __( 'Remove From Vault', 'edd-vault' );
		$button_args        = array(
			'edd-action'        => 'vault_remove',
			'item_id'           => $post->ID,
			'item_type'         => 'download'
		);
	} else {
		$download_status    = '';
		$download_title     = sprintf( __( '%s Is Not In Vault', 'edd-vault' ), edd_get_label_singular() );
		$button_title       = __( 'Add To Vault', 'edd-vault' );
		$button_args        = array(
			'edd-action'        => 'vault_add',
			'item_id'           => $post->ID,
			'item_type'         => 'download'
		);
	}

	if( $status['download_category'] ) {
		$download_category_status   = ' edd-vault-status-stored';
		$download_category_title    = sprintf( __( '%s Category Is In Vault', 'edd-vault' ), edd_get_label_singular() );
	} else {
		$download_category_status   = '';
		$download_category_title    = sprintf( __( '%s Category Is Not In Vault', 'edd-vault' ), edd_get_label_singular() );
	}

	if( $status['download_tag'] ) {
		$download_tag_status   = ' edd-vault-status-stored';
		$download_tag_title    = sprintf( __( '%s Tag Is In Vault', 'edd-vault' ), edd_get_label_singular() );
	} else {
		$download_tag_status   = '';
		$download_tag_title    = sprintf( __( '%s Tag Is Not In Vault', 'edd-vault' ), edd_get_label_singular() );
	}	
	?>
	<hr />
	
	<p class="product-vault-status">
		<span class="label"><?php _e( 'Vault Status:', 'edd-vault' ); ?></span>
		<span class="dashicons dashicons-download<?php echo $download_status; ?>" title="<?php echo $download_title; ?>"></span>
		<span class="dashicons dashicons-category<?php echo $download_category_status; ?>" title="<?php echo $download_category_title; ?>"></span>
		<span class="dashicons dashicons-tag<?php echo $download_tag_status; ?>" title="<?php echo $download_tag_title; ?>"></span>
	</p>

	<div class="product-vault-toggle">
		<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( $button_args ), 'edd-vault-nonce' ) ); ?>"><?php echo $button_title; ?></a>
	</div>
	<?php
}
add_action( 'edd_stats_meta_box', 'edd_vault_stats_meta_box' );


/**
 * Display admin notices
 *
 * @since       1.0.0
 * @return      void
 */
function edd_vault_admin_notices() {
	if( isset( $_GET['edd_vault_message'] ) ) {
		switch( $_GET['edd_vault_message'] ) {
			case 'download-added':
				$message = sprintf( __( '%s added to the vault.', 'edd-vault' ), edd_get_label_singular() );
				break;
			case 'download-removed':
				$message = sprintf( __( '%s removed from the vault.', 'edd-vault' ), edd_get_label_singular() );
				break;
			case 'download_tag-added':
				$message = sprintf( __( '%s tag added to the vault.', 'edd-vault' ), edd_get_label_singular() );
				break;
			case 'download_tag-removed':
				$message = sprintf( __( '%s tag removed from the vault.', 'edd-vault' ), edd_get_label_singular() );
				break;
			case 'download_category-added':
				$message = sprintf( __( '%s category added to the vault.', 'edd-vault' ), edd_get_label_singular() );
				break;
			case 'download_category-removed':
				$message = sprintf( __( '%s category removed from the vault.', 'edd-vault' ), edd_get_label_singular() );
				break;
		}

		echo '<div class="updated"><p>' . $message . '</p></div>';
	}
}
add_action( 'admin_notices', 'edd_vault_admin_notices' );
