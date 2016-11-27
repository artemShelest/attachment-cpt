<?php

namespace acpt_plugin_ns\acpt_lib {

	if (!defined ('ABSPATH')) {
		exit;
	}

	require_once __DIR__ . DIRECTORY_SEPARATOR . 'wp-copy.php';

	class ACPT_Post_Type {

		protected $_text_domain;
		protected $_post_type;

		public function __construct($post_type, $text_domain='') {
			$this->_text_domain = $text_domain;
			$this->_post_type = $post_type;
			add_action('init', array($this, 'init'));
			add_filter('image_downsize', array('acpt_plugin_ns\acpt_lib\WP_Copy', 'image_downsize'), 10, 3);
			add_action('template_redirect', array($this, 'choose_template'));
		}

		public function text_domain() {
			return $this->_text_domain;
		}
		
		public function init () {
			if (!is_admin()) {
				return;
			}
			$this->_admin_init();
		}

		protected function _admin_init() {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'admin.php';
			new Admin($this->post_type(), $this->text_domain());
		}
		
		public function choose_template () {
			$p = get_queried_object_id();
			if ($this->post_type() != get_post_type($p)) {
				return;
			}
			remove_filter('the_content', 'prepend_attachment');
			if ($template = apply_filters('template_include', get_attachment_template())) {
				include($template);
				exit();
			}
		}

		public function post_type () {
			return $this->_post_type;
		}
	}
}