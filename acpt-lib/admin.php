<?php

namespace acpt_plugin_ns\acpt_lib {
	if (!defined('ABSPATH')) {
		exit;
	}

	class Admin {
		const AJAX_FORM = 'acpt-media-upload-form';
		const AJAX_ACTION = 'acpt-upload-media';
		protected $_post_type;
		protected $_text_domain;

		public function __construct ($post_type, $text_domain) {
			$this->_post_type = $post_type;
			$this->_text_domain = $text_domain;
			add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
			add_action('pre_get_posts', array($this, 'adjust_post'));
			add_action('edit_form_after_title', array($this, 'implant_editor'));
			add_action('before_delete_post', array($this, 'before_delete_post'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action('save_post', array($this, 'save_post'));
			add_action('wp_ajax_' . Admin::AJAX_ACTION, array($this, 'handle_upload'));
		}

		public function enqueue_scripts ($page) {
			if (get_post_type() != $this->_post_type) {
				return;
			}

			if ($page == 'post-new.php') {
				wp_enqueue_script('plupload-all');
			}
			if ($page == 'post.php') {
				wp_enqueue_script('image-edit');
				wp_enqueue_style('imgareaselect');
			}
		}

		public function add_meta_boxes () {
			global $post;

			if ($post->post_type == $this->_post_type) {
				if ($post->post_status == 'auto-draft') {
					remove_post_type_support($post->post_type, 'title');
					remove_meta_box('submitdiv', $post->post_type, 'side');
					remove_meta_box('titlediv', $post->post_type, 'normal');
					remove_meta_box('authordiv', $post->post_type, 'normal');
					remove_meta_box('slugdiv', $post->post_type, 'normal');
				}
			}
		}

		public function adjust_post ($query) {
			$cs = get_current_screen();
			if (!$cs) {
				return;
			}
			if ($cs->post_type != $this->_post_type) {
				return;
			}
			$remove_pt_support = array('editor', 'revisions', 'thumbnail', 'excerpt');

			foreach ($remove_pt_support as $entry) {
				remove_post_type_support($cs->post_type, $entry);
			}
		}

		public function implant_editor ($post) {
			if ($post->post_type != $this->_post_type) {
				return;
			}
			$template = 'media-editor';
			if ($post->post_status == 'auto-draft') {
				$template = 'media-editor-new';
			}
			$text_domain = $this->_text_domain;
			include __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.php';
		}

		public function before_delete_post ($post_id) {
			if (get_post_type($post_id) != $this->_post_type) {
				return;
			}
			WP_Copy::delete_attached_files($post_id);
		}

		public function save_post ($post_id) {
			$post = get_post($post_id);
			if ($post->post_type != $this->_post_type) {
				return;
			}
			if (isset($_POST['_wp_attachment_image_alt'])) {
				update_post_meta($post->ID,
					'_wp_attachment_image_alt',
					wp_strip_all_tags(wp_slash($_POST['_wp_attachment_image_alt']), true));
			} else {
				delete_post_meta($post->ID, '_wp_attachment_image_alt');
			}
		}

		public function handle_upload () {
			check_ajax_referer(Admin::AJAX_FORM);
			$post_id = intval($_POST['post_id']);
			$post = get_post($post_id);
			if (!$post) {
				wp_die(__('Post not found'));
			}
			if ($post->post_type != $this->_post_type) {
				wp_die(__('Invalid post'));
			}
			$file = $_FILES['async-upload'];
			$status = wp_handle_upload($file, array('test_form' => true, 'action' => Admin::AJAX_ACTION));
			$image_file = $status['file'];
			update_attached_file($post->ID, $image_file);
			wp_update_post(array('ID' => $post->ID,
				'post_mime_type' => WP_Copy::get_mime_type(strtolower(pathinfo($image_file, PATHINFO_EXTENSION))),
				'post_status' => 'draft',
				'post_title' => pathinfo($image_file, PATHINFO_FILENAME)));
			$metadata = wp_generate_attachment_metadata($post->ID, $image_file);
			wp_update_attachment_metadata($post->ID, $metadata);
			wp_send_json(array('url' => $status['url']));
		}
	}
}
