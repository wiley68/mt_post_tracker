<?php

/**
 * Plugin Name: Maxtrade Simple Post Views Tracker
 * Plugin URI: 
 * Description: Stores and displays brief information about visits to posts, and products
 * Version: 1.0.1
 * Author: Авалон ООД
 * Author URI: http://avalonbg.com
 * Text Domain: mt_post_tracker
 * Domain Path: /languages
 * Network: 
 * License: GPL3.0
 */
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
/**
 * Check if WooCommerce is active
 **/
// Makes sure the plugin is defined before trying to use it
if (! function_exists('is_plugin_active_for_network')) {
	require_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

if ((is_plugin_active_for_network('woocommerce/woocommerce.php')) || in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	/** definitions */
	define('MT_POST_TRACKER_PLUGIN_DIR', untrailingslashit(dirname(__FILE__)));
	define('MT_POST_TRACKER_INCLUDES_DIR', MT_POST_TRACKER_PLUGIN_DIR . '/includes');
	define('MT_POST_TRACKER_CSS_URI', WP_CONTENT_URL . '/plugins/mt_post_tracker/css');
	define('MT_POST_TRACKER_JS_URI', WP_CONTENT_URL . '/plugins/mt_post_tracker/js');
	define('MT_POST_TRACKER_VERSION', '1.0.1');

	/** includes */
	require_once MT_POST_TRACKER_INCLUDES_DIR . '/functions.php';
	require_once MT_POST_TRACKER_INCLUDES_DIR . '/admin.php';

	/** add text domain */
	add_action('init', 'mt_post_tracker_load_textdomain');

	//make theme ready for translation
	load_plugin_textdomain('mt_post_tracker', false, MT_POST_TRACKER_PLUGIN_DIR . '/languages');

	/** add admin menu options page ###includes/admin.php### */
	add_action('admin_menu', 'mt_post_tracker_admin_actions');
	/** output buffer ###includes/functions.php### */
	add_action('init', 'mt_post_tracker_do_output_buffer');

	/** add style and scripts front */
	add_action('wp_enqueue_scripts', 'mt_post_tracker_add_meta');
	/** add style and scripts admin */
	add_action('admin_enqueue_scripts', 'mt_post_tracker_add_meta_admin');

	$is_active = (int) get_option('mt_post_tracker_status_in');
	if ($is_active) {
		// AJAX handler
		add_action('wp_ajax_nopriv_mt_post_tracker_track_view', 'mt_post_tracker_track_view');
		add_action('wp_ajax_mt_post_tracker_track_view', 'mt_post_tracker_track_view');

		// Show column in admin list of products and posts
		add_action('admin_head', 'mt_post_tracker_admin_head');
		add_filter('manage_post_posts_columns', 'mt_post_tracker_add_column');
		add_filter('manage_product_posts_columns', 'mt_post_tracker_add_column');
		add_action('manage_post_posts_custom_column', 'mt_post_tracker_show_column', 10, 2);
		add_action('manage_product_posts_custom_column', 'mt_post_tracker_show_column', 10, 2);
		//add_action('woocommerce_product_meta_end', 'mt_post_tracker_show_product_meta');
	}
}
