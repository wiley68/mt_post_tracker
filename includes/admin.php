<?php
/** add admin menu */
function mt_post_tracker_admin_actions() {
	add_options_page( __( 'Maxtrade Post Tracker - Module settings', 'mt_post_tracker'), __( 'Maxtrade Post Tracker', 'mt_post_tracker' ), 'manage_options', "mt_post_tracker_options", "mt_post_tracker_admin_options" );
}
