<?php
/**
Plugin Name: Attachment CPT
Description: WordPress Attachment Custom Post Types allows to have custom post types with a behaviour of an "attachment" post type.
Author:      Artem Shelest
Version:     0.1
Plugin URI:  https://github.com/artemShelest/attachment-cpt
License:     GPLv2 or Later
Domain Path: /languages
Text Domain: attachment-cpt
 */

/* Attachment CPT
 *
 * Copyright (C) 2016 Artem Shelest (artem.e.shelest@gmail.com | https://github.com/artemShelest)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright 2016
 * @license GPL v3
 * @version 0.1
 * @package attachment-cpt
 * @author Artem Shelest <artem.e.shelest@gmail.com>
 */

require_once dirname( __FILE__ ) . '/includes/options.php';
require_once dirname( __FILE__ ) . '/includes/wp-copy.php';


abstract class Attachment_CPT {

	const text_domain = 'attachment-cpt';
	const version = '0.1';
	protected static $_acpt_types = [];

	static function init() {

		add_action( 'init', array( __CLASS__, 'action_init' ) );
		add_action( 'init', array( __CLASS__, 'init_translations'), 5  );
		add_filter( 'image_downsize', array('ACPT_WP', 'image_downsize'), 10, 3);
		add_action( 'template_redirect', array( __CLASS__, 'choose_template' ) );
		ACPT_Options::init();
	}


	static function init_translations() {
		load_plugin_textdomain( Attachment_CPT::text_domain,
			false,
			plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}


	static function action_init() {

		if ( !is_admin() )
			return;
		require_once dirname( __FILE__ ) . '/includes/admin.php';
		ACPT_Admin::init();
	}


	static function is_attachment_type($type) {
		return in_array($type, static::$_acpt_types);
	}


	static function template( $template, $args = array() ) {

		if ( !$template )
			return false;

		extract( $args );
		$path = dirname( __FILE__ ) . '/templates/'.$template.'.php';

		include $path;

	}


	static function choose_template() {

		$p = get_queried_object_id();
		if (!Attachment_CPT::is_attachment_type(get_post_type($p))) {
			return;
		}
		remove_filter('the_content', 'prepend_attachment');
		if ( $template = apply_filters( 'template_include', get_attachment_template() ) ) {
			include( $template );
			exit();
		}
	}


	static function add_acpt_type($type) {
		if(!static::is_attachment_type($type)) {
			array_push(static::$_acpt_types, $type);
			ACPT_Options::set_registered_types(static::$_acpt_types);
		}
	}

}

register_uninstall_hook(__FILE__, 'pluginprefix_function_to_run');

Attachment_CPT::init();
if(get_option(ACPT_Options::option_show_demo)) {
	require_once dirname( __FILE__ ) . '/acpt-demo.php';
}