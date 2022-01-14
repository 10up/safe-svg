# Safe SVG

> Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website

[![Support Level](https://img.shields.io/badge/support-active-green.svg)](#support-level) [![Release Version](https://img.shields.io/github/release/10up/safe-svg.svg)](https://github.com/10up/safe-svg/releases/latest) ![WordPress tested up to version](https://img.shields.io/wordpress/plugin/tested/safe-svg?label=WordPress) [![GPLv2 License](https://img.shields.io/github/license/10up/safe-svg.svg)](https://github.com/10up/safe-svg/blob/develop/LICENSE.md)

## Overview

Safe SVG is the best way to Allow SVG Uploads in WordPress!

It gives you the ability to allow SVG uploads whilst making sure that they're sanitized to stop SVG/XML vulnerabilities affecting your site.
It also gives you the ability to preview your uploaded SVGs in the media library in all views.

### Current Features
* **Sanitised SVGs** - Don't open up security holes in your WordPress site by allowing uploads of unsanitised files.
* **View SVGs in the Media Library** - Gone are the days of guessing which SVG is the correct one, we'll enable SVG previews in the WordPress media library.

### Features on the Roadmap
* **SVGO Optimisation** - You'll have the option to run your SVGs through our SVGO server on upload to save you space.
* **Choose Who Can Upload** - Restrict SVG uploads to certain users on your WordPress site or allow anyone to upload.

Initially a proof of concept for [#24251](https://core.trac.wordpress.org/ticket/24251).

SVG Sanitization is done through the following library: [https://github.com/darylldoyle/svg-sanitizer](https://github.com/darylldoyle/svg-sanitizer).

## Requirements

* PHP 5.6+
* [WordPress](http://wordpress.org/) 4.0+

## Installation

Install through the WordPress directory or download, unzip and upload the files to your `/wp-content/plugins/` directory.

## Frequently Asked Questions

### Can we change the allowed attributes and tags?

Yes, this can be done using the `svg_allowed_attributes` and `svg_allowed_tags` filters.
They take one argument that must be returned. See below for examples:

    add_filter( 'svg_allowed_attributes', function ( $attributes ) {

        // Do what you want here...

        // This should return an array so add your attributes to
        // to the $attributes array before returning it. E.G.

        $attributes[] = 'target'; // This would allow the target="" attribute.

        return $attributes;
    } );


    add_filter( 'svg_allowed_tags', function ( $tags ) {

        // Do what you want here...

        // This should return an array so add your tags to
        // to the $tags array before returning it. E.G.

        $tags[] = 'use'; // This would allow the <use> element.

        return $tags;
    } );

## Support Level

**Active:** 10up is actively working on this, and we expect to continue work for the foreseeable future including keeping tested up to the most recent version of WordPress.  Bug reports, feature requests, questions, and pull requests are welcome.

## Changelog

A complete listing of all notable changes to Safe SVG are documented in [CHANGELOG.md](https://github.com/10up/safe-svg/blob/develop/CHANGELOG.md).

## Contributing

Please read [CODE_OF_CONDUCT.md](https://github.com/10up/safe-svg/blob/develop/CODE_OF_CONDUCT.md) for details on our code of conduct, [CONTRIBUTING.md](https://github.com/10up/safe-svg/blob/develop/CONTRIBUTING.md) for details on the process for submitting pull requests to us, and [CREDITS.md](https://github.com/10up/safe-svg/blob/develop/CREDITS.md) for a listing of maintainers of, contributors to, and libraries used by Safe SVG.

## Like what you see?

<a href="http://10up.com/contact/"><img src="https://10up.com/uploads/2016/10/10up-Github-Banner.png" width="850" alt="Work with us at 10up"></a>
