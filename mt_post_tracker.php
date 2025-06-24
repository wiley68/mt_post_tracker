<?php
/**
 * Plugin Name:       MT Post Tracker
 * Plugin URI:        https://avalonbg.com/plugins/mt-post-tracker
 * Description:       Tracks and displays simple visit statistics for posts and products.
 * Version:           1.0.1
 * Author:            Avalon Ltd.
 * Author URI:        https://avalonbg.com
 * Text Domain:       mt_post_tracker
 * Domain Path:       /languages
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package MT_Post_Tracker
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
	exit;
}

/**
 * Check if WooCommerce is active.
 */
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) || in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins' ) ) ) ) {

	/**
	 * Define plugin constants.
	 *
	 * MT_POST_TRACKER_PLUGIN_DIR   - Absolute path to the plugin's root directory.
	 * MT_POST_TRACKER_INCLUDES_DIR - Absolute path to the plugin's includes subdirectory.
	 * MT_POST_TRACKER_CSS_URI      - URL to the plugin's CSS directory.
	 * MT_POST_TRACKER_JS_URI       - URL to the plugin's JS directory.
	 * MT_POST_TRACKER_VERSION      - Plugin version.
	 */
	define( 'MT_POST_TRACKER_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	define( 'MT_POST_TRACKER_INCLUDES_DIR', MT_POST_TRACKER_PLUGIN_DIR . '/includes' );
	define( 'MT_POST_TRACKER_CSS_URI', WP_CONTENT_URL . '/plugins/mt_post_tracker/css' );
	define( 'MT_POST_TRACKER_JS_URI', WP_CONTENT_URL . '/plugins/mt_post_tracker/js' );
	define( 'MT_POST_TRACKER_VERSION', '1.0.1' );

	/**
	 * Include plugin core files.
	 *
	 * functions.php - General helper functions and AJAX handlers.
	 * admin.php     - Admin interface, settings page and admin hooks.
	 */
	require_once MT_POST_TRACKER_INCLUDES_DIR . '/functions.php';
	require_once MT_POST_TRACKER_INCLUDES_DIR . '/admin.php';

	/**
	 * Register plugin activation hook to initialize default settings.
	 *
	 * When the plugin is activated, this hook triggers the function
	 * 'mt_post_tracker_activate()' to set up initial configuration.
	 *
	 * @since 1.0.0
	 */
	register_activation_hook( __FILE__, 'mt_post_tracker_activate' );

	/**
	 * Initialize plugin hooks after plugins are loaded.
	 */
	add_action( 'plugins_loaded', function() {

		// Load plugin translations.
		mt_post_tracker_load_textdomain();

		// Register admin menu and enqueue admin scripts.
		add_action( 'admin_menu', 'mt_post_tracker_admin_actions' );
		add_action( 'admin_enqueue_scripts', 'mt_post_tracker_add_meta_admin' );

		// Enqueue frontend scripts.
		add_action( 'wp_enqueue_scripts', 'mt_post_tracker_add_meta' );

		// Check if tracking is enabled.
		$is_active = (int) get_option( 'mt_post_tracker_status_in' );
		if ( $is_active === 1 ) {

			// Register AJAX handlers for tracking views.
			add_action( 'wp_ajax_nopriv_mt_post_tracker_track_view', 'mt_post_tracker_track_view' );
			add_action( 'wp_ajax_mt_post_tracker_track_view', 'mt_post_tracker_track_view' );

			// Add additional hooks only if tracking is active.
			add_action( 'admin_head', 'mt_post_tracker_admin_head' );
			add_filter( 'manage_post_posts_columns', 'mt_post_tracker_add_column' );
			add_filter( 'manage_product_posts_columns', 'mt_post_tracker_add_column' );
			add_action( 'manage_post_posts_custom_column', 'mt_post_tracker_show_column', 10, 2 );
			add_action( 'manage_product_posts_custom_column', 'mt_post_tracker_show_column', 10, 2 );
			add_filter( 'manage_edit-post_sortable_columns', 'mt_post_tracker_sortable_column' );
			add_filter( 'manage_edit-product_sortable_columns', 'mt_post_tracker_sortable_column' );
			add_action( 'pre_get_posts', 'mt_post_tracker_orderby' );
			add_action( 'woocommerce_catalog_orderby', 'mt_post_tracker_catalog_orderby' );
			add_action( 'woocommerce_get_catalog_ordering_args', 'mt_post_tracker_catalog_ordering_args' );
			add_filter( 'woocommerce_product_query_meta_query', 'mt_post_tracker_catalog_meta_query', 10, 2 );
			add_action( 'woocommerce_default_catalog_orderby', 'mt_post_tracker_default_catalog_orderby' );

			// Display product meta if enabled.
			if ( (int) get_option( 'mt_post_tracker_show_on_product' ) ) {
				add_action( 'woocommerce_product_meta_end', 'mt_post_tracker_show_product_meta' );
			}

			// Display content meta if enabled.
			if ( (int) get_option( 'mt_post_tracker_show_on_post' ) ) {
				add_action( 'the_content', 'mt_post_tracker_show_content' );
			}
		}
	});
}
