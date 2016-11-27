<?php

namespace acpt_plugin_ns\acpt_lib {
	if (!defined ('ABSPATH')) {
		exit;
	}

// TODO: update with WP version, current is 4.6.1
	abstract class WP_Copy {

		// copy/paste from WP_Image_Editor
		static function get_mime_type ($extension) {
			$mime_types = wp_get_mime_types();
			$extensions = array_keys($mime_types);

			foreach ($extensions as $_extension) {
				if (preg_match("/{$extension}/i", $_extension)) {
					return $mime_types[$_extension];
				}
			}

			return '';
		}

		// copy paste of image_downsize w/o post type checks and correct call to get attachment url
		static function image_downsize ($downsize, $id, $size) {
			if (!wp_attachment_is_image($id)) {
				return false;
			}

			/*
			 * Remove filters call as we are the filter
	
			if ( $out = apply_filters( 'image_downsize', false, $id, $size ) ) {
				return $out;
			}
			*/

			/*
			 * Replace wp_get_attachment_url() call to static::get_attachment_url()
			$img_url = wp_get_attachment_url($id);
			*/
			$img_url = static::get_attachment_url($id);
			$meta = wp_get_attachment_metadata($id);
			$width = $height = 0;
			$is_intermediate = false;
			$img_url_basename = wp_basename($img_url);

			// try for a new style intermediate size
			if ($intermediate = image_get_intermediate_size($id, $size)) {
				$img_url = str_replace($img_url_basename, $intermediate['file'], $img_url);
				$width = $intermediate['width'];
				$height = $intermediate['height'];
				$is_intermediate = true;
			} elseif ($size == 'thumbnail') {
				// fall back to the old thumbnail
				if (($thumb_file = wp_get_attachment_thumb_file($id)) && $info = getimagesize($thumb_file)) {
					$img_url = str_replace($img_url_basename, wp_basename($thumb_file), $img_url);
					$width = $info[0];
					$height = $info[1];
					$is_intermediate = true;
				}
			}
			if (!$width && !$height && isset($meta['width'], $meta['height'])) {
				// any other type: use the real image
				$width = $meta['width'];
				$height = $meta['height'];
			}

			if ($img_url) {
				// we have the actual image size, but might need to further constrain it if content_width is narrower
				list($width, $height) = image_constrain_size_for_editor($width, $height, $size);

				return array($img_url, $width, $height, $is_intermediate);
			}
			return false;

		}

		// copy paste of wp_get_attachment_url w/o post type check
		static function get_attachment_url ($post_id = 0) {
			$post_id = (int)$post_id;
			if (!$post = get_post($post_id)) {
				return false;
			}

			/*
			 * remove type check
			if ( 'attachment' != $post->post_type )
				return false;
			*/

			$url = '';
			// Get attached file.
			if ($file = get_post_meta($post->ID, '_wp_attached_file', true)) {
				// Get upload directory.
				if (($uploads = wp_get_upload_dir()) && false === $uploads['error']) {
					// Check that the upload base exists in the file location.
					if (0 === strpos($file, $uploads['basedir'])) {
						// Replace file location with url location.
						$url = str_replace($uploads['basedir'], $uploads['baseurl'], $file);
					} elseif (false !== strpos($file, 'wp-content/uploads')) {
						// Get the directory name relative to the basedir (back compat for pre-2.7 uploads)
						$url = trailingslashit($uploads['baseurl'] . '/' . _wp_get_attachment_relative_path($file)) . basename($file);
					} else {
						// It's a newly-uploaded file, therefore $file is relative to the basedir.
						$url = $uploads['baseurl'] . "/$file";
					}
				}
			}

			/*
			 * If any of the above options failed, Fallback on the GUID as used pre-2.7,
			 * not recommended to rely upon this.
			 */
			if (empty($url)) {
				$url = get_the_guid($post->ID);
			}

			// On SSL front end, URLs should be HTTPS.
			if (is_ssl() && !is_admin() && 'wp-login.php' !== $GLOBALS['pagenow']) {
				$url = set_url_scheme($url);
			}

			/**
			 * Filters the attachment URL.
			 *
			 * @since 2.1.0
			 *
			 * @param string $url URL for the given attachment.
			 * @param int $post_id Attachment ID.
			 */
			$url = apply_filters('wp_get_attachment_url', $url, $post->ID);

			if (empty($url)) {
				return false;
			}

			return $url;
		}


