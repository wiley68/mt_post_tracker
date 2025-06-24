<?php
/**
 * Load plugin textdomain for internationalization.
 *
 * This function loads the translation files for the plugin based on the current locale.
 * Translation files must be placed in the /languages directory of the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mt_post_tracker_load_textdomain() {
	$locale = apply_filters( 'plugin_locale', determine_locale(), 'mt_post_tracker' );
	$mofile = 'mt_post_tracker' . '-' . $locale . '.mo';
	load_textdomain( 'mt_post_tracker', MT_POST_TRACKER_PLUGIN_DIR . '/languages/' . $mofile );
}

/**
 * Enqueue admin CSS styles for the plugin settings page.
 *
 * This function checks if the current admin screen is the plugin's settings page,
 * and enqueues the admin CSS file only for that screen.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mt_post_tracker_add_meta_admin() {
	$screen = get_current_screen();

	if ( isset( $screen->id ) && 'settings_page_mt_post_tracker_options' === $screen->id ) {
		if ( ! wp_style_is( 'mt_post_tracker_style_admin', 'enqueued' ) ) {
			wp_enqueue_style(
				'mt_post_tracker_style_admin',
				MT_POST_TRACKER_CSS_URI . '/mt_post_tracker_admin.css',
				array(),
				filemtime( MT_POST_TRACKER_PLUGIN_DIR . '/css/mt_post_tracker_admin.css' ),
				'all'
			);
		}
	}
}

/**
 * Enqueue plugin CSS and JS assets on single post or product pages.
 *
 * This function loads the plugin's frontend CSS and JavaScript files only when viewing
 * a single post or WooCommerce product. It also localizes the JS script with necessary
 * AJAX parameters.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mt_post_tracker_add_meta() {
	if ( is_singular( [ 'post', 'product' ] ) ) {
		wp_enqueue_style(
			'mt_post_tracker_css',
			MT_POST_TRACKER_CSS_URI . '/mt_post_tracker.css',
			array(),
			filemtime( MT_POST_TRACKER_PLUGIN_DIR . '/css/mt_post_tracker.css' ),
			'all'
		);

		wp_enqueue_script(
			'mt_post_tracker_js',
			MT_POST_TRACKER_JS_URI . '/mt_post_tracker.js',
			array( 'jquery' ),
			filemtime( MT_POST_TRACKER_PLUGIN_DIR . '/js/mt_post_tracker.js' ),
			true
		);

		wp_localize_script(
			'mt_post_tracker_js',
			'mt_post_tracker_js',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'post_id'  => get_the_ID(),
				'nonce'    => wp_create_nonce( 'mt_post_tracker_nonce' ),
			)
		);
	}
}

/**
 * Plugin activation callback to set default WooCommerce catalog sorting.
 *
 * Sets 'mt_post_tracker_views' as the default sorting option for the WooCommerce product catalog
 * when the plugin is activated.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mt_post_tracker_activate() {
	update_option( 'woocommerce_default_catalog_orderby', 'mt_post_tracker_views' );
}

/**
 * Handle AJAX request to track post or product views.
 *
 * Increments the view counter for the current post or product,
 * while preventing duplicate counting within the same session and IP.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON response.
 */
function mt_post_tracker_track_view() {
	check_ajax_referer( 'mt_post_tracker_nonce', 'nonce' );

	$post_id = (int) $_POST['post_id'];
	if ( ! $post_id || get_post_status( $post_id ) !== 'publish' ) {
		wp_send_json_error();
	}

	$ip  = $_SERVER['REMOTE_ADDR'];
	$key = 'mt_post_tracker_' . md5( $ip . '_' . $post_id );

	if ( ! isset( $_SESSION ) ) {
		session_start();
	}

	if ( isset( $_SESSION[ $key ] ) ) {
		wp_send_json_success( array( 'message' => 'already tracked' ) );
	}

	$_SESSION[ $key ] = true;

	$count = get_post_meta( $post_id, 'mt_post_tracker_views', true );
	$count = $count ? (int) $count + 1 : 1;
	update_post_meta( $post_id, 'mt_post_tracker_views', $count );

	wp_send_json_success( array( 'views' => $count ) );
}

function mt_post_tracker_admin_head() {
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

/**
 * Add custom column for view count in admin post/product lists.
 *
 * @since 1.0.0
 *
 * @param array $columns Existing columns.
 * @return array Modified columns with the new "Views" column.
 */
function mt_post_tracker_add_column( $columns ) {
	$columns['mt_post_tracker_views'] = esc_html__( 'Views', 'mt_post_tracker' );
	return $columns;
}

/**
 * Display the view count value in the custom column.
 *
 * @since 1.0.0
 *
 * @param string $column Column name.
 * @param int    $post_id Post ID.
 * @return void
 */
function mt_post_tracker_show_column( $column, $post_id ) {
	if ( $column === 'mt_post_tracker_views' ) {
		$views = get_post_meta( $post_id, 'mt_post_tracker_views', true );
		echo $views !== '' ? intval( $views ) : '';
	}
}

/**
 * Make the custom view count column sortable in admin lists.
 *
 * @since 1.0.0
 *
 * @param array $columns Existing sortable columns.
 * @return array Modified sortable columns.
 */
function mt_post_tracker_sortable_column( $columns ) {
	$columns['mt_post_tracker_views'] = 'mt_post_tracker_views';
	return $columns;
}

/**
 * Modify admin queries to sort by view count when applicable.
 *
 * Handles both default sorting (if enabled) and manual column sorting by the admin user.
 * Ensures posts/products without view meta are also included in the result set.
 *
 * @since 1.0.0
 *
 * @param WP_Query $query The current query object.
 * @return void
 */
function mt_post_tracker_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	// Handle manual column sorting
	if ( $orderby === 'mt_post_tracker_views' ) {
		$query->set(
			'meta_query',
			array(
				'relation'                   => 'OR',
				'mt_post_tracker_views_clause' => array(
					'key'  => 'mt_post_tracker_views',
					'type' => 'NUMERIC',
				),
				array(
					'key'     => 'mt_post_tracker_views',
					'compare' => 'NOT EXISTS',
				),
			)
		);

		$query->set(
			'orderby',
			array(
				'mt_post_tracker_views_clause' => strtoupper( $query->get( 'order' ) ) === 'ASC' ? 'ASC' : 'DESC',
			)
		);
		return;
	}

	// Handle default sorting if enabled
	if (
		( $screen->id === 'edit-post' && ! $query->get( 'orderby' ) && get_option( 'mt_post_tracker_posts_default_sorting' ) == 1 ) ||
		( $screen->id === 'edit-product' && ! $query->get( 'orderby' ) && get_option( 'mt_post_tracker_products_default_sorting' ) == 1 )
	) {
		$query->set(
			'meta_query',
			array(
				'relation'                   => 'OR',
				'mt_post_tracker_views_clause' => array(
					'key'  => 'mt_post_tracker_views',
					'type' => 'NUMERIC',
				),
				array(
					'key'     => 'mt_post_tracker_views',
					'compare' => 'NOT EXISTS',
				),
			)
		);

		$query->set(
			'orderby',
			array(
				'mt_post_tracker_views_clause' => 'DESC',
			)
		);
	}
}

