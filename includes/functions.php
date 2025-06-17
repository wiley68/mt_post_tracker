<?php

/** add text domain */
function mt_post_tracker_load_textdomain()
{
	$locale = apply_filters('plugin_locale', determine_locale(), 'mt_post_tracker');
	$mofile = 'mt_post_tracker' . '-' . $locale . '.mo';
	load_textdomain('mt_post_tracker', MT_POST_TRACKER_PLUGIN_DIR . '/languages/' . $mofile);
}

/** do output buffer */
function mt_post_tracker_do_output_buffer()
{
	ob_start();
}

/** add style and scripts front */
function mt_post_tracker_add_meta()
{
	if (is_singular(['post', 'product'])) {
		wp_enqueue_style('mt_post_tracker_css', MT_POST_TRACKER_CSS_URI . '/mt_post_tracker.css', array(), filemtime(MT_POST_TRACKER_PLUGIN_DIR . '/css/mt_post_tracker.css'), 'all');
		wp_enqueue_script('mt_post_tracker_js', MT_POST_TRACKER_JS_URI . '/mt_post_tracker.js', array('jquery'), filemtime(MT_POST_TRACKER_PLUGIN_DIR . '/js/mt_post_tracker.js'), true);
		wp_localize_script(
			'mt_post_tracker_js',
			'mt_post_tracker_js',
			array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'post_id'  => get_the_ID(),
				'nonce'    => wp_create_nonce('mt_post_tracker_nonce'),
			)
		);
	}
}

/** add style and scripts admin */
function mt_post_tracker_add_meta_admin()
{
	$screen = get_current_screen();
	if (isset($screen->id) && 'settings_page_mt_post_tracker_options' === $screen->id) {
		if (! wp_style_is('mt_post_tracker_style_admin', 'enqueued')) {
			wp_enqueue_style('mt_post_tracker_style_admin', MT_POST_TRACKER_CSS_URI . '/mt_post_tracker_admin.css', array(), filemtime(MT_POST_TRACKER_PLUGIN_DIR . '/css/mt_post_tracker_admin.css'), 'all');
		}
	}
}

// AJAX handler
function mt_post_tracker_track_view()
{
	check_ajax_referer('mt_post_tracker_nonce', 'nonce');

	$post_id = (int)$_POST['post_id'];
	if (!$post_id || get_post_status($post_id) !== 'publish') {
		wp_send_json_error();
	}

	$ip = $_SERVER['REMOTE_ADDR'];
	$key = 'mt_post_tracker_' . md5($ip . '_' . $post_id);
	if (!isset($_SESSION)) {
		session_start();
	}

	if (isset($_SESSION[$key])) {
		wp_send_json_success(['message' => 'already tracked']);
	}

	$_SESSION[$key] = true;

	$count = get_post_meta($post_id, 'mt_post_tracker_views', true);
	$count = $count ? (int)$count + 1 : 1;
	update_post_meta($post_id, 'mt_post_tracker_views', $count);

	wp_send_json_success(['views' => $count]);
}

// Show column in admin list of products and posts
function mt_post_tracker_add_column($columns)
{
	$columns['mt_post_tracker_views'] = esc_html__('Посещения', 'mt_post_tracker');
	return $columns;
}

function mt_post_tracker_show_column($column, $post_id)
{
	if ($column === 'mt_post_tracker_views') {
		echo (int)get_post_meta($post_id, 'mt_post_tracker_views', true);
	}
}

function mt_post_tracker_admin_head()
{
	$screen = get_current_screen();
	if ($screen && $screen->post_type === 'product') {
		echo '<style>
				th#mt_post_tracker_views, td.mt_post_tracker_views {
					width: 80px;
					text-align: center;
				}
			</style>';
	}
}

function mt_post_tracker_show_product_meta()
{
	global $post;
	$views = intval(get_post_meta($post->ID, 'mt_post_tracker_views', true));
	if ($views) {
		echo '<span class="sku_wrapper"><span class="label">Прегледи: </span><span class="sku">' . $views . '</span></span>';
	}
};
