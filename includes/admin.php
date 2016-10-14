<?php

abstract class ACPT_Admin
{
    const remove_pt_support = array('editor', 'revisions', 'thumbnail', 'excerpt');

    const ajax_form = 'acpt-media-upload-form';
    const ajax_action = 'acpt-upload-media';

    static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('pre_get_posts', array(__CLASS__, 'adjust_post'));
        add_action('edit_form_after_title', array(__CLASS__, 'implant_editor'));
        add_action('before_delete_post', array( __CLASS__, 'before_delete_post' ) );
        add_action('admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts'));
        add_action('save_post', array(__CLASS__, 'save_post'));
        add_action('wp_ajax_'.static::ajax_action, array(__CLASS__, 'handle_upload'));
    }


    static function enqueue_scripts ($page) {
        if ( (( $page != 'post.php' ) && ( $page != 'post-new.php' )) ||
             !Attachment_CPT::is_attachment_type(get_post_type()) ) {
            return;
        }

        if($page == 'post-new.php') {
            wp_enqueue_script( 'plupload-all' );
        }
        if($page == 'post.php') {
            wp_enqueue_script( 'image-edit' );
            wp_enqueue_style( 'imgareaselect' );
        }
    }


    static function add_meta_boxes() {
        global $post;

        if (Attachment_CPT::is_attachment_type($post->post_type)) {
            if ($post->post_status == 'auto-draft') {
                remove_post_type_support( $post->post_type, 'title' );
                remove_meta_box('submitdiv', $post->post_type, 'side');
                remove_meta_box('titlediv', $post->post_type, 'normal');
                remove_meta_box('authordiv', $post->post_type, 'normal');
                remove_meta_box('slugdiv', $post->post_type, 'normal');
            }
        }
    }

    static function adjust_post( $query ) {
        $cs = get_current_screen();
        if (!$cs) {
            return;
        }
        if(!Attachment_CPT::is_attachment_type($cs->post_type)) {
            return;
        }

        foreach(static::remove_pt_support as $entry) {
            remove_post_type_support ($cs->post_type, $entry);
        }
    }


    static function implant_editor($post) {
        if(!Attachment_CPT::is_attachment_type($post->post_type)) {
            return;
        }
        $template = 'media-editor';
        if($post->post_status == 'auto-draft') {
            $template = 'media-editor-new';
        }
        Attachment_CPT::template( $template, compact( 'post' ) );
    }


    function before_delete_post( $post_id ) {
        if ( !Attachment_CPT::is_attachment_type(get_post_type( $post_id ))) {
            return;
        }
        ACPT_WP::delete_attached_files($post_id);
    }


    static function save_post($post_id)
    {
        $post = get_post($post_id);
        if (!Attachment_CPT::is_attachment_type($post->post_type)) {
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


    function handle_upload() {
        check_ajax_referer(static::ajax_form);
        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);
        if(!$post) {
            wp_die(__('Post not found', Attachment_CPT::text_domain));
        }
        if (!Attachment_CPT::is_attachment_type($post->post_type)) {
            wp_die(__('Invalid post', Attachment_CPT::text_domain));
        }
        $file = $_FILES['async-upload'];
        $status = wp_handle_upload($file, array('test_form'=>true, 'action' => static::ajax_action));
        $image_file = $status['file'];
        update_attached_file($post->ID, $image_file);
        wp_update_post(array('ID' => $post->ID,
            'post_mime_type' => ACPT_WP::get_mime_type(strtolower( pathinfo( $image_file, PATHINFO_EXTENSION ) )),
            'post_status' => 'draft',
            'post_title' => pathinfo( $image_file, PATHINFO_FILENAME )));
        $metadata = wp_generate_attachment_metadata($post->ID, $image_file);
        wp_update_attachment_metadata($post->ID, $metadata);
        die('{"jsonrpc" : "2.0", "url" : "'.($status['url']).'" }');
    }

}
?>