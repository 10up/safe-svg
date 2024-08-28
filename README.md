# Safe SVG

> Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website.

[![Support Level](https://img.shields.io/badge/support-stable-blue.svg)](#support-level) ![WordPress tested up to version](https://img.shields.io/wordpress/plugin/tested/safe-svg?label=WordPress) [![GPL-2.0-or-later License](https://img.shields.io/github/license/10up/safe-svg.svg)](https://github.com/10up/safe-svg/blob/develop/LICENSE.md) [![WordPress Playground Demo](https://img.shields.io/wordpress/plugin/v/safe-svg?logo=wordpress&logoColor=FFFFFF&label=Playground%20Demo&labelColor=3858E9&color=3858E9)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/10up/safe-svg/update/badges/.wordpress-org/blueprints/blueprint.json)

[![E2E test](https://github.com/10up/safe-svg/actions/workflows/cypress.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/cypress.yml) [![PHP Compatibility](https://github.com/10up/safe-svg/actions/workflows/php-compatibility.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/php-compatibility.yml) [![PHPCS](https://github.com/10up/safe-svg/actions/workflows/phpcs.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/phpcs.yml) [![PHPUnit](https://github.com/10up/safe-svg/actions/workflows/phpunit.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/phpunit.yml) [![Dependency Review](https://github.com/10up/safe-svg/actions/workflows/dependency-review.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/dependency-review.yml) 

## Overview

Safe SVG is the best way to Allow SVG Uploads in WordPress!

It gives you the ability to allow SVG uploads whilst making sure that they're sanitized to stop SVG/XML vulnerabilities affecting your site.  It also gives you the ability to preview your uploaded SVGs in the media library in all views.

### Current Features

* **Sanitised SVGs** - Don't open up security holes in your WordPress site by allowing uploads of unsanitised files.
* **SVGO Optimisation** - Runs your SVGs through the SVGO tool on upload to save you space. This feature is disabled by default but can be enabled by adding the following code: `add_filter( 'safe_svg_optimizer_enabled', '__return_true' );`
* **View SVGs in the Media Library** - Gone are the days of guessing which SVG is the correct one, we'll enable SVG previews in the WordPress media library.
* **Choose Who Can Upload** - Restrict SVG uploads to certain users on your WordPress site or allow anyone to upload.

Initially a proof of concept for [#24251](https://core.trac.wordpress.org/ticket/24251).

SVG Sanitization is done through the following library: [https://github.com/darylldoyle/svg-sanitizer](https://github.com/darylldoyle/svg-sanitizer).

SVG Optimization is done through the following library: [https://github.com/svg/svgo](https://github.com/svg/svgo).

## Requirements

* PHP 7.4+
* [WordPress](http://wordpress.org/) 6.4+

## Installation

Install through the WordPress directory or download, unzip and upload the files to your `/wp-content/plugins/` directory.

## Frequently Asked Questions

### Can we change the allowed attributes and tags?

Yes, this can be done using the `svg_allowed_attributes` and `svg_allowed_tags` filters.
They take one argument that must be returned. See below for examples:

```php
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
```

## Support Level

**Stable:** 10up is not planning to develop any new features for this, but will still respond to bug reports and security concerns. We welcome PRs, but any that include new features should be small and easy to integrate and should not include breaking changes. We otherwise intend to keep this tested up to the most recent version of WordPress.

## Changelog

A complete listing of all notable changes to Safe SVG are documented in [CHANGELOG.md](CHANGELOG.md).

## Contributing

Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for details on our code of conduct,
[CONTRIBUTING.md](CONTRIBUTING.md) for details on the process for submitting pull requests to us,
and [CREDITS.md](CREDITS.md) for a listing of maintainers of, contributors to, and libraries used by Safe SVG.

## Like what you see?

<a href="http://10up.com/contact/"><img src="https://10up.com/uploads/2016/10/10up-Github-Banner.png" width="850" alt="Work with us at 10up"></a>