		// copy/paste of wp_delete_attachment
		// This is intended to delete only generated files and attached file
		static function delete_attached_files ($post_id) {
			global $wpdb;

			/*
			 * Removed this, as we do not need post object, do not need this check and do not work with trashed items
			if ( !$post = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $post_id) ) )
				return $post;
	
			if ( 'attachment' != $post->post_type )
				return false;
	
			if ( !$force_delete && EMPTY_TRASH_DAYS && MEDIA_TRASH && 'trash' != $post->post_status )
				return wp_trash_post( $post_id );
	
			delete_post_meta($post_id, '_wp_trash_meta_status');
			delete_post_meta($post_id, '_wp_trash_meta_time');
			*/

			$meta = wp_get_attachment_metadata($post_id);
			$backup_sizes = get_post_meta($post_id, '_wp_attachment_backup_sizes', true);
			$file = get_attached_file($post_id);

			if (is_multisite()) {
				delete_transient('dirsize_cache');
			}

			/*
			 * Removed this as we do not delete post, also, skip all actions related to post and comments deletion
			do_action( 'delete_attachment', $post_id );
	
			wp_delete_object_term_relationships($post_id, array('category', 'post_tag'));
			wp_delete_object_term_relationships($post_id, get_object_taxonomies($post->post_type));
	
			// Delete all for any posts.
			delete_metadata( 'post', null, '_thumbnail_id', $post_id, true );
	
			wp_defer_comment_counting( true );
	
			$comment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d", $post_id ));
			foreach ( $comment_ids as $comment_id ) {
				wp_delete_comment( $comment_id, true );
			}
	
			wp_defer_comment_counting( false );
	
			$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d ", $post_id ));
			foreach ( $post_meta_ids as $mid )
				delete_metadata_by_mid( 'post', $mid );
	
			/** This action is documented in wp-includes/post.php * /
			do_action( 'delete_post', $post_id );
			$result = $wpdb->delete( $wpdb->posts, array( 'ID' => $post_id ) );
			if ( ! $result ) {
				return false;
			}
			/** This action is documented in wp-includes/post.php * /
			do_action( 'deleted_post', $post_id );
			*/

			$uploadpath = wp_get_upload_dir();

			if (!empty($meta['thumb'])) {
				// Don't delete the thumb if another attachment uses it.
				if (!$wpdb->get_row($wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE %s AND post_id <> %d", '%' . $wpdb->esc_like($meta['thumb']) . '%', $post_id))) {
					$thumbfile = str_replace(basename($file), $meta['thumb'], $file);
					/** This filter is documented in wp-includes/functions.php */
					$thumbfile = apply_filters('wp_delete_file', $thumbfile);
					@ unlink(path_join($uploadpath['basedir'], $thumbfile));
				}
			}

			// Remove intermediate and backup images if there are any.
			if (isset($meta['sizes']) && is_array($meta['sizes'])) {
				foreach ($meta['sizes'] as $size => $sizeinfo) {
					$intermediate_file = str_replace(basename($file), $sizeinfo['file'], $file);
					/** This filter is documented in wp-includes/functions.php */
					$intermediate_file = apply_filters('wp_delete_file', $intermediate_file);
					@ unlink(path_join($uploadpath['basedir'], $intermediate_file));
				}
			}

			if (is_array($backup_sizes)) {
				foreach ($backup_sizes as $size) {
					$del_file = path_join(dirname($meta['file']), $size['file']);
					/** This filter is documented in wp-includes/functions.php */
					$del_file = apply_filters('wp_delete_file', $del_file);
					@ unlink(path_join($uploadpath['basedir'], $del_file));
				}
			}

			wp_delete_file($file);

			/*
			 * Changed to call with $post_id instead of the $post object which we do not have
			 */
			clean_post_cache($post_id);

			return;
		}

	}
}