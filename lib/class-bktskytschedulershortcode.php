<?php
class BktskYtSchedulerShortcode {

	private $weekdays;
	private $style_url;

	private $this_year;
	private $this_month;

	private $options;
	private $week_start;

	private $wp_timezone;


	public function __construct() {
		$this->wp_timezone = get_option( 'timezone_string' );
		$now               = new DateTime( 'now', new DateTimeZone( $this->wp_timezone ) );
		$this->style_url   = plugins_url( '../style/', __FILE__ );
		$this->this_year   = $now->format( 'Y' );
		$this->this_month  = $now->format( 'n' );
		$this->options     = get_option( 'bktsk_yt_scheduler_options' );
		$this->week_start  = $this->options['wod_start'];

		add_action( 'wp_enqueue_scripts', array( $this, 'calendar_init' ) );

		add_shortcode( 'bktsk_live_calendar', array( $this, 'show_calendar' ) );
	}

	public function calendar_init() {
		global $post;

		if ( has_shortcode( $post->post_content, 'bktsk_live_calendar' ) ) {
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

			wp_register_style( 'bktsk-live-calendar', $this->style_url . 'calendar/style.min.css' );
			wp_enqueue_style( 'bktsk-live-calendar' );
		}
	}

	public function show_calendar() {
		if ( ! is_admin() ) {
			echo '<div class="bktsk-live-calendar">';
			self::show_weekdays( $this->week_start );
			self::get_lmonth_dates();
			self::get_month_dates();
			self::get_nmonth_dates();
			echo '</div>';
		}
	}

	private function show_weekdays( $start = 0 ) {
		?>
		<div class="row weekday">
			<?php
			for ( $i = $start; $i < $start + 7; $i++ ) {
				echo '<div class="day wday-' . $i . '"><div class="day-week">' . $this->weekdays[ $i ] . '</div></div>';
			}
			?>
		</div>
		<?php
	}

	private function get_month_dates() {
		$month      = sprintf( '%02d', $this->this_month );
		$start_date = new DateTime( 'first day of ' . $this->this_year . '-' . $month );
		$end_date   = new DateTime( 'last day of ' . $this->this_year . '-' . $month );
		$interval   = $start_date->diff( $end_date );
		$diff       = intval( $interval->format( '%a' ) );

		$echo_date = clone $start_date;
		for ( $i = 0; $i <= $diff; $i++ ) {
			$flag = true;

			echo '<div class="day this-month wday-' . $echo_date->format( 'w' ) . '">';
			echo '<div class="date-num">' . $echo_date->format( 'd' ) . '</div>';
			self::get_the_live_schedule( $echo_date->format( 'Y-m-d' ) );
			echo '</div>';

			if ( 0 == $this->week_start ) {
				$echo_weekday = $echo_date->format( 'w' );
				if ( 6 == $echo_weekday ) {
					$flag = false;
				}
			} else {
				$echo_weekday = $echo_date->format( 'N' );
				if ( 7 == $echo_weekday ) {
					$flag = false;
				}
			}

			if ( ! $flag ) {
				echo '</div><div class="row date">';
			}

			$echo_date->modify( '+1 day' );
		}
	}

	private function get_lmonth_dates() {
		echo '<div class="row date">';

		$month      = sprintf( '%02d', $this->this_month );
		$start_date = new DateTime( 'first day of ' . $this->this_year . '-' . $month );
		$end_date   = new DateTime( 'last day of ' . $this->this_year . '-' . $month );

		$start_date->modify( 'first day of last months' );
		$end_date->modify( 'last day of last months' );

		$flag = true;

		if ( 0 == $this->week_start ) {
			$end_weekday = $end_date->format( 'w' );
			if ( 6 == $end_weekday ) {
				$flag = false;
			}
		} else {
			$end_weekday = $end_date->format( 'N' );
			if ( 7 == $end_weekday ) {
				$flag = false;
			}
		}

		if ( $flag ) {
			$echo_date = clone $end_date;
			$diff      = $end_weekday - $this->week_start;
			$echo_date->modify( '-' . $diff . ' days' );

			$flag2 = false;
			if ( 0 == $this->week_start ) {
				$echo_weekday = $echo_date->format( 'w' );
				if ( 6 == $echo_weekday ) {
					$flag2 = true;
				}
			} else {
				$echo_weekday = $echo_date->format( 'N' );
				if ( 7 == $echo_weekday ) {
					$flag2 = true;
				}
			}

			for ( $i = 0; $i <= $diff; $i++ ) {
				echo '<div class="day last-month wday-' . $echo_date->format( 'w' ) . '">';
				echo '<div class="date-num">' . $echo_date->format( 'd' ) . '</div>';
				self::get_the_live_schedule( $echo_date->format( 'Y-m-d' ) );
				echo '</div>';

				if ( $flag2 ) {
					echo '</div><div class="row date">';
				}

				$echo_date->modify( '+1 day' );
			}
		}
	}

