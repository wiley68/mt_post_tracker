<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

global $wpdb;
$meta_key = 'mt_post_tracker_views';

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM $wpdb->postmeta WHERE meta_key = %s",
		$meta_key
	)
);

$mt_post_tacker_settings = [
	'mt_post_tracker_status_in',
	'mt_post_tracker_delete_on_uninstall',
	'mt_post_tracker_show_on_post',
	'mt_post_tracker_show_on_product',
	'mt_post_tracker_show_column_post',
	'mt_post_tracker_show_column_product'
];

/**
 * Loop through each option and delete it from the database.
 */
foreach ($mt_post_tacker_settings as $option) {
	delete_option($option);
	delete_site_option($option);
}

/**
 * Flush the cache after options are deleted.
 */
wp_cache_flush();
