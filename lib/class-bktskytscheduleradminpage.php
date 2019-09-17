<?php
class BktskYtSchedulerAdminPage {

	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			__( 'YouTube Live Scheduler Settings', 'BktskYtScheduler' ),
			__( 'YT Live Settings', 'BktskYtScheduler' ),
			'administrator',
			'bktskytscheduleradmin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'bktsk_yt_scheduler_options' );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'YouTube Live Scheduler Settings', 'BktskYtScheduler' ); ?></h2>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'bktsk-yt-scheduler-group' );
				do_settings_sections( 'bktsk-yt-scheduler-admin' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			'bktsk-yt-scheduler-group', // Option group
			'bktsk_yt_scheduler_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		// add sction for slugs
		add_settings_section(
			'bktsk-yt-scheduler-slugs', // ID
			__( 'Slugs', 'BktskYtScheduler' ), // Title
			array( $this, 'print_slugs_section_info' ), // Callback
			'bktsk-yt-scheduler-admin' // Page
		);

		// add field for post type slug
		add_settings_field(
			'posttype_slug', // ID
			'Live post slug', // Title
			array( $this, 'posttype_slug_callback' ), // Callback
			'bktsk-yt-scheduler-admin', // Page
			'bktsk-yt-scheduler-slugs' // Section
		);

		// add field for taxonomy slug
		add_settings_field(
			'taxonomy_slug',
			'Live category slug',
			array( $this, 'taxonomy_slug_callback' ),
			'bktsk-yt-scheduler-admin',
			'bktsk-yt-scheduler-slugs'
		);

		// add field for iCalendar URL slug
		add_settings_field(
			'ical_slug',
			'Live iCalendar slug',
			array( $this, 'ical_slug_callback' ),
			'bktsk-yt-scheduler-admin',
			'bktsk-yt-scheduler-slugs'
		);

		// add section for iCalendar title/description
		add_settings_section(
			'bktsk-yt-scheduler-ical-info', // ID
			__( 'iCalendar Info', 'BktskYtScheduler' ), // Title
			array( $this, 'print_icalinfo_section_info' ), // Callback
			'bktsk-yt-scheduler-admin' // Page
		);

		// add field for name of iCalendar
		add_settings_field(
			'ical_title', // ID
			'iCalendar title', // Title
			array( $this, 'title_info_callback' ), // Callback
			'bktsk-yt-scheduler-admin', // Page
			'bktsk-yt-scheduler-ical-info' // Section
		);

		// add field for description of iCalendar
		add_settings_field(
			'ical_desc',
			'iCalendar Description',
			array( $this, 'desc_info_callback' ),
			'bktsk-yt-scheduler-admin',
			'bktsk-yt-scheduler-ical-info'
		);

		// add section for iCalendar tags
		add_settings_section(
			'bktsk-yt-scheduler-ical-tags', // ID
			__( 'iCalendar Tags', 'BktskYtScheduler' ), // Title
			array( $this, 'print_icaltags_section_info' ), // Callback
			'bktsk-yt-scheduler-admin' // Page
		);

		// add field for tag of canceled events
		add_settings_field(
			'canceled_tag', // ID
			'Canceled', // Title
			array( $this, 'canceled_tag_callback' ), // Callback
			'bktsk-yt-scheduler-admin', // Page
			'bktsk-yt-scheduler-ical-tags' // Section
		);

		// add field for tag of time not fixed events
		add_settings_field(
			'notfixed_tag',
			'Time not fixed',
			array( $this, 'notfixed_tag_callback' ),
			'bktsk-yt-scheduler-admin',
			'bktsk-yt-scheduler-ical-tags'
		);

		// add field for tag of day off events
		add_settings_field(
			'dayoff_tag',
			'Day off',
			array( $this, 'dayoff_tag_callback' ),
			'bktsk-yt-scheduler-admin',
			'bktsk-yt-scheduler-ical-tags'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['posttype_slug'] ) ) {
			$new_input['posttype_slug'] = urlencode( $input['posttype_slug'] );
		}

		if ( isset( $input['taxonomy_slug'] ) ) {
			$new_input['taxonomy_slug'] = urlencode( $input['taxonomy_slug'] );
		}

		if ( isset( $input['ical_slug'] ) ) {
			$new_input['ical_slug'] = urlencode( $input['ical_slug'] );
		}

		if ( isset( $input['ical_title'] ) ) {
			$new_input['ical_title'] = $input['ical_title'];
		}

