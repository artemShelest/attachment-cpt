<?php
/*
 * Demo of an Attachment Custom Post Type
 * You can use this source file as a base for your plugin without changes to the code,
 * adjustment of constants is enough.
 */

// Include function (add_acpt_type) from attachment-cpt plugin
require_once dirname( dirname( __FILE__ )) . '/attachment-cpt/attachment-cpt.php';

if(!class_exists('ACPT_Demo')) {
	abstract class ACPT_Demo {

		const post_type = 'acpt-demo';
		const text_domain = 'acpt-demo';

		static function init() {
			add_action( 'init', array( __CLASS__, 'init_translations' ), 5 );
			add_action( 'init', array( __CLASS__, 'register_cpt' ) );
			/*
			 * NOTE: this condition is only needed for this demo inside an Attachment CPT plugin
			 * Normally you add action without this check
			 */
			if(!did_action('plugins_loaded')) {
				add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
			}
			else {
				static::plugins_loaded();
			}
		}


		static function plugins_loaded() {

			Attachment_CPT::add_acpt_type( static::post_type );
		}


		static function init_translations() {
			load_plugin_textdomain( static::text_domain,
				false,
				plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
		}


		static function register_cpt() {
			$labels = array(
				'name'               => __( 'Gallery Images', static::text_domain ),
				'singular_name'      => __( 'Gallery Image', static::text_domain ),
				'add_new'            => __( 'Add New', static::text_domain ),
				'add_new_item'       => __( 'Add Gallery Image', static::text_domain ),
				'edit_item'          => __( 'Edit Gallery Image', static::text_domain ),
				'new_item'           => __( 'New Gallery Image', static::text_domain ),
				'view_item'          => __( 'View Gallery Image', static::text_domain ),
				'search_items'       => __( 'Search Gallery Images', static::text_domain ),
				'not_found'          => __( 'No gallery images found', static::text_domain ),
				'not_found_in_trash' => __( 'No gallery images found in Trash', static::text_domain ),
				'menu_name'          => __( 'Gallery Images', static::text_domain ),
			);

			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'author', 'revisions' ),
				'public'              => true,
				'show_ui'             => true,
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => true,
				'map_meta_cap'        => true,
				'menu_icon'           => 'dashicons-album',
			);

			register_post_type( static::post_type, $args );
		}

	}

	ACPT_Demo::init();
}