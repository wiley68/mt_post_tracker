jQuery(function ($) {
	$.post(mt_post_tracker_js.ajax_url, {
		action: 'mt_post_tracker_track_view',
		nonce: mt_post_tracker_js.nonce,
		post_id: mt_post_tracker_js.post_id
	});
});
