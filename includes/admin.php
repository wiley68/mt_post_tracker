<?php
/**
 * Register plugin settings page in the WordPress admin menu.
 *
 * Adds an options page under the "Settings" menu where plugin configuration can be managed.
 * Access is restricted to users with 'manage_options' capability.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mt_post_tracker_admin_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	add_options_page(
		__( 'Maxtrade Post Tracker - Module settings', 'mt_post_tracker' ),
		__( 'Maxtrade Post Tracker', 'mt_post_tracker' ),
		'manage_options',
		'mt_post_tracker_options',
		'mt_post_tracker_admin_options'
	);
}

/**
 * Display plugin admin options page content.
 *
 * Checks user capabilities before displaying the admin interface.
 * Includes the admin options form from 'mt_post_tracker_import_admin.php'.
 * Displays an error message if the file is not found.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mt_post_tracker_admin_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient rights to access this page.', 'mt_post_tracker' ) );
	}

	$file_path = plugin_dir_path( __FILE__ ) . 'mt_post_tracker_import_admin.php';

	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	} else {
		echo '<div class="error"><p>' . esc_html__( 'The file mt_post_tracker_import_admin.php was not found!', 'mt_post_tracker' ) . '</p></div>';
	}
}
