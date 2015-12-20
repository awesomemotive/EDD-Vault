<?php
/**
 * Plugin Name:     Easy Digital Downloads - Vault
 * Plugin URI:      https://easydigitaldownloads.com/extension/vault
 * Description:     Allow site owners to rotate stock by placing items in a restricted 'vault'
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:	    https://section214.com
 * Text Domain:     edd-vault
 *
 * @package         EDD\Vault
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'EDD_Vault' ) ) {


	/**
	 * Main EDD_Vault class
	 *
	 * @since       1.0.0
	 */
	class EDD_Vault {


		/**
		 * @var         EDD_Vault $instance The one true EDD_Vault
		 * @since       1.0.0
		 */
		public static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      self::$instance The one true EDD_Vault
		 */
		public static function instance() {
			if( ! self::$instance ) {
				self::$instance = new EDD_Vault();
				self::$instance->setup_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function setup_constants() {
			// Plugin version
			define( 'EDD_VAULT_VER', '1.0.0' );

			// Plugin path
			define( 'EDD_VAULT_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_VAULT_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			require_once EDD_VAULT_DIR . 'includes/functions.php';
			require_once EDD_VAULT_DIR . 'includes/scripts.php';
			require_once EDD_VAULT_DIR . 'includes/actions.php';
			require_once EDD_VAULT_DIR . 'includes/filters.php';

			if( is_admin() ) {
				if( edd_get_option( 'vault_status_column', false ) == true ) {
					require_once EDD_VAULT_DIR . 'includes/admin/downloads/dashboard-columns.php';
				}

				require_once EDD_VAULT_DIR . 'includes/admin/downloads/actions.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {
			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Vault', EDD_VAULT_VER, 'Daniel J Griffiths' );
			}

			// Add extension settings
			add_filter( 'edd_settings_extensions', array( $this, 'settings' ) );
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) )  . '/languages/';
			$lang_dir = apply_filters( 'edd_vault_language_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), '' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-vault', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/edd-vault/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-vault/ folder
				load_textdomain( 'edd-vault', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-vault/languages/ folder
				load_textdomain( 'edd-vault', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-vault', false, $lang_dir );
			}
		}


		/**
		 * Register settings
		 *
		 * @access      public
		 * @since       1.0.0
		 * @param       array $settings The existing settings
		 * @return      array $settings The updated settings
		 */
		public function settings( $settings ) {
			$new_settings = array(
				'vault_header' => array(
					'id'    => 'vault_header',
					'name'  => '<strong>' . __( 'Vault Settings', 'edd-vault' ) . '</strong>',
					'desc'  => __( 'Configure vault settings', 'edd-vault' ),
					'type'  => 'header'
				),
				'vault_status_column' => array(
					'id'    => 'vault_status_column',
					'name'  => __( 'Display Status Column', 'edd-vault' ),
					'desc'  => __( 'Check to display a status column on download, category and tag pages.', 'edd-vault' ),
					'type'  => 'checkbox'
				),
				'vault_redirect' => array(
					'id'    => 'vault_redirect',
					'name'  => __( 'Redirect Page', 'edd-vault' ),
					'desc'  => __( 'Select the page to redirect users to if they directly access a stored page.', 'edd-vault' ),
					'type'  => 'select',
					'options'   => edd_get_pages()
				),
				'vault_notice_text' => array(
					'id' => 'vault_notice_text',
					'name' => __( 'Vault Notice Text', 'edd-vault' ),
					'desc' => __( 'Change the text displayed to admins when viewing a product that is in the vault.', 'edd-vault' ),
					'type' => 'text',
					'std' => sprintf( __( 'This %s is currently in the vault.', 'edd-vault' ), edd_get_label_singular( true ) )
				)
			);

			return array_merge( $settings, $new_settings );
		}
	}
}


/**
 * The main function responsible for returning the one true EDD_Vault
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Vault The one true EDD_Vault
 */
function edd_vault() {
	if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		if( ! class_exists( 'S214_EDD_Activation' ) ) {
			require_once 'includes/libraries/class.s214-edd-activation.php';
		}

		$activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		return EDD_Vault::instance();
	} else {
		return EDD_Vault::instance();
	}
}
add_action( 'plugins_loaded', 'edd_vault' );
