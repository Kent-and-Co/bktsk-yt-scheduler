<?php

function bktsk_yt_live_add_taxonomy() {

	$bktsk_yt_live_options       = get_option( 'bktsk_yt_scheduler_options' );
	$bktsk_yt_live_taxonomy_slug = $bktsk_yt_live_options['taxonomy_slug'];

	if ( empty( $bktsk_yt_live_taxonomy_slug ) ) {
		$bktsk_yt_live_taxonomy_slug = 'live_category';
	}

	register_taxonomy(
		'bktsk-yt-live-taxonomy',
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
			'rewrite'           => array(
				'slug' => $bktsk_yt_live_taxonomy_slug,
			),
		)
	);
}

add_action( 'init', 'bktsk_yt_live_add_taxonomy' );
