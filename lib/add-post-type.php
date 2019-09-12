<?php
add_action( 'init', 'bktsk_yt_scheduler_post_type_init' );


function bktsk_yt_scheduler_post_type_init() {
	$labels = array(
		'name'               => _x( 'YouTube Live Schedules', 'post type general name', 'BktskYtScheduler' ),
		'singular_name'      => _x( 'YouTube Live Schedule', 'post type singular name', 'BktskYtScheduler' ),
		'menu_name'          => _x( 'YT Live', 'admin menu', 'BktskYtScheduler' ),
		'name_admin_bar'     => _x( 'YT Live', 'add new on admin bar', 'BktskYtScheduler' ),
		'add_new'            => _x( 'Add New', 'Live', 'BktskYtScheduler' ),
		'add_new_item'       => __( 'Add New Live', 'BktskYtScheduler' ),
		'new_item'           => __( 'New Live', 'BktskYtScheduler' ),
		'edit_item'          => __( 'Edit Live', 'BktskYtScheduler' ),
		'view_item'          => __( 'View Live', 'BktskYtScheduler' ),
		'all_items'          => __( 'All Live Schedules', 'BktskYtScheduler' ),
		'search_items'       => __( 'Search Live Schedules', 'BktskYtScheduler' ),
		'parent_item_colon'  => __( 'Parent Lives:', 'BktskYtScheduler' ),
		'not_found'          => __( 'No Live found.', 'BktskYtScheduler' ),
		'not_found_in_trash' => __( 'No Live found in Trash.', 'BktskYtScheduler' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'live_schedule' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-calendar-alt',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
	);

	register_post_type( 'BktskYtLive', $args );
}

add_filter( 'use_block_editor_for_post', 'bktsk_yt_scheduler_block_disabler', 10, 2 );

function bktsk_yt_scheduler_block_disabler( $use_block_editor, $post ) {
	if ( 'BktskYtLive' === $post->post_type ) {
		$use_block_editor = false;
	}
	return $use_block_editor;
}
