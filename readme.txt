=== Safe SVG ===
Contributors: enshrined
Donate link: https://wpsvg.com/
Tags: svg, sanitize, upload, sanitise, security, svg upload, image, vector, file, graphic, media, mime
Requires at least: 4.0
Tested up to: 4.9.1
Stable tag: 1.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website

== Description ==

Safe SVG is the best way to Allow SVG Uploads in WordPress!

It gives you the ability to allow SVG uploads whilst making sure that they're sanitized to stop SVG/XML vulnerabilities affecting your site.
It also gives you the ability to preview your uploaded SVGs in the media library in all views.

>**[Loving Safe SVG? Try the Pro version for extra features.](https://wpsvg.com/)**

#### Free Features
* **Sanitised SVGs** - Don't open up security holes in your WordPress site by allowing uploads of unsanitised files.
* **View SVGs in the Media Library** - Gone are the days of guessing which SVG is the correct one, we'll enable SVG previews in the WordPress media library.

#### Pro Features
* **SVGO Optimisation** - You'll have the option to run your SVGs through our SVGO server on upload to save you space.
* **Choose Who Can Upload** - Restrict SVG uploads to certain users on your WordPress site or allow anyone to upload.
* **Premium Support** - Pro users get premium support whilst free support is offered in the WordPress forums in our spare time


Initially a proof of concept for [#24251](https://core.trac.wordpress.org/ticket/24251)

SVG Sanitization is done through the following library: [https://github.com/darylldoyle/svg-sanitizer](https://github.com/darylldoyle/svg-sanitizer)

== Installation ==

Install through the WordPress directory or download, unzip and upload the files to your `/wp-content/plugins/` directory

== Changelog ==

= 1.6.1 =
* Images will now use the size chosen when inserted into the page rather than default to 2000px everytime.

= 1.6.0 =
* Fairly big new feature - The library now allows `<use>` elements as long as they don't reference external files!
* You can now also embed safe image types within the SVG and not have them stripped (PNG, GIF, JPG)

= 1.5.3 =
* 1.5.2 introduced an issue that can freeze the media library. This fixes that issue. Sorry!

= 1.5.2 =
* Tested with 4.9.0
* Fixed an issue with SVGs when regenerating media

= 1.5.1 =
* Fix PHP strict standards warning

= 1.5.0 =
* Library update
* role, aria- and data- attributes are now whitelisted to improve accessibility

= 1.4.5 =
* Fixes some issues with defining the size of an SVG.
* Library update

= 1.4.4 =
* SVGs now display as featured images in the admin area

= 1.4.3 =
* WordPress 4.7.3 Compatibility
* Expanded SVG previews in media library

= 1.4.2 =
* Added a check / fix for when mb_* functions are not available

= 1.4.1 =
* Updated underlying library to allow attributes/tags in all case variations

= 1.4.0 =
* Added ability to preview SVG on both grid and list view in the wp-admin media area
* Updated underlying library version

= 1.3.4 =
* A fix for SVGZ uploads failing and not sanitising correctly

= 1.3.3 =
* Allow SVGZ uploads

= 1.3.2 =
* Fix for the mime type issue in 4.7.1. Mad props to @lewiscowles

= 1.3.1 =
* Updated underlying library version

= 1.3.0 =
* Minify SVGs after cleaning so they can be loaded correctly through file_get_contents

= 1.2.0 =
* Added support for camel case attributes such as viewBox

= 1.1.1 =
* Fixed an issue with empty svg elements self-closing

= 1.1.0 =
* Added i18n
* Added da, de ,en, es, fr, nl and ru translations
* Fixed an issue with filename not being pulled over on failed uploads

= 1.0.0 =
* Initial Release