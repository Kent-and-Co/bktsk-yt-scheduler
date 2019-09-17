<?php
// 1つ目、アクションフック
add_action( 'admin_menu', 'bktsk_yt_live_admin_menu' );

// 2つ目、アクションフックで呼ばれる関数
function bktsk_yt_live_admin_menu() {
	add_options_page(
		__( 'YouTube Live Scheduler Settings', 'BktskYtScheduler' ),
		__( 'YT Live Settings', 'BktskYtScheduler' ),
		'administrator',
		'bktskytscheduler',
		'bktsk_yt_live_display_admin_page'
	);
}

// 3つ目、設定画面用のHTML
function bktsk_yt_live_display_admin_page() {
	echo '<div class="wrap">';
	echo '<p>' . __( 'YouTube Live Scheduler Settings', 'BktskYtScheduler' ) . '</p>';
	echo '</div>';
}
