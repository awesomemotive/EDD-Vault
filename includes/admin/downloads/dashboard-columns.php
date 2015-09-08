<?php
/**
 * Dashboard columns
 *
 * @package     EDD\Vault\Admin\Downloads\DashboardColumns
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add vault status header
 *
 * @since       1.0.0
 * @param       array $columns The existing columns
 * @return      array $columns The updated columns
 */
function edd_vault_dashboard_columns( $columns ) {
	$columns['vault-status'] = __( 'Vault', 'edd-vault' );

	return $columns;
}
add_filter( 'manage_edit-download_category_columns', 'edd_vault_dashboard_columns' );
add_filter( 'manage_edit-download_tag_columns', 'edd_vault_dashboard_columns' );
add_filter( 'manage_edit-download_columns', 'edd_vault_dashboard_columns' );


/**
 * Add vault status content (download)
 *
 * @since       1.0.0
 * @param       string $column The column we are working with
 * @param       int $id The item ID
 * @return      void
 */
function edd_vault_dashboard_download_column_content( $column, $id ) {
	switch( $column ) {
		case 'vault-status':
			$status = edd_vault_is_stored( $id );

			if( $status['download'] ) {
				$download_status    = ' edd-vault-status-stored';
				$download_title     = sprintf( __( '%s Is In Vault', 'edd-vault' ), edd_get_label_singular() );
			} else {
				$download_status    = '';
				$download_title     = sprintf( __( '%s Is Not In Vault', 'edd-vault' ), edd_get_label_singular() );
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
			
			echo '<span class="dashicons dashicons-download' . $download_status . '" title="' . $download_title . '"></span>';
			echo '<span class="dashicons dashicons-category' . $download_category_status . '" title="' . $download_category_title . '"></span>';
			echo '<span class="dashicons dashicons-tag' . $download_tag_status . '" title="' . $download_tag_title . '"></span>';
			break;
	}
}
add_action( 'manage_download_posts_custom_column', 'edd_vault_dashboard_download_column_content', 10, 2 );


/**
 * Add vault status content (category/tag)
 *
 * @since       1.0.0
 * @param       string $c Null
 * @param       string $column The column we are working with
 * @param       int $id The item ID
 * @return      void
 */
function edd_vault_dashboard_taxonomy_column_content( $c, $column, $id ) {
	switch( $column ) {
		case 'vault-status':
			if( $_GET['taxonomy'] == 'download_category' ) {
				$title  = sprintf( __( '%s Category Is', 'edd-vault' ), edd_get_label_singular() );
				$icon   = 'category';
			} else {
				$title  = sprintf( __( '%s Tag Is', 'edd-vault' ), edd_get_label_singular() );
				$icon   = 'tag';
			}
			
			if( edd_vault_is_stored( $id, $_GET['taxonomy'] ) ) {
				$status = ' edd-vault-status-stored';
				$title .= ' ' . __( 'In Vault', 'edd-vault' );
			} else {
				$status = '';
				$title .= ' ' . __( 'Not In Vault', 'edd-vault' );
			}
			
			echo '<span class="dashicons dashicons-' . $icon . $status . '" title="' . $title . '"></span>';
			break;
	}
}
add_action( 'manage_download_category_custom_column', 'edd_vault_dashboard_taxonomy_column_content', 10, 3 );
add_action( 'manage_download_tag_custom_column', 'edd_vault_dashboard_taxonomy_column_content', 10, 3 );
