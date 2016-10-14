=== Attachment CPT ===

Tags: posts, custom post type, attachment, developer, base
Requires at least: 4.6
Tested up to:  4.6
Stable tag: 0.1
License: GPLv2 or Later

WordPress Attachment CPT (Custom Post Type) plugin allows developers to create custom post types with attachment post type behaviour.

== Description ==

WordPress Attachment Post Type plugin allows a developer of easily obtain attachment editor for a custom post type. Such a custom post type later may be used in media galleries, other places where you have to distinct media from the regular media library.
 
= How might you use it? =
* Create your own plugin with a support of an attachment custom post type
* Distinct regular media gallery from your plugins posts

= How does it work? =
A user plugin registers in Attachment CPT with the custom post type.
How Atachment CPT plugin takes care of adjusting editor for having an attachment behaviour of your custom post types:
* Sets filter 'image_downsize' to skip 'attachment' post check performed by Wordpress core.
* Removes all stuff from the post creation page, allowing only one image upload.
* Removes unsupported metaboxes on the editor page, adds Alt image, caption and description boxes.
* Makes thumbnails for you image once you upload it
* Deletes your uploaded media from the uploads folder once the post is permanently deleted

= Create your plugin =
<?php
add_action( 'plugins_loaded', 'plugins_loaded' );

function plugins_loaded() {

    Attachment_CPT::add_acpt_type( 'my_post_type' );
}
?>

This is sufficient to make your post types to have an attachment behaviour in the editor.

= Restrictions =
1. For now, ACPT supports only images.

== Installation ==

= Automatic Install =
1. Login to your WordPress site as an Administrator, or if you haven't already, complete the famous [WordPress Five Minute Install](http://codex.wordpress.org/Installing_WordPress)
1. Navigate to Plugins->Add New from the menu on the left
1. Search for Post Forking
1. Click "Install"
1. Click "Activate Now"

= Manual Install =
1. Download the plugin from the link in the top left corner
1. Unzip the file, and upload the resulting "attachment-cpt" folder to your "/wp-content/plugins directory" as "/wp-content/plugins/attachment-cpt"
1. Log into your WordPress install as an administrator, and navigate to the plugins screen from the left-hand menu
1. Activate Attachment CPT

= Demo =
You can enable demo plugin that is embedded into the Attachment CPT:
1. Open Settings page
1. Choose "Media Settings"
1. Find "Attachment CPT" section
1. Tick "Show demo plugin" checkbox
1. Save settings

Once you done, you will see that there is a post type called "acpt-demo" registered with the plugin

== Frequently Asked Questions ==

Please, ask: Artem Shelest <artem.e.shelest@gmail.com>

== Screenshots ==

![Add new Gallery Image of the demo plugin](https://cl.ly/0W1S341p213j/add_image.png)

![Edit Gallery Image 1 of the demo plugin](https://cl.ly/201D0m0i0F1u/edit_image1.png)

![Edit Gallery Image 2 of the demo plugin](https://cl.ly/1Y3v1o2x0m1d/edit_image2.png)

![Gallery Images list](https://cl.ly/12290x2U2w2I/image_list.png)

![Attachment CPT settings](https://cl.ly/1l0p3j1C3Z2R/settings.png)

== Changelog ==

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.1 =
* Initial Release
