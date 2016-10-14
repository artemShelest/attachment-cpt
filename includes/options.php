<?php

abstract class ACPT_Options {

	const wp_media_settings = 'media';
	const settings_section = 'acpt_settings_section';

	const options_prefix = 'acpt_';
	// option_post_types is just informational
	const option_post_types = self::options_prefix.'post_types';
	const option_show_demo = self::options_prefix.'show_demo';

	const template_prefix = 'options-';
	const template_section = self::template_prefix.'section';

	const type_textarea = 'textarea';
	const type_checkbox = 'checkbox';

	const name_types = array(
		self::option_post_types => self::type_textarea,
		self::option_show_demo => self::type_checkbox,
	);

	const name_titles = array(
		self::option_post_types => 'Active post types',
		self::option_show_demo => 'Show demo plugin',
	);

	const name_infotips = array(
		self::option_post_types => 'List of post types registered with ACPT plugin',
		self::option_show_demo => 'List of post types registered with ACPT plugin',
	);

	private static $_registered_types;

	static function init() {

		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

	}


	static function delete_options() {
		delete_option(static::option_show_demo);
	}
	

	static function set_registered_types(&$types) {
		static::$_registered_types = $types;
	}

	static function register_settings() {

		add_settings_section(
			static::settings_section,
			__('Attachment CPT Settings', Attachment_CPT::text_domain),
			function() { Attachment_CPT::template(static::template_section); },
			static::wp_media_settings
		);
		static::add_field(static::option_show_demo);
		add_settings_field(
			static::option_post_types,
			__(static::name_titles[static::option_post_types], Attachment_CPT::text_domain),
			array(__CLASS__, 'render_type_info'),
			static::wp_media_settings,
			static::settings_section,
			static::option_post_types
		);
	}


	protected static function add_field($name) {

		add_settings_field(
			$name,
			__(static::name_titles[$name], Attachment_CPT::text_domain),
			array(__CLASS__, 'render_field'),
			static::wp_media_settings,
			static::settings_section,
			$name
		);

		register_setting( static::wp_media_settings,
			$name,
			function ($input) use ($name) { return static::sanitize($name, $input); } );

	}


	static function render_field($name) {
		$infotip = __(static::name_infotips[$name], Attachment_CPT::text_domain);
		$value = get_option($name);
		Attachment_CPT::template((static::template_prefix).static::name_types[$name], compact('name', 'infotip', 'value'));
	}


	static function render_type_info() {
		$name = static::option_post_types;
		$infotip = __(static::name_infotips[$name], Attachment_CPT::text_domain);
		$value = static::$_registered_types;
		$readonly = true;
		Attachment_CPT::template(static::template_prefix.static::type_textarea, compact('name', 'infotip', 'value', 'readonly'));
	}


	static function sanitize( $name, $input ) {

		switch($name) {
			case static::option_post_types:
				// Ignore edit
				return get_option($name);//array_map('trim', preg_split('/[\s,]+/', $input));
			case static::option_show_demo:
				return boolval($input);
		}

		wp_die(sprintf(__('Failed to save option: %s'), $name));

	}


}