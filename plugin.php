<?php
/*
Plugin Name: BKTSK YouTube Scheduler
Plugin URI:
Description: Show YouTube Schedule in WordPress
Version: 0.0.1
Author: SASAGAWA Kiyoshi
Author URI: https://kent-and-co.com
License: GPL v2 or later
Text Domain: BktskYtScheduler
Domain Path: /languages

Copyright 2019 SASAGAWA Kiyoshi (email : sasagawa@kent-and-co.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

load_plugin_textdomain(
	'BktskYtScheduler',
	false,
	plugin_basename( dirname( __FILE__ ) ) . '/languages'
);

require_once dirname( __FILE__ ) . '/lib/add-post-type.php'; // for Post Type
require_once dirname( __FILE__ ) . '/lib/make-ics.php'; // for ics response
require_once dirname( __FILE__ ) . '/lib/admin-menu.php'; // for Admin Menus
