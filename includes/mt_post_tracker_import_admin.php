<?php
$mt_post_tacker_settings = [
	'mt_post_tracker_status_in' => 1,
	'mt_post_tracker_delete_on_uninstall' => 0,
	'mt_post_tracker_show_on_post' => 1,
	'mt_post_tracker_show_on_product' => 1,
	'mt_post_tracker_show_column_post' => 1,
	'mt_post_tracker_show_column_product' => 1,
	'mt_post_tracker_posts_default_sorting' => 1,
	'mt_post_tracker_products_default_sorting' => 1
];

if (isset($_POST['mt_post_tracker_save'])) {
	check_admin_referer('mt_post_tracker_settings_save', 'mt_post_tracker_nonce');

	foreach ($mt_post_tacker_settings as $key => $default) {
		$value = filter_input(INPUT_POST, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		update_option($key, $value !== null ? $value : $default);
	}

	echo '<div class="updated"><p><strong>' . esc_html__('Settings saved successfully.', 'mt_post_tracker') . '</strong></p></div>';
}

if (isset($_POST['mt_post_tracker_reset'])) {
	global $wpdb;
	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = 'mt_post_tracker_views'");
	echo '<div class="updated"><p>' . esc_html__('All statistics have been deleted.', 'mt_post_tracker') . '</p></div>';
}

foreach ($mt_post_tacker_settings as $key => $default) {
	$$key = get_option($key, $default);
}
?>

<div class="mt_post_tracker_container">
	<form name="mt_post_tracker_form" method="post" enctype="multipart/form-data" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<?php wp_nonce_field('mt_post_tracker_settings_save', 'mt_post_tracker_nonce'); ?>
		<div class="mt_post_tracker_page_header">
			<?php esc_html_e('Maxtrade Post Tracker', 'mt_post_tracker'); ?>
			<div>
				<input type="submit" name="mt_post_tracker_save" class="button-primary" value="<?php esc_html_e('Save changes', 'mt_post_tracker'); ?>" />
				<input type="submit" name="mt_post_tracker_reset" class="button" value="Изчисти статистиките" onclick="return confirm('Сигурни ли сте, че искате да изтриете всички посещения?');" />
			</div>
		</div>
		<div class="mt_post_tracker_row">

			<div class="mt_post_tracker_panel">
				<div class="mt_post_tracker_panel_heading">
					<?php esc_html_e('Settings', 'mt_post_tracker'); ?>
				</div>
				<div class="mt_post_tracker_panel_body">
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Turn on the module', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_status_in" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_status_in, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_status_in, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Turning the module on/off', 'mt_post_tracker'); ?></span>
						</div>
					</div>
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Cleanup on uninstall', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_delete_on_uninstall" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_delete_on_uninstall, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_delete_on_uninstall, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Clear all statistics on uninstallation', 'mt_post_tracker'); ?></span>
						</div>
					</div>
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Show in posts (page)', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_show_on_post" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_show_on_post, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_show_on_post, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Show statistics in posts (page)', 'mt_post_tracker'); ?></span>
						</div>
					</div>
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Show in products', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_show_on_product" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_show_on_product, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_show_on_product, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Show statistics in products (page)', 'mt_post_tracker'); ?></span>
						</div>
					</div>
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Show in admin column of posts', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_show_column_post" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_show_column_post, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_show_column_post, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Show statistics in admin column of posts', 'mt_post_tracker'); ?></span>
						</div>
					</div>
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Show in admin product column', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_show_column_product" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_show_column_product, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_show_column_product, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Show statistics in admin product column', 'mt_post_tracker'); ?></span>
						</div>
					</div>
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Sort posts by visits by default', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_posts_default_sorting" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_posts_default_sorting, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_posts_default_sorting, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Sort by visits by default when opening the posts page', 'mt_post_tracker'); ?></span>
						</div>
					</div>
					<div class="mt_post_tracker_form_group">
						<div class="mt_post_tracker_control_label"><?php esc_html_e('Sort products by visits by default', 'mt_post_tracker'); ?></div>
						<div class="mt_post_tracker_control">
							<select name="mt_post_tracker_products_default_sorting" class="mt_post_tracker_form_control">
								<option value=0 <?php selected($mt_post_tracker_products_default_sorting, 0); ?>><?php esc_html_e('Off', 'mt_post_tracker'); ?></option>
								<option value=1 <?php selected($mt_post_tracker_products_default_sorting, 1); ?>><?php esc_html_e('On', 'mt_post_tracker'); ?></option>
							</select>
							<span class="mt_post_tracker_form_controll_text"><?php esc_html_e('Sort by visits by default when opening the products page', 'mt_post_tracker'); ?></span>
						</div>
					</div>
				</div>
			</div>

		</div>
	</form>

</div>