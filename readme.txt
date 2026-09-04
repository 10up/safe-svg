=== Safe SVG ===
Contributors:      10up, enshrined, jeffpaul
Tags:              svg, security, media, vector, mime
Requires at least: 6.9
Requires PHP:      7.4
Tested up to:      7.1
Stable tag:        2.4.0
License:           GPL-2.0-or-later
License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html

Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website.

== Description ==

Safe SVG is the best way to Allow SVG Uploads in WordPress!

It gives you the ability to allow SVG uploads whilst making sure that they're sanitized to stop SVG/XML vulnerabilities affecting your site.  It also gives you the ability to preview your uploaded SVGs in the media library in all views.

= Current Features =

* **Sanitised SVGs** - Don't open up security holes in your WordPress site by allowing uploads of unsanitised files.
* **SVGO Optimisation** - Runs your SVGs through the SVGO tool on upload to save you space. This feature is disabled by default but can be enabled by adding the following code: `add_filter( 'safe_svg_optimizer_enabled', '__return_true' );`
* **View SVGs in the Media Library** - Gone are the days of guessing which SVG is the correct one, we'll enable SVG previews in the WordPress media library.
* **Choose Who Can Upload** - Restrict SVG uploads to certain users on your WordPress site or allow anyone to upload.

Initially a proof of concept for [#24251](https://core.trac.wordpress.org/ticket/24251).

SVG Sanitization is done through the following library: [https://github.com/darylldoyle/svg-sanitizer](https://github.com/darylldoyle/svg-sanitizer).

SVG Optimization is done through the following library: [https://github.com/svg/svgo](https://github.com/svg/svgo).

= Technical: Upload Path Security =

WordPress’s `_wp_handle_upload( $file, $action )` function allows any `$action` value, which determines the filter hook name: `{$action}_prefilter`. Safe SVG hooks common actions like `wp_handle_upload` and `wp_handle_sideload`, but cannot hook arbitrary custom actions defined by third-party code. Since upload actions are unbounded and MIME allowances are global, we cannot guarantee sanitization coverage across all possible upload paths.

== Installation ==

Install through the WordPress directory or download, unzip and upload the files to your `/wp-content/plugins/` directory

== Frequently Asked Questions ==

= Can we change the allowed attributes and tags? =

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

= Can my theme style an inline SVG? =

Mostly, yes. The Inline SVG block renders an SVG that carries its own `<style>` element inside a shadow root, because CSS inside an inline SVG is otherwise applied to the whole page rather than just the SVG. Stylesheets cannot reach into a shadow root, so theme CSS such as `.entry-content svg { fill: red; }` will not apply to those SVGs.

Inherited properties still cross the boundary, so setting `color` on an ancestor and using `currentColor` inside the SVG works, as do CSS custom properties. SVGs that do not contain a `<style>` element are rendered without the shadow root and can be styled by theme stylesheets.

To turn isolation off, at the cost of allowing an SVG's CSS to affect the rest of the page:

    add_filter( 'safe_svg_inline_use_shadow_dom', '__return_false' );

= Why doesn't Safe SVG globally enable SVG uploads? =

Safe SVG only allows SVGs through upload paths it can actively sanitize. While most WordPress uploads use standard functions like `wp_handle_upload()` (which Safe SVG hooks), plugins and themes can create custom upload paths by calling WordPress's underlying `_wp_handle_upload()` function with arbitrary action parameters.

Globally enabling the `image/svg+xml` MIME type would allow SVGs through all upload paths—including custom ones Safe SVG cannot intercept and sanitize. This would create security vulnerabilities where unsanitized SVGs containing malicious scripts could be uploaded.

This is a deliberate design decision: Safe SVG prioritizes guaranteed sanitization over broad compatibility. SVGs are only allowed when we can ensure they're safe.

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the Safe SVG plugin through the [Patchstack Vulnerability Disclosure  Program](https://patchstack.com/database/vdp/9e5fb4ed-587a-4ada-8dc3-a5b7362c0501).  The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== Changelog ==

= 2.5.0 - 2026-09-04 =
* **Security:** Prevented direct access of PHP files (props [@mehrazmorshed](https://github.com/mehrazmorshed), [@dkotter](https://github.com/dkotter) via [#300](https://github.com/10up/safe-svg/pull/300)).
* **Security:** The Inline SVG block now renders SVGs that carry their own `<style>` element inside a shadow root, so their CSS is scoped to the block instead of applying to the whole page (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328)).
* **Security:** Bump `enshrined/svg-sanitize` from `^0.22.0` to `^1.0.0` to pull in security fixes (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#327](https://github.com/10up/safe-svg/pull/327)).
* **Added:** Link support for the SVG Inline block, including URL input, new tab toggle, and nofollow/sponsored rel options (props [@vegetable-bits](https://github.com/vegetable-bits), [@mgiannopoulos24](https://github.com/mgiannopoulos24), [@jeffpaul](https://github.com/jeffpaul), [@thrijith](https://github.com/thrijith), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter), [@pbiron](https://github.com/pbiron) via [#315](https://github.com/10up/safe-svg/pull/315)).
* **Added:** New `safe_svg_inline_use_shadow_dom` filter to control which inline SVGs are isolated in a shadow root, and new `safe_svg_inline_shadow_styles` filter to adjust the CSS injected alongside them (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328)).
* **Added:** New `safe_svg_remove_remote_references` filter to strip remote `url()`, `@import` and `image-set()` references, along with remote `href` targets, from uploaded SVGs. Off by default, because legitimate SVGs reference remote fonts and images but use this filter to turn it on (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328)).
* **Added:** Added support for Enable Media Replace plugin (props [@gthayer](https://github.com/gthayer), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#285](https://github.com/10up/safe-svg/pull/285)).
* **Changed:** Bump WordPress minimum supported version to 6.9 (props [@zamanq](https://github.com/zamanq), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#320](https://github.com/10up/safe-svg/pull/320)).
* **Changed:** Bump "tested up to header" to indicate WordPress 7.1 support (props [@navi151](https://github.com/navi151), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@zamanq](https://github.com/zamanq) via [#290](https://github.com/10up/safe-svg/pull/290), [#311](https://github.com/10up/safe-svg/pull/311), [#320](https://github.com/10up/safe-svg/pull/320)).
* **Changed:** Theme CSS can no longer target an inline SVG that carries its own `<style>` element, because stylesheets cannot reach into a shadow root. Style those SVGs from within the SVG itself, or opt out with the `safe_svg_inline_use_shadow_dom` filter. Inherited properties, including `color`/`currentColor` and custom properties, still apply as before, and SVGs without a `<style>` element are unaffected (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328)).
* **Changed:** Updated blueprint file for WordPress.org live previews (props [@fellyph](https://github.com/fellyph), [@username2](https://github.com/username2), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#287](https://github.com/10up/safe-svg/pull/287)).
* **Changed:** Bump `svgo` from 3.2.0 to 3.3.5 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot) via [#309](https://github.com/10up/safe-svg/pull/309)).

= 2.4.0 - 2025-09-22 =
* **Added:** Ability to upload SVGs from more admin locations (props [@stormrockwell](https://github.com/stormrockwell), [@darylldoyle](https://github.com/darylldoyle), [@wpexplorer](https://github.com/wpexplorer), [@smerriman](https://github.com/smerriman), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#279](https://github.com/10up/safe-svg/pull/279)).
* **Changed:** Added `$attachment_id` argument to filters `safe_svg_use_width_height_attributes` and `safe_svg_dimensions` (props [@roborourke](https://github.com/roborourke), [@dkotter](https://github.com/dkotter) via [#278](https://github.com/10up/safe-svg/pull/278)).
* **Fixed:** Inconsistent or incorrect data type for `$svg` argument in the filters `safe_svg_use_width_height_attributes` and `safe_svg_dimensions` (props [@roborourke](https://github.com/roborourke), [@dkotter](https://github.com/dkotter) via [#278](https://github.com/10up/safe-svg/pull/278)).

= 2.3.3 - 2025-08-13 =
* **Security:** Update the `enshrined/svg-sanitize` package from `0.19.0` to `0.22.0` to fix an issue with case-insensitive attributes slipping through the sanitiser and address PHP 8.4 deprecation warnings (props [@darylldoyle](https://github.com/darylldoyle), [@sudar](https://github.com/sudar), [@georgestephanis](https://github.com/georgestephanis), [@dkotter](https://github.com/dkotter), [@realazizk](https://github.com/realazizk) via [#268](https://github.com/10up/safe-svg/pull/268), [#272](https://github.com/10up/safe-svg/pull/272)).
* **Security:** Bump `form-data` from 4.0.0 to 4.0.4 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#270](https://github.com/10up/safe-svg/pull/270)).
* **Security:** Bump `tmp` from 0.2.3 to 0.2.5 and `@inquirer/editor` from 4.2.9 to 4.2.16 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#271](https://github.com/10up/safe-svg/pull/271)).

= 2.3.2 - 2025-07-21 =
* **Fixed:** Visual parity between the front end and the block editor (props [@s3rgiosan](https://github.com/s3rgiosan), [@dkotter](https://github.com/dkotter) via [#261](https://github.com/10up/safe-svg/pull/261), [#266](https://github.com/10up/safe-svg/pull/266)).
* **Changed:** Bump WordPress "tested up to" version 6.8 (props [@godleman](https://github.com/godleman), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#251](https://github.com/10up/safe-svg/pull/251), [#254](https://github.com/10up/safe-svg/pull/254)).
* **Changed:** Bump WordPress minimum supported version to 6.6 (props [@godleman](https://github.com/godleman), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#254](https://github.com/10up/safe-svg/pull/254)).
* **Security:** Bump `ws` from 7.5.10 to 8.18.0, `@wordpress/scripts` from 27.9.0 to 30.6.0, `nanoid` from 3.3.7 to 3.3.8 and `mocha` from 10.2.0 to 11.0.1 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#245](https://github.com/10up/safe-svg/pull/245)).
* **Security:** Bump `@babel/runtime` from 7.23.9 to 7.27.0, `axios` from 1.7.4 to 1.8.4, `cookie` from 0.4.2 to 0.7.1, `express` from 4.21.0 to 4.21.2 and `@wordpress/e2e-test-utils-playwright` from 0.26.0 to 1.20.0 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#250](https://github.com/10up/safe-svg/pull/250)).
* **Security:** Bump `http-proxy-middleware` from 2.0.6 to 2.0.9 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#253](https://github.com/10up/safe-svg/pull/253)).
* **Security:** Bump `tar-fs` from 3.0.8 to 3.0.9 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#258](https://github.com/10up/safe-svg/pull/258)).
* **Security:** Bump `bytes` from 3.0.0 to 3.1.2 and `compression` from 1.7.4 to 1.8.1 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#265](https://github.com/10up/safe-svg/pull/265)).

= 2.3.1 - 2024-12-05 =
* **Fixed:** Revert changes made to how we determine custom dimensions for SVGs (props [@dkotter](https://github.com/dkotter), [@martinpl](https://github.com/martinpl), [@subfighter3](https://github.com/subfighter3), [@smerriman](https://github.com/smerriman), [@gigatyrant](https://github.com/gigatyrant), [@jeffpaul](https://github.com/jeffpaul), [@iamdharmesh](https://github.com/iamdharmesh) via [#238](https://github.com/10up/safe-svg/pull/238)).

= 2.3.0 - 2024-11-25 =
* **Added:** New setting that allows large SVG files (roughly 10MB or greater) to be uploaded and sanitized properly (props [@kirtangajjar](https://github.com/kirtangajjar), [@faisal-alvi](https://github.com/faisal-alvi), [@darylldoyle](https://github.com/darylldoyle), [@manojsiddoji](https://github.com/manojsiddoji), [@dkotter](https://github.com/dkotter) via [#201](https://github.com/10up/safe-svg/pull/201)).
* **Added:** New `get_svg_dimensions` function in order to reduce code duplication (props [@gabriel-glo](https://github.com/gabriel-glo), [@jeremymoore](https://github.com/jeremymoore), [@darylldoyle](https://github.com/darylldoyle), [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#216](https://github.com/10up/safe-svg/pull/216)).
* **Changed:** Updated the `enshrined/svg-sanitize` package from 0.16.0 to 0.19.0 to fix a PHP 8.3 compatibility issue (props [@sksaju](https://github.com/sksaju), [@TylerB24890](https://github.com/TylerB24890), [@darylldoyle](https://github.com/darylldoyle), [@rolf-yoast](https://github.com/rolf-yoast), [@faisal-alvi](https://github.com/faisal-alvi) via [#214](https://github.com/10up/safe-svg/pull/214)).
* **Changed:** Update how image dimensions are passed in `get_image_tag_override` and `one_pixel_fix` methods (props [@gabriel-glo](https://github.com/gabriel-glo), [@jeremymoore](https://github.com/jeremymoore), [@darylldoyle](https://github.com/darylldoyle), [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#216](https://github.com/10up/safe-svg/pull/216)).
* **Changed:** Bump WordPress "tested up to" version to 6.7 (props [@colinswinney](https://github.com/colinswinney), [@jeffpaul](https://github.com/jeffpaul) via [#232](https://github.com/10up/safe-svg/pull/232), [#233](https://github.com/10up/safe-svg/pull/233)).
* **Changed:** Bump WordPress minimum from 6.4 to 6.5 (props [@colinswinney](https://github.com/colinswinney), [@jeffpaul](https://github.com/jeffpaul) via [#232](https://github.com/10up/safe-svg/pull/232), [#233](https://github.com/10up/safe-svg/pull/233)).
* **Changed:** Remove composer dev dependencies from archived project (props [@TylerB24890](https://github.com/TylerB24890), [@szepeviktor](https://github.com/szepeviktor), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#220](https://github.com/10up/safe-svg/pull/220)).
* **Fixed:** Use proper block category for the Safe SVG Icon block (props [@kirtangajjar](https://github.com/kirtangajjar), [@fabiankaegy](https://github.com/fabiankaegy) via [#226](https://github.com/10up/safe-svg/pull/226)).
* **Security:** Only allow SVG file types to be uploaded if our sanitizer is able to run on those files (props [@darylldoyle](https://github.com/darylldoyle), [@xknown](https://github.com/xknown), [@dkotter](https://github.com/dkotter) via [#228](https://github.com/10up/safe-svg/pull/228)).
* **Security:** Bump `webpack` from 5.90.1 to 5.94.0 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#222](https://github.com/10up/safe-svg/pull/222)).
* **Security:** Bump `ws` from 7.5.10 to 8.18.0, `serve-static` from 1.15.0 to 1.16.2 and `express` from 4.19.2 to 4.21.0 (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi) via [#227](https://github.com/10up/safe-svg/pull/227), [#230](https://github.com/10up/safe-svg/pull/230), [#234](https://github.com/10up/safe-svg/pull/234)).

= 2.2.6 - 2024-08-28 =
* **Changed:** Bump WordPress "tested up to" version to 6.6 (props [@sudip-md](https://github.com/sudip-md), [@ankitguptaindia](https://github.com/ankitguptaindia), [@jeffpaul](https://github.com/jeffpaul) via [#212](https://github.com/10up/safe-svg/pull/212), [#213](https://github.com/10up/safe-svg/pull/213)).
* **Changed:** Bump WordPress minimum from 5.7 to 6.4 (props [@sudip-md](https://github.com/sudip-md), [@ankitguptaindia](https://github.com/ankitguptaindia), [@jeffpaul](https://github.com/jeffpaul) via [#212](https://github.com/10up/safe-svg/pull/212), [#213](https://github.com/10up/safe-svg/pull/213)).
* **Security:** Add svg sanitization on the `wp_handle_sideload_prefilter` filter (props [@dkotter](https://github.com/dkotter), [@xknown](https://github.com/xknown), [@iamdharmesh](https://github.com/iamdharmesh) via [GHSA-3vr7-86pg-hf4g](https://github.com/10up/safe-svg/security/advisories/GHSA-3vr7-86pg-hf4g)).
* **Security:** Bump `braces` from 3.0.2 to 3.0.3, `pac-resolver` from 7.0.0 to 7.0.1, `socks` from 2.7.1 to 2.8.3, `ws` from 7.5.9 to 7.5.10 and remove `ip` (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9) via [#206](https://github.com/10up/safe-svg/pull/206)).
* **Security:** Bump `axios` from 1.6.7 to 1.7.4 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#218](https://github.com/10up/safe-svg/pull/218)).

[View historical changelog details here](https://github.com/10up/safe-svg/blob/develop/CHANGELOG.md).

== Upgrade Notice ==

= 2.5.0 =
This is a security release, it is recommended to upgrade immediately.

= 2.3.2 =
Note that this release bumps the WordPress minimum version from 6.5 to 6.6.

= 2.3.0 =
Note that this release bumps the WordPress minimum version from 6.4 to 6.5.

= 2.2.6 =
Note that this release bumps the WordPress minimum version from 5.7 to 6.4.

= 1.9.10 =
Important: bumped the WordPress minimum version from 4.0 to 4.7 and the PHP minimum version from 5.6 to 7.0.
