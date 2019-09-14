<?php
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

add_action( 'parse_request', 'bktsk_yt_scheduler_ics_requests' );

function bktsk_yt_scheduler_ics_requests( $wp ) {
	$valid_actions = array( true );

	if (
	! empty( $wp->query_vars['bktsk_yt_live'] ) &&
	in_array( $wp->query_vars['bktsk_yt_live'], $valid_actions )
	) {

		header( 'Content-Type: text/calendar; charset=UTF-8' );
		?>
BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:-//BKTSK YouTube Live Scheduler for WordPress//Manually//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Japan
BEGIN:STANDARD
DTSTART:19390101T000000
TZOFFSETFROM:+0900
TZOFFSETTO:+0900
TZNAME:JST
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:com.bktsk.sasapiyogames.1
DTSTAMP:20190915T000000Z
SUMMARY:サーモンラン
DESCRIPTION:テスト用
DTSTART;VALUE=DATE:20190915;
DTEND;VALUE=DATE:20190916;
TRANSP:TRANSPARENT
PRIORITY:0
CLASS:PUBLIC
END:VEVENT
END:VCALENDAR
		<?php
		exit;
	}

}
