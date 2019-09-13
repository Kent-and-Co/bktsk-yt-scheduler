<?php

// making post type and add

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
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	);

	register_post_type( 'bktskytlive', $args );
}

add_filter( 'use_block_editor_for_post', 'bktsk_yt_scheduler_block_disabler', 10, 2 );

function bktsk_yt_scheduler_block_disabler( $use_block_editor, $post ) {
	if ( 'bktskytlive' === $post->post_type ) {
		$use_block_editor = false;
	}
	return $use_block_editor;
}


// add meta_box for custom post type

add_action( 'admin_init', 'bktsk_yt_scheduler_add_meta_box' );
function bktsk_yt_scheduler_add_meta_box() {
	add_meta_box( 'bktsk_yt_scheduler_meta_box', __( 'Live Date/Time', 'BktskYtScheduler' ), 'bktsk_yt_scheduler_meta_html', 'bktskytlive', 'normal' );
}

function bktsk_yt_scheduler_meta_html() {
	global $post;
	$custom = get_post_custom( $post->ID );
	//メタキーがあったら
	if ( ! empty( $custom ) ) {
		$live_start = $custom['live_start'][0];
		$live_end   = $custom['live_end'][0];
		//開始時間
		$live_start_date = date( 'Y-m-d', strtotime( $live_start ) );
		$live_start_time = date( 'H:i', strtotime( $live_start ) );
		//終了時間
		$live_end_date = date( 'Y-m-d', strtotime( $live_end ) );
		$live_end_time = date( 'H:i', strtotime( $live_end ) );
	}
	echo '<input type="hidden" name="bktskytlive-live-nonce" id="bktskytlive-live-nonce" value="' . wp_create_nonce( 'bktskytlive-live-nonce' ) . '">';

	//入力フィールドの表示
	?>
	<style type="text/css">
	#live-time table th {
			text-align: left;
			font-weight: normal;
			padding-right: 10px;
	}
	</style>
	<script>
	jQuery(document).ready(function ($) {// initialize input widgets first
		// initialize input widgets first
		$('#jqueryExample .time').timepicker({
			'showDuration': true,
			'timeFormat': 'H:i'
		});

		$('#jqueryExample .date').datepicker({
			'dateFormat': 'yy-mm-dd',
			'autoclose': true
		});

		// initialize datepair
		$('#jqueryExample').datepair({
			parseDate: function (el) {
				var val = $(el).datepicker('getDate');
				if (!val) {
					return null;
				}
				var utc = new Date(val);
				return utc && new Date(utc.getTime() + (utc.getTimezoneOffset() * 60000));
			},
			updateDate: function (el, v) {
				$(el).datepicker('setDate', new Date(v.getTime() - (v.getTimezoneOffset() * 60000)));
			}
		});
	});
	</script>
	<div id="live-time">
	<table>
		<tr>
			<th><?php _e( 'Live Schedule', 'BktskYtScheduler' ); ?></th>
			<td id="live-sch">
				<p id="jqueryExample">
					<input type="text" class="date start" name="live_start_date" value="
					<?php
					if ( $live_start_date ) {
						echo $live_start_date;
					}
					?>
					">
					<input type="text" class="time start" name="live_start_time" value="
					<?php
					if ( $live_start_time ) {
						echo $live_start_time;
					}
					?>
					"> to
					<input type="text" class="date end" name="live_end_date" value="
					<?php
					if ( $live_end_date ) {
						echo $live_end_date;
					}
					?>
					">
					<input type="text" class="time end" name="live_end_time" value="
					<?php
					if ( $live_end_time ) {
						echo $live_end_time;
					}
					?>
					">
				</p>
			</td>
		</tr>
	</table>
	</div>
	<?php
}


// add JavaScript and CSS files
add_action( 'admin_enqueue_scripts', 'bktsk_yt_scheduler_load_jquery' );

function bktsk_yt_scheduler_load_jquery( $hook ) {
	if ( 'post-new.php' == $hook || 'post.php' == $hook ) {
		global $post;
		if ( 'bktskytlive' === $post->post_type ) {
			$js_url    = plugins_url( '../js/', __FILE__ );
			$style_url = plugins_url( '../style/', __FILE__ );
			wp_enqueue_script( 'datepair', $js_url . 'datepair.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-datepair', $js_url . 'jquery.datepair.js', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-ui', $js_url . 'jquery-ui.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'jquery-timepicker', $js_url . 'jquery.timepicker.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'moment', $js_url . 'moment.min.js', array( 'jquery' ) );

			wp_enqueue_style( 'jquery-ui', $style_url . 'jquery-ui.min.css' );
			wp_enqueue_style( 'jquery-ui-structure', $style_url . 'jquery-ui.structure.min.css' );
			wp_enqueue_style( 'jquery-ui-theme', $style_url . 'jquery-ui.theme.min.css' );
			wp_enqueue_style( 'jquery-timepicker', $style_url . 'jquery.timepicker.min.css' );
		}
	}
}
