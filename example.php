<?php
/**
 * Plugin Name: Attachment CPT Library Example
 * Description: Wordpress Attachment Custom Post Types allows to have custom post types with a behaviour of an "attachment" post type.
 * Author:      Artem Shelest
 * Version:     0.2
 * Plugin URI:  https://github.com/artemShelest/attachment-cpt
 * License:     MIT
 */

namespace acpt_plugin_ns {

	if (!defined('ABSPATH')) {
		exit;
	}

	use acpt_plugin_ns\acpt_lib\ACPT_Post_Type;

	include_once __DIR__ . DIRECTORY_SEPARATOR . 'acpt-lib' .
		DIRECTORY_SEPARATOR . 'acpt-post-type.php';
	define('ACPT_PLUGIN_CPT', 'acpt_image');
	define('ACPT_PLUGIN_OPTION_PREFIX', 'acpt_plugin_option_');

	abstract class ACPT_Demo {

		static function init () {
			add_action('init', array(__CLASS__, 'register_cpt'));
			register_deactivation_hook( __FILE__, array(__CLASS__, 'deactivate') );
			new ACPT_Post_Type(ACPT_PLUGIN_CPT);
		}

		// TODO: update rewrite cache
		static function register_cpt () {
			$labels = array(
				'name' => __('ACPT Images'),
				'singular_name' => __('ACPT Image'),
				'add_new' => __('Add New'),
				'add_new_item' => __('Add ACPT Image'),
				'edit_item' => __('Edit ACPT Image'),
				'new_item' => __('New ACPT Image'),
				'view_item' => __('View ACPT Image'),
				'search_items' => __('Search ACPT Images'),
				'not_found' => __('No ACPT images found'),
				'not_found_in_trash' => __('No ACPT images found in Trash'),
				'menu_name' => __('ACPT Images'),
			);

			$args = array(
				'labels' => $labels,
				'hierarchical' => false,
				'supports' => array('title', 'author', 'revisions'),
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => false,
				'publicly_queryable' => true,
				'exclude_from_search' => true,
				'has_archive' => false,
				'query_var' => true,
				'can_export' => true,
				'rewrite' => true,
				'map_meta_cap' => true,
				'menu_icon' => 'dashicons-album',
			);

			if (!get_option( ACPT_PLUGIN_OPTION_PREFIX . 'activated' )) {
				flush_rewrite_rules();
				update_option( ACPT_PLUGIN_OPTION_PREFIX . 'activated', true );
			}

			register_post_type(ACPT_PLUGIN_CPT, $args);
		}

		public static function deactivate() {
			update_option( ACPT_PLUGIN_OPTION_PREFIX . 'activated', false );
			flush_rewrite_rules();
		}
	}

	ACPT_Demo::init();
}
