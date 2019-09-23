<?php
class BktskYtSchedulerShortcode {

	private $weekdays;
	private $style_url;

	private $this_year;
	private $this_month;
	private $now;

	private $options;
	private $week_start;

	private $wp_timezone;

	private $cal_span_start;
	private $cal_span_end;

	private $query;


	public function __construct() {
		$this->wp_timezone = get_option( 'timezone_string' );
		$this->now         = new DateTime( 'now', new DateTimeZone( $this->wp_timezone ) );
		$this->style_url   = plugins_url( '../style/', __FILE__ );
		$this->this_year   = $this->now->format( 'Y' );
		$this->this_month  = $this->now->format( 'n' );
		$this->options     = get_option( 'bktsk_yt_scheduler_options' );
		$this->week_start  = $this->options['wod_start'];

		$this->weekdays = array(
			__( 'Sun', 'bktsk-live-scheduler' ),
			__( 'Mon', 'bktsk-live-scheduler' ),
			__( 'Tue', 'bktsk-live-scheduler' ),
			__( 'Wed', 'bktsk-live-scheduler' ),
			__( 'Thu', 'bktsk-live-scheduler' ),
			__( 'Fri', 'bktsk-live-scheduler' ),
			__( 'Sat', 'bktsk-live-scheduler' ),
			__( 'Sun', 'bktsk-live-scheduler' ),
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'calendar_init' ) );