		if ( isset( $input['ical_desc'] ) ) {
			$new_input['ical_desc'] = $input['ical_desc'];
		}

		if ( isset( $input['canceled_tag'] ) ) {
			$new_input['canceled_tag'] = $input['canceled_tag'];
		}

		if ( isset( $input['notfixed_tag'] ) ) {
			$new_input['notfixed_tag'] = $input['notfixed_tag'];
		}

		if ( isset( $input['dayoff_tag'] ) ) {
			$new_input['dayoff_tag'] = $input['dayoff_tag'];
		}
		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_slugs_section_info() {
		_e( 'Fields of this section will be used for URL slugs. All fields will be urlencoded.', 'BktskYtScheduler' );
		echo '<br>';
		_e( 'After changing this section, permalink update is strongly recommended', 'BktskYtScheduler' );
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function posttype_slug_callback() {
		printf(
			'<input type="text" id="posttype_slug" name="bktsk_yt_scheduler_options[posttype_slug]" value="%s" placeholder="live_schedule">',
			isset( $this->options['posttype_slug'] ) ? esc_attr( $this->options['posttype_slug'] ) : ''
		);
		echo '<div class="bktsk-yt-notes">';
		_e( 'This will be used for post type slug of the lives. "live_schedule" is the default. (When this field is empty.)', 'BktskYtScheduler' );
		echo '</div>';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function taxonomy_slug_callback() {
		printf(
			'<input type="text" id="taxonomy_slug" name="bktsk_yt_scheduler_options[taxonomy_slug]" value="%s" placeholder="live_category">',
			isset( $this->options['taxonomy_slug'] ) ? esc_attr( $this->options['taxonomy_slug'] ) : ''
		);
		echo '<div class="bktsk-yt-notes">';
		_e( 'This will be used for taxonomy slug of the lives. "live_category" is the default. (When this field is empty.)', 'BktskYtScheduler' );
		echo '</div>';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function ical_slug_callback() {
		printf(
			'<input type="text" id="ical_slug" name="bktsk_yt_scheduler_options[ical_slug]" value="%s" placeholder="bktsk_yt_live">',
			isset( $this->options['ical_slug'] ) ? esc_attr( $this->options['ical_slug'] ) : ''
		);
		echo '<div class="bktsk-yt-notes">';
		_e( 'This will be used for iCalendar URL slug. "bktsk_yt_live" is the default. (When this field is empty.)', 'BktskYtScheduler' );
		echo '</div>';
	}

	/**
	 * Print the Section text
	 */
	public function print_icalinfo_section_info() {
		_e( 'Fields of this section will be used on the iCalendar.', 'BktskYtScheduler' );
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function title_info_callback() {
		printf(
			'<input type="text" id="ical_title" name="bktsk_yt_scheduler_options[ical_title]" value="%s">',
			isset( $this->options['ical_title'] ) ? esc_attr( $this->options['ical_title'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function desc_info_callback() {
		printf(
			'<input type="text" id="ical_desc" name="bktsk_yt_scheduler_options[ical_desc]" value="%s">',
			isset( $this->options['ical_desc'] ) ? esc_attr( $this->options['ical_desc'] ) : ''
		);
	}

	/**
	 * Print the Section text
	 */
	public function print_icaltags_section_info() {
		_e( 'Fields of this section will be used just before title (VEVENT/SUMMARY) on the iCalendar.', 'BktskYtScheduler' );
		echo '<br>';
		_e( 'When none given, nothing will be added.', 'BktskYtScheduler' );
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function canceled_tag_callback() {
		printf(
			'<input type="text" id="canceled_tag" name="bktsk_yt_scheduler_options[canceled_tag]" value="%s">',
			isset( $this->options['canceled_tag'] ) ? esc_attr( $this->options['canceled_tag'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function notfixed_tag_callback() {
		printf(
			'<input type="text" id="notfixed_tag" name="bktsk_yt_scheduler_options[notfixed_tag]" value="%s">',
			isset( $this->options['notfixed_tag'] ) ? esc_attr( $this->options['notfixed_tag'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function dayoff_tag_callback() {
		printf(
			'<input type="text" id="dayoff_tag" name="bktsk_yt_scheduler_options[dayoff_tag]" value="%s">',
			isset( $this->options['dayoff_tag'] ) ? esc_attr( $this->options['dayoff_tag'] ) : ''
		);
	}
}

if ( is_admin() ) {
	$bktsk_yt_scheduler_settings_page = new BktskYtSchedulerAdminPage();
}