	private function get_nmonth_dates() {
		$month      = sprintf( '%02d', $this->this_month );
		$start_date = new DateTime( 'first day of ' . $this->this_year . '-' . $month );
		$end_date   = new DateTime( 'last day of ' . $this->this_year . '-' . $month );

		$start_date->modify( 'first day of next months' );
		$end_date->modify( 'last day of next months' );

		$flag = true;

		if ( 0 == $this->week_start ) {
			$start_weekday = $start_date->format( 'w' );
			if ( 0 == $start_weekday ) {
				$flag = false;
			}
		} else {
			$start_weekday = $start_date->format( 'N' );
			if ( 1 == $start_weekday ) {
				$flag = false;
			}
		}

		if ( $flag ) {
			$echo_date = clone $start_date;
			$diff      = 6 + $this->week_start - $start_weekday;

			for ( $i = 0; $i <= $diff; $i++ ) {
				echo '<div class="day next-month wday-' . $echo_date->format( 'w' ) . '">';
				echo '<div class="date-num">' . $echo_date->format( 'd' ) . '</div>';
				self::get_the_live_schedule( $echo_date->format( 'Y-m-d' ) );
				echo '</div>';

				$flag2 = false;
				if ( 0 == $this->week_start ) {
					$echo_weekday = $echo_date->format( 'w' );
					if ( 6 == $echo_weekday ) {
						$flag2 = true;
					}
				} else {
					$echo_weekday = $echo_date->format( 'N' );
					if ( 7 == $echo_weekday ) {
						$flag2 = true;
					}
				}

				$echo_date->modify( '+1 day' );
			}
		}

		echo '</div>';
	}

	private function get_the_live_schedule( $date ) {
		$timezone    = new DateTimeZone( $this->wp_timezone );
		$today_start = new DateTime( $date, $timezone );
		$today_end   = new DateTime( $date, $timezone );
		$today_end->modify( '+1 day' );

		$args = array(
			'post_type'  => 'bktskytlive',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'relation' => 'AND',
					array(
						'key'     => 'bktsk_yt_live_frontpage_start',
						'value'   => $today_start->format( 'Y-m-d H:i:s' ),
						'compare' => '>=',
					),
					array(
						'key'     => 'bktsk_yt_live_frontpage_start',
						'value'   => $today_end->format( 'Y-m-d H:i:s' ),
						'compare' => '<',
					),
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'bktsk_yt_live_frontpage_start',
						'value'   => $today_start->format( 'Y-m-d H:i:s' ),
						'compare' => '<',
					),
					array(
						'key'     => 'bktsk_yt_live_frontpage_due',
						'value'   => $today_end->format( 'Y-m-d H:i:s' ),
						'compare' => '>',
					),
				),
			),
		);

		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$postid         = get_the_ID();
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

				echo '<a href="' . get_permalink( $postid ) . '">';
				echo '<div class="live' . $term_class . $canceled_class . '">';
				echo '<div class="time">' . $time_text . '</div>';
				echo '<div class="title">' . get_the_title() . '</div>';
				echo '</div>';
				echo '</a>';
			}
		} else {
			echo '<div class="live none">';
			_e( 'None', 'bktsk-live-scheduler' );
			echo '</div>';
		}
	}
}

$bktsk_live_calendar = new BktskYtSchedulerShortcode();
