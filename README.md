# Safe SVG

![Safe SVG](https://github.com/10up/safe-svg/blob/develop/.wordpress-org/banner-1544x500.png)

[![Support Level](https://img.shields.io/badge/support-stable-blue.svg)](#support-level) ![Required PHP Version](https://img.shields.io/wordpress/plugin/required-php/safe-svg?label=Requires%20PHP) ![Required WP Version](https://img.shields.io/wordpress/plugin/wp-version/safe-svg?label=Requires%20WordPress) ![WordPress tested up to version](https://img.shields.io/wordpress/plugin/tested/safe-svg?label=WordPress) [![GPL-2.0-or-later License](https://img.shields.io/github/license/10up/safe-svg.svg)](https://github.com/10up/safe-svg/blob/develop/LICENSE.md) [![Dependency Review](https://github.com/10up/safe-svg/actions/workflows/dependency-review.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/dependency-review.yml) [![E2E test](https://github.com/10up/safe-svg/actions/workflows/cypress.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/cypress.yml) [![PHP Compatibility](https://github.com/10up/safe-svg/actions/workflows/php-compatibility.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/php-compatibility.yml) [![PHPCS](https://github.com/10up/safe-svg/actions/workflows/phpcs.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/phpcs.yml) [![PHPUnit](https://github.com/10up/safe-svg/actions/workflows/phpunit.yml/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/phpunit.yml) [![CodeQL](https://github.com/10up/safe-svg/actions/workflows/github-code-scanning/codeql/badge.svg)](https://github.com/10up/safe-svg/actions/workflows/github-code-scanning/codeql) [![WordPress Playground Demo](https://img.shields.io/wordpress/plugin/v/safe-svg?logo=wordpress&logoColor=FFFFFF&label=Playground%20Demo&labelColor=3858E9&color=3858E9)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/10up/safe-svg/update/badges/.wordpress-org/blueprints/blueprint.json)

> Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website.

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

### Technical: Upload Path Security

WordPress’s `_wp_handle_upload( $file, $action )` function allows any `$action` value, which determines the filter hook name: `{$action}_prefilter`. Safe SVG hooks common actions like `wp_handle_upload` and `wp_handle_sideload`, but cannot hook arbitrary custom actions defined by third-party code. Since upload actions are unbounded and MIME allowances are global, we cannot guarantee sanitization coverage across all possible upload paths.

## Requirements

* PHP 7.4+
* [WordPress](http://wordpress.org/) 6.6+

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

### Can my theme style an inline SVG?

Mostly, yes. The Inline SVG block renders an SVG that carries its own `<style>` element inside a shadow root, because CSS inside an inline SVG is otherwise applied to the whole page rather than just the SVG. Stylesheets cannot reach into a shadow root, so theme CSS such as `.entry-content svg { fill: red; }` will not apply to those SVGs.

Inherited properties still cross the boundary, so setting `color` on an ancestor and using `currentColor` inside the SVG works, as do CSS custom properties. SVGs that do not contain a `<style>` element are rendered exactly as before and can be styled normally.

To turn isolation off, at the cost of allowing an SVG's CSS to affect the rest of the page:

```php
add_filter( 'safe_svg_inline_use_shadow_dom', '__return_false' );
```

### Why doesn't Safe SVG globally enable SVG uploads?

Safe SVG only allows SVGs through upload paths it can actively sanitize. While most WordPress uploads use standard functions like `wp_handle_upload()` (which Safe SVG hooks), plugins and themes can create custom upload paths by calling WordPress's underlying `_wp_handle_upload()` function with arbitrary action parameters.

Globally enabling the `image/svg+xml` MIME type would allow SVGs through all upload paths—including custom ones Safe SVG cannot intercept and sanitize. This would create security vulnerabilities where unsanitized SVGs containing malicious scripts could be uploaded.

This is a deliberate design decision: Safe SVG prioritizes guaranteed sanitization over broad compatibility. SVGs are only allowed when we can ensure they're safe.

### Where do I report security bugs found in this plugin?

Please report security bugs found in the source code of the Safe SVG plugin through the [Patchstack Vulnerability Disclosure  Program](https://patchstack.com/database/vdp/9e5fb4ed-587a-4ada-8dc3-a5b7362c0501).  The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

## Support Level

**Stable:** 10up is not planning to develop any new features for this, but will still respond to bug reports and security concerns. We welcome PRs, but any that include new features should be small and easy to integrate and should not include breaking changes. We otherwise intend to keep this tested up to the most recent version of WordPress.

## Changelog

A complete listing of all notable changes to Safe SVG are documented in [CHANGELOG.md](CHANGELOG.md).

## Contributing

Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for details on our code of conduct,
[CONTRIBUTING.md](CONTRIBUTING.md) for details on the process for submitting pull requests to us,
and [CREDITS.md](CREDITS.md) for a listing of maintainers of, contributors to, and libraries used by Safe SVG.

## Like what you see?

<a href="http://10up.com/contact/"><img src="https://github.com/10up/.github/blob/trunk/profile/10up-github-banner.jpg" width="850" alt="Work with the 10up WordPress Practice at Fueled"></a>
