<?php

// add url route for ics

add_filter( 'query_vars', 'bktsk_yt_scheduler_ics_query_vars' );
add_action( 'init', 'bktsk_yt_scheduler_ics_urls' );

function bktsk_yt_scheduler_ics_query_vars( $vars ) {
	$vars[] = 'bktsk_yt_live';
	return $vars;
}

function bktsk_yt_scheduler_ics_urls() {
	add_rewrite_rule(
		'^bktsk_yt_live/?',
		'index.php?bktsk_yt_live=true',
		'top'
	);
}

// add response for ics

add_action( 'parse_request', 'bktsk_yt_scheduler_ics_requests' );

function bktsk_yt_scheduler_ics_requests( $wp ) {
	$valid_actions = array( true );

	if (
	! empty( $wp->query_vars['bktsk_yt_live'] ) &&
	in_array( $wp->query_vars['bktsk_yt_live'], $valid_actions )
	) {

		header( 'Content-Type: text/calendar; charset=UTF-8' );
		$bktsk_yt_live_calendar = <<<EOF
BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:-//BKTSK YouTube Live Scheduler for WordPress//Manually//EN
VERSION:2.0
X-WR-CALNAME:配信カレンダー
X-WR-CALDESC:配信カレンダーテスト
EOF;

		$bktsk_yt_live_calendar .= "\n" . bktsk_yt_live_make_events_ics();

		$bktsk_yt_live_calendar .= <<<EOF

END:VCALENDAR
EOF;

		$bktsk_tmp = preg_replace( "/\r\n|\r|\n/", "\r\n", $bktsk_yt_live_calendar );
		echo preg_replace( "/\r\n\r\n/", "\r\n", $bktsk_tmp );
		exit();
	}

}