/**
 * Display the view count inside the WooCommerce product meta section.
 *
 * Adds a new line in the product meta area showing the number of views,
 * if any views have been registered for the product.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mt_post_tracker_show_product_meta() {
	global $post;

	$views = intval( get_post_meta( $post->ID, 'mt_post_tracker_views', true ) );

	if ( $views ) {
		echo '<span class="mt_product_wrapper"><span class="mt_label">' . esc_html__( 'Views: ', 'mt_post_tracker' ) . '</span><span class="mt_value">' . esc_html( $views ) . '</span></span>';
	}
}

/**
 * Append the view count to the content of single post pages.
 *
 * If the current post has recorded views, displays the view count below the post content.
 * This only affects single post pages and only for the main query inside the loop.
 *
 * @since 1.0.0
 *
 * @param string $content The original post content.
 * @return string Modified post content with view count appended.
 */
function mt_post_tracker_show_content( $content ) {
	if ( is_singular( 'post' ) && in_the_loop() && is_main_query() ) {
		$views = intval( get_post_meta( get_the_ID(), 'mt_post_tracker_views', true ) );

		if ( $views ) {
			$views_html  = '<span class="mt_product_wrapper"><span class="mt_label">' . esc_html__( 'Views: ', 'mt_post_tracker' ) . '</span>';
			$views_html .= '<span class="mt_value">' . esc_html( $views ) . '</span></span>';
			$content    .= $views_html;
		}
	}

	return $content;
}

/**
 * Add custom sorting option to WooCommerce catalog orderby dropdown.
 *
 * Adds a new option "Views" to the sorting dropdown on the WooCommerce shop page,
 * allowing users to sort products by the number of views.
 *
 * @since 1.0.0
 *
 * @param array $sortby Existing sorting options.
 * @return array Modified sorting options including the custom view count option.
 */
function mt_post_tracker_catalog_orderby( $sortby ) {
	$sortby['popularity'] = __( 'Best sellers first', 'mt_post_tracker' );
	$sortby['mt_post_tracker_views'] = esc_html__( 'Most popular first', 'mt_post_tracker' );
	return $sortby;
}

/**
 * Modify WooCommerce catalog ordering arguments for custom "views" sorting.
 *
 * When the "mt_post_tracker_views" orderby option is selected, adjust the
 * WooCommerce query arguments to sort products by view count (meta_value_num).
 *
 * @since 1.0.0
 *
 * @param array $args Existing WooCommerce catalog ordering arguments.
 * @return array Modified ordering arguments.
 */
function mt_post_tracker_catalog_ordering_args( $args ) {
	$current_orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : get_option( 'woocommerce_default_catalog_orderby' );

	if ( $current_orderby === 'mt_post_tracker_views' ) {
		$args['orderby'] = 'meta_value_num';
		$args['order']   = 'desc';
	}
	return $args;
}

/**
 * Modify WooCommerce product meta query to include products without view count meta.
 *
 * When sorting by "mt_post_tracker_views", ensures that products without the
 * 'mt_post_tracker_views' meta key are still included in the results.
 *
 * @since 1.0.0
 *
 * @param array     $meta_query Existing meta query conditions.
 * @param WC_Query  $query      The WooCommerce product query object.
 * @return array Modified meta query conditions.
 */
function mt_post_tracker_catalog_meta_query( $meta_query, $query ) {
	$current_orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : get_option( 'woocommerce_default_catalog_orderby' );

	if ( $current_orderby === 'mt_post_tracker_views' ) {
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'  => 'mt_post_tracker_views',
				'type' => 'NUMERIC',
			),
			array(
				'key'     => 'mt_post_tracker_views',
				'compare' => 'NOT EXISTS',
			),
		);
	}
	return $meta_query;
}

/**
 * Set the default WooCommerce catalog sorting to custom "views" order.
 *
 * Forces WooCommerce to use "mt_post_tracker_views" as the default sorting
 * option for the product catalog pages.
 *
 * @since 1.0.0
 *
 * @param string $default_orderby Current default orderby value.
 * @return string Modified default orderby value.
 */
function mt_post_tracker_default_catalog_orderby( $default_orderby ) {
	return 'mt_post_tracker_views';
};
