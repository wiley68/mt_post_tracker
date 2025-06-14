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