		add_shortcode( 'bktsk-live-calendar', array( $this, 'show_calendar' ) );
		add_shortcode( 'bktsk-live-calendar-test', array( $this, 'show_calendar' ) );
	}

	public function calendar_init() {
		global $post;

		//	if ( has_shortcode( $post->post_content, 'bktsk-live-calendar' ) ) {
			wp_register_style( 'bktsk-live-calendar-css', $this->style_url . 'calendar/style.min.css' );
			wp_enqueue_style( 'bktsk-live-calendar-css' );
		//	}
	}

	private function show_weekdays( $start = 0 ) {
		?>
		<div class="row weekday">
			<?php
			for ( $i = $start; $i < $start + 7; $i++ ) {
				print( '<div class="day wday-' . $i . '"><div class="day-week">' . $this->weekdays[ $i ] . '</div></div>' );
			}
			?>
		</div>
		<?php
	}

	public function show_calendar() {
		if ( ! is_admin() ) {

			self::get_calendar_span();
			self::get_lives_between();
			ob_start();
			print( '<div class="bktsk-live-calendar">' );
			self::show_weekdays( $this->week_start );
			self::show_dates();
			print( '</div>' );

			$response = ob_get_contents();
			ob_get_clean();

			return $response;
		}
	}

	private function get_calendar_span() {
		$this->cal_span_start = self::get_calendar_start();
		$this->cal_span_end   = self::get_calendar_end();
	}

	private function get_calendar_start() {
		$timezone         = new DateTimeZone( $this->wp_timezone );
		$month            = sprintf( '%02d', $this->this_month );
		$this_month_start = new DateTime( 'first day of ' . $this->this_year . '-' . $month, $timezone );
		$last_month_end   = new DateTime( 'last day of ' . $this->this_year . '-' . $month, $timezone );

		$last_month_end->modify( 'last day of last months' );

		if ( 0 == $this->week_start ) {
			$end_weekday = $last_month_end->format( 'w' );
			if ( 6 == $end_weekday ) {
				return $this_month_start;
			}
		} else {
			$end_weekday = $last_month_end->format( 'N' );
			if ( 7 == $end_weekday ) {
				return $this_month_start;
			}
		}

		$diff = $end_weekday - $this->week_start;
		$last_month_end->modify( '-' . $diff . ' days' );

		return $last_month_end;
	}

	private function get_calendar_end() {
		$timezone         = new DateTimeZone( $this->wp_timezone );
		$month            = sprintf( '%02d', $this->this_month );
		$this_month_end   = new DateTime( 'last day of ' . $this->this_year . '-' . $month, $timezone );
		$next_month_start = new DateTime( 'first day of ' . $this->this_year . '-' . $month, $timezone );

		$next_month_start->modify( 'first day of next months' );

		if ( 0 == $this->week_start ) {
			$end_weekday = $this_month_end->format( 'w' );
			if ( 6 == $end_weekday ) {
				return $this_month_end;
			}
		} else {
			$end_weekday = $this_month_end->format( 'N' );
			if ( 7 == $end_weekday ) {
				return $this_month_end;
			}
		}

		$diff = 6 + $this->week_start - $end_weekday - 1;
		$next_month_start->modify( '+' . $diff . ' days' );

		return $next_month_start;
	}

	private function get_lives_between() {
		$start = $this->cal_span_start;
		$end   = clone $this->cal_span_end;
		$end->modify( '+1 day' );

		$args = array(
			'post_type'      => 'bktskytlive',
			'order'          => 'ASC',
			'order_by'       => 'meta_value',
			'meta_key'       => 'bktsk_yt_live_frontpage_start',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'bktsk_yt_live_frontpage_start',
					'value'   => $start->format( 'Y-m-d H:i:s' ),
					'compare' => '>=',
				),
				array(
					'key'     => 'bktsk_yt_live_frontpage_start',
					'value'   => $end->format( 'Y-m-d H:i:s' ),
					'compare' => '<',
				),
			),
			'posts_per_page' => -1,
		);

		$the_query   = new WP_Query( $args );
		$this->query = clone $the_query;
		wp_reset_postdata();
	}

	private function show_dates() {
		$proc = clone $this->cal_span_start;
		$end  = clone $this->cal_span_end;

		while ( $proc <= $end ) {
			self::make_date_block( $proc );
			$proc->modify( '+1 day' );
		}
	}

	private function make_date_block( $proc ) {
		$day_class = 'this-month';

		if ( 0 == $this->week_start ) {
			$wod = $proc->format( 'w' );
			if ( 0 == $wod ) {
				print( '<div class="row date">' );
			}
		} else {
			$wod = $proc->format( 'N' );
			if ( 1 == $wod ) {
				print( '<div class="row date">' );
			}
		}

		if ( $this->this_month > $proc->format( 'n' ) ) {
			$day_class = 'last-month';
		} elseif ( $this->this_month < $proc->format( 'n' ) ) {
			$day_class = 'next-month';
		}

		if ( $this->now->format( 'Y-m-d' ) === $proc->format( 'Y-m-d' ) ) {
			$day_class .= ' today';
		} elseif ( $this->now > $proc ) {
			$day_class .= ' past';
		}

		print( '<div class="day ' . $day_class . ' wday-' . $proc->format( 'w' ) . '">' );
		print( '<div class="date-num">' . $proc->format( 'd' ) . '</div>' );

		self::make_schedule_block( $proc );

		print( '</div>' );

		if ( 0 == $this->week_start ) {
			$wod = $proc->format( 'w' );
			if ( 6 == $wod ) {
				print( '</div>' );
			}
		} else {
			$wod = $proc->format( 'N' );
			if ( 7 == $wod ) {
				print( '</div>' );
			}
		}
	}

	private function make_schedule_block( $proc ) {
		$timezone = new DateTimeZone( $this->wp_timezone );
		$query    = clone $this->query;
		$response = '';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$end = clone $proc;
				$end->modify( '+1 day' );

				$postid = get_the_ID();

				$strtime = get_post_meta( $postid, 'bktsk_yt_live_frontpage_start', true );
				$time    = new DateTime( $strtime, new DateTimeZone( 'UTC' ) );
				$time->setTimezone( $timezone );

				if ( $time >= $proc && $time < $end ) {
					$live_type      = get_post_meta( $postid, 'bktsk_yt_live_type', true );
					$time_text      = '';
					$canceled_class = '';

					if ( false !== strpos( $live_type, 'canceled' ) ) {
						$canceled_class = ' canceled';
					}

					$terms      = get_the_terms( $postid, 'bktsk-yt-live-taxonomy' );
					$term_class = '';
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach ( $terms as $term ) {
							$term_class .= ' ' . $term->slug;
						}
					}

					switch ( $live_type ) {
						case 'live_schedule':
						case 'canceled_live_schedule':
							$time = new DateTime( get_post_meta( $postid, 'bktsk_yt_live_start', true ), new DateTimeZone( 'UTC' ) );
							$time->setTimezone( $timezone );
							$time_text = $time->format( 'H:i' );
							break;

						case 'all_day_live_schedule':
						case 'canceled_all_day_live_schedule':
							$time_text = __( 'All day', 'bktsk-live-scheduler' );
							break;

						case 'day_off':
							$time_text   = __( 'All day', 'bktsk-live-scheduler' );
							$term_class .= ' day-off';
							break;
					}

					$response .= self::make_each_live_block( $postid, $term_class, $canceled_class, $time_text );
				}
			}
		}

		if ( '' != $response ) {
			echo $response;
		} else {
			print( '<div class="live none">' );
			_e( 'None', 'bktsk-live-scheduler' );
			print( '</div>' );
		}
	}

	private function make_each_live_block( $postid, $term_class, $canceled_class, $time_text ) {
		$response .= '<a href="' . get_permalink( $postid ) . '">'
		. '<div class="live' . $term_class . $canceled_class . '">'
		. '<div class="time">' . $time_text . '</div>'
		. '<div class="title">' . get_the_title( $postid ) . '</div>'
		. '</div>'
		. '</a>';

		return $response;
	}
}

$bktsk_live_calendar = new BktskYtSchedulerShortcode();
