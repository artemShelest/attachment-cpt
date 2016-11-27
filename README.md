# ACPT-Lib: Attachment Custom Post Type Library

Copyright (c) 2016 Artem Shelest
Licensed under MIT license, see LICENSE for details.

## Description

The ACPT-Lib, Attachment Custom Post Type Library, allows to have custom
post types with a behavior of an "attachment" post type.
A user plugin creates an Attachment custom post type object.
How Atachment CPT plugin takes care of adjusting editor for having an attachment behaviour of your custom post types:
* Sets filter 'image_downsize' to skip 'attachment' post check performed by Wordpress core.
* Removes all stuff from the post creation page, allowing only one image upload.
* Removes unsupported metaboxes on the editor page, adds alt image, caption and description boxes.
* Makes thumbnails for the image once it is uploaded
* Deletes uploaded media from the uploads folder once the post is permanently deleted

## Features

* Class for an attachment custom post types.
* Attachment editor support for attachment custom post types.

## Installation

1. Copy distribution folder to your library path or plugin root.
2. Replace acpt_plugin_ns everywhere with your plugin actual namespace.
If you do not have one - choose any unique to use with the library. This
will ensure you do not overlap with someone else's plugin using this
library, as it may be of other version.
3. Require once acpt-post-type.php from library root prior to any library
calls.

## Changelog

0.2 - 2016-11-27
----------------

### Updates

- changed plugin dependency architecture to library
- added readme

0.1 - 2016-10-14
----------------

- Initial Commit
