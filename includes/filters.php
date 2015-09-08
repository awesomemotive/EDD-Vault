<?php
/**
 * Filters
 *
 * @package     EDD\Vault\Filters
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Edit downloads query
 *
 * @since       1.0.0
 * @param       array $query The current query
 * @param       array $atts The shortcode atts
 * @return      array The updated query
 */
function edd_vault_downloads_query( $query, $atts ) {
	$terms = get_option( 'edd_vault_term_status' );
	
	$new_query = array(
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'       => '_edd_vault',
				'value'     => 'true',
				'compare'   => '!='
			),
			array(
				'key'       => '_edd_vault',
				'compare'   => 'NOT EXISTS'
			)
		),
	);

	if( ! empty( $terms['download_category'] ) && ! empty( $terms['download_tag'] ) ) {
		$tax_query = array(
			'tax_query' => array(
				'relation' => 'AND'
			)
		);
	}

	if( ! empty( $terms['download_category'] ) ) {
		$tax_query['tax_query'][] = array(
			'taxonomy'  => 'download_category',
			'field'     => 'term_id',
			'terms'     => array_values( $terms['download_category'] ),
			'operator'  => 'NOT IN'
		);
	}

	if( ! empty( $terms['download_tag'] ) ) {
		$tax_query['tax_query'][] = array(
			'taxonomy'  => 'download_tag',
			'field'     => 'term_id',
			'terms'     => array_values( $terms['download_tag'] ),
			'operator'  => 'NOT IN'
		);
	}

	if( $tax_query ) {
		$new_query = array_merge( $new_query, $tax_query );
	}

	return array_merge( $query, $new_query );
}
add_filter( 'edd_downloads_query', 'edd_vault_downloads_query', 10, 2 );


/**
 * Add a notification bar for admins
 *
 * @since       1.0.0
 * @param       string $the_content The post content
 * @return      string $the_content The updated post content
 */
function edd_vault_display_admin_notice( $the_content ) {
	if( isset( $GLOBALS['post'] ) && $GLOBALS['post']->post_type == 'download' ) {
		$status = array_values( edd_vault_is_stored( $GLOBALS['post']->ID ) );

		if( in_array( true, $status ) ) {
			$the_content = '<div class="edd-vault-notice"><p>' . sprintf( __( 'This %s is currently in the vault.', 'edd-vault' ), edd_get_label_singular( true ) ) . '</p></div>' . $the_content;
		}
	}

	return $the_content;
}
add_filter( 'the_content', 'edd_vault_display_admin_notice' );
