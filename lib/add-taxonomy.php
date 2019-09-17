<?php

function bktsk_yt_live_add_taxonomy() {

	register_taxonomy(
		'bktak-yt-live-taxonomy',
		'bktskytlive',
		array(
			'label'             => __( 'Live Categories', 'BktskYtScheduler' ),
			'singular_label'    => __( 'Live Category', 'BktskYtScheduler' ),
			'labels'            => array(
				'all_items'    => __( 'All live categories', 'BktskYtScheduler' ),
				'add_new_item' => __( 'Add new live category', 'BktskYtScheduler' ),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'hierarchical'      => true,
		)
	);
}

add_action( 'init', 'bktsk_yt_live_add_taxonomy' );
