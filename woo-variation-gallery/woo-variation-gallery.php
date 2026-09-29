<?php
/**
 * Plugin Name: Additional Variation Images Gallery for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/woo-variation-gallery/
 * Description: Allows inserting multiple images for per variation to let visitors see a different images when WooCommerce product variations are switched.
 * Author: Emran Ahmed
 * Version: 1.4.1
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.7
 * Tested up to: 7.1
 * WC requires at least: 5.8
 * WC tested up to: 11.1
 * Requires Plugins: woocommerce
 * Text Domain: woo-variation-gallery
 * Author URI: https://getwooplugins.com/
 */

defined( 'ABSPATH' ) or die( 'Keep Silent' );

use \Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'WOO_VARIATION_GALLERY_PLUGIN_FILE' ) ) {
	define( 'WOO_VARIATION_GALLERY_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WOO_VARIATION_GALLERY_PLUGIN_VERSION' ) ) {
	define( 'WOO_VARIATION_GALLERY_PLUGIN_VERSION', '1.4.0' );
}

if ( ! defined( 'WOO_VARIATION_GALLERY_MINIMUM_COMPATIBLE_PRO_PLUGIN_VERSION' ) ) {
	define( 'WOO_VARIATION_GALLERY_MINIMUM_COMPATIBLE_PRO_PLUGIN_VERSION', '1.4.0' );
}

if ( ! defined( 'WOO_VARIATION_GALLERY_MAYBE_PRO_PLUGIN_FILE' ) ) {
	$plugin_file = sprintf( '%s/woo-variation-gallery-pro/woo-variation-gallery-pro.php', wp_normalize_path( WP_PLUGIN_DIR ) );
	define( 'WOO_VARIATION_GALLERY_MAYBE_PRO_PLUGIN_FILE', $plugin_file );
}

/**
 * Check is using correct version of pro plugin.
 *
 * @return bool
 */
function woo_variation_gallery_is_using_compatible_pro_version(): bool {
	return defined( 'WOO_VARIATION_GALLERY_PRO_PLUGIN_VERSION' ) && ( version_compare( constant( 'WOO_VARIATION_GALLERY_PRO_PLUGIN_VERSION' ), constant( 'WOO_VARIATION_GALLERY_MINIMUM_COMPATIBLE_PRO_PLUGIN_VERSION' ) ) >= 0 );
}

/**
 * Prevent Pro plugin.
 *
 * @return void
 */
function woo_variation_gallery_deactivate_notice_pro() {
	if ( woo_variation_gallery_is_using_compatible_pro_version() ) {
		return;
	}

	/* translators: %s: Pro Plugin Version */
	$notice_text = sprintf( esc_html__( 'You are running older version of "Variation Gallery for WooCommerce - Pro". Please upgrade to %s or upper and continue.', 'woo-variation-gallery' ), esc_html( constant( 'WOO_VARIATION_GALLERY_MINIMUM_COMPATIBLE_PRO_PLUGIN_VERSION' ) ) );

	printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', esc_html( $notice_text ) );
}


/**
 * Prevent activating pro old version.
 *
 * @return void
 */
function woo_variation_gallery_deactivate_pro() {
	if ( woo_variation_gallery_is_using_compatible_pro_version() ) {
		return;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( 'woo-variation-gallery-pro/woo-variation-gallery-pro.php' ) ) {
		// Suppress "Plugin activated." notice.
		unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Display notice why pro version cannot activate.
		add_action( 'admin_notices', 'woo_variation_gallery_deactivate_notice_pro' );
		// Deactivate the plugin silently, Prevent deactivation hooks from running.
		deactivate_plugins( 'woo-variation-gallery-pro/woo-variation-gallery-pro.php', true );
	}
}

/**
 * Show notice on plugin row.
 *
 * @param string $plugin_file Refer to {@see 'plugin_row_meta'} filter.
 * @param array  $plugin_data Refer to {@see 'plugin_row_meta'} filter.
 *
 * @return void
 */
function woo_variation_gallery_row_meta_notice_pro( string $plugin_file, array $plugin_data ) {
	if ( plugin_basename( WOO_VARIATION_GALLERY_MAYBE_PRO_PLUGIN_FILE ) === $plugin_file ) {
		$current_version = $plugin_data['Version'];
		if ( version_compare( $current_version, constant( 'WOO_VARIATION_GALLERY_MINIMUM_COMPATIBLE_PRO_PLUGIN_VERSION' ), '<' ) ) {
			/* translators: %s: Pro Plugin Version */
			$notice_text = sprintf( esc_html__( 'You are running older version of "Variation Gallery for WooCommerce - Pro". Please upgrade to %s or upper.', 'woo-variation-gallery' ), esc_html( constant( 'WOO_VARIATION_GALLERY_MINIMUM_COMPATIBLE_PRO_PLUGIN_VERSION' ) ) );

			printf( '<p style="color: darkred"><span class="dashicons dashicons-warning"></span> <strong>%s</strong></p>', esc_html( $notice_text ) );
		}
	}
}

add_action( 'plugins_loaded', 'woo_variation_gallery_deactivate_pro', 9 );
add_action( 'after_plugin_row_meta', 'woo_variation_gallery_row_meta_notice_pro', 10, 2 );

// Include the main class.
if ( ! class_exists( 'Woo_Variation_Gallery', false ) ) {
	require_once dirname( __FILE__ ) . '/includes/class-woo-variation-gallery.php';
}

// Require WooCommerce admin message
function woo_variation_gallery_wc_requirement_notice() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		$text    = esc_html__( 'WooCommerce', 'woo-variation-gallery' );
		$link    = esc_url( add_query_arg( array(
			'tab'       => 'plugin-information',
			'plugin'    => 'woocommerce',
			'TB_iframe' => 'true',
			'width'     => '640',
			'height'    => '500',
		), admin_url( 'plugin-install.php' ) ) );
		$message = wp_kses( __( "<strong>Additional Variation Images Gallery for WooCommerce</strong> is an add-on of ", 'woo-variation-gallery' ), array( 'strong' => array() ) );

		printf( '<div class="%1$s"><p>%2$s <a class="thickbox open-plugin-details-modal" href="%3$s"><strong>%4$s</strong></a></p></div>', 'notice notice-error', $message, $link, $text );
	}
}

add_action( 'admin_notices', 'woo_variation_gallery_wc_requirement_notice' );

/**
 * Returns the main instance.
 */

function woo_variation_gallery() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

	if ( function_exists( 'woo_variation_gallery_pro' ) && woo_variation_gallery_is_using_compatible_pro_version() ) {
		return woo_variation_gallery_pro();
	}

	return Woo_Variation_Gallery::instance();
}

add_action( 'plugins_loaded', 'woo_variation_gallery' );

// Supporting WooCommerce High-Performance Order Storage
function woo_variation_gallery_hpos_compatibility() {
	if ( class_exists( FeaturesUtil::class ) ) {
		FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

add_action( 'before_woocommerce_init', 'woo_variation_gallery_hpos_compatibility' );

register_activation_hook( __FILE__, array( 'Woo_Variation_Gallery', 'plugin_activated' ) );
