<div class="wp_attachment_holder wp-clearfix">
    <div id="plupload-upload-ui" class="hide-if-no-js">
        <div id="drag-drop-area">
            <div class="drag-drop-inside">
                <p class="drag-drop-info"><?php _e('Drop file here', Attachment_CPT::text_domain); ?></p>
                <p><?php _ex('or', 'Uploader: Drop file here - or - Select File', Attachment_CPT::text_domain); ?></p>
                <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php _e('Select File', Attachment_CPT::text_domain); ?>" class="button" /></p>
            </div>
        </div>
    </div>
</div>
<?php

$plupload_init = array(
    'runtimes'            => 'html5,silverlight,flash,html4',
    'browse_button'       => 'plupload-browse-button',
    'container'           => 'plupload-upload-ui',
    'drop_element'        => 'drag-drop-area',
    'file_data_name'      => 'async-upload',
    'multiple_queues'     => true,
    'max_file_size'       => wp_max_upload_size().'b',
    'url'                 => admin_url('admin-ajax.php'),
    'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
    'filters'             => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
    'multipart'           => true,
    'urlstream_upload'    => true,

    'multipart_params'    => array(
        '_ajax_nonce' => wp_create_nonce(ACPT_Admin::ajax_form),
        'action'      => ACPT_Admin::ajax_action,
        'post_id' => $post->ID,
    ),
);

$plupload_init = apply_filters('plupload_init', $plupload_init); ?>

<script type="text/javascript">

    jQuery(document).ready(function($){

        // create the uploader and pass the config from above
        var uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

        // checks if browser supports drag and drop upload, makes some css adjustments if necessary
        uploader.bind('Init', function(up){
            var uploaddiv = $('#plupload-upload-ui');

            if(up.features.dragdrop){
                uploaddiv.addClass('drag-drop');
                $('#drag-drop-area')
                    .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                    .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

            }else{
                uploaddiv.removeClass('drag-drop');
                $('#drag-drop-area').unbind('.wp-uploader');
            }
        });

        uploader.init();

        uploader.bind('FilesAdded', function(up, files){
            var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
            if (files.length > 1){
                alert("<?php _e('You can upload only one file', Attachment_CPT::text_domain); ?>");
                return false;
            }
            var file = files[0]; //process only the first one
            if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5'){
                alert("<?php _e('Maximum allowed size exceeded', Attachment_CPT::text_domain); ?>");
                return false;
            }else{
                $("#plupload-browse-button").prop("disabled", true);
            }

            up.refresh();
            up.start();
        });

        uploader.bind('FileUploaded', function(up, file, response) {
            window.location.href = "<?php echo admin_url( 'post.php?post='.$post->ID.'&action=edit'); ?>";
        });

        uploader.bind('Error', function(up, args) {
            $("#plupload-browse-button").prop("disabled", false);
            alert("Error uploading file: " + args.message);
        });
    });

</script>
<?php
//$extras = get_compat_media_markup( $post->ID );
//echo $extras['item'];
//echo '<input type="hidden" id="image-edit-context" value="edit-attachment" />' . "\n";
?>