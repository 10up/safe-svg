=== Safe SVG ===
Contributors:      10up, enshrined, jeffpaul
Tags:              svg, security, media, vector, mime
Tested up to:      6.6
Stable tag:        2.2.6
License:           GPL-2.0-or-later
License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html

Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website.

== Description ==

Safe SVG is the best way to Allow SVG Uploads in WordPress!

It gives you the ability to allow SVG uploads whilst making sure that they're sanitized to stop SVG/XML vulnerabilities affecting your site.  It also gives you the ability to preview your uploaded SVGs in the media library in all views.

#### Current Features
* **Sanitised SVGs** - Don't open up security holes in your WordPress site by allowing uploads of unsanitised files.
* **SVGO Optimisation** - Runs your SVGs through the SVGO tool on upload to save you space. This feature is disabled by default but can be enabled by adding the following code: `add_filter( 'safe_svg_optimizer_enabled', '__return_true' );`
* **View SVGs in the Media Library** - Gone are the days of guessing which SVG is the correct one, we'll enable SVG previews in the WordPress media library.
* **Choose Who Can Upload** - Restrict SVG uploads to certain users on your WordPress site or allow anyone to upload.

Initially a proof of concept for [#24251](https://core.trac.wordpress.org/ticket/24251).

SVG Sanitization is done through the following library: [https://github.com/darylldoyle/svg-sanitizer](https://github.com/darylldoyle/svg-sanitizer).

SVG Optimization is done through the following library: [https://github.com/svg/svgo](https://github.com/svg/svgo).

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

== Changelog ==

= 2.2.6 - 2024-08-28 =
* **Changed:** Bump WordPress "tested up to" version to 6.6 (props [@sudip-md](https://github.com/sudip-md), [@ankitguptaindia](https://github.com/ankitguptaindia), [@jeffpaul](https://github.com/jeffpaul) via [#212](https://github.com/10up/safe-svg/pull/212), [#213](https://github.com/10up/safe-svg/pull/213)).
* **Changed:** Bump WordPress minimum from 5.7 to 6.4 (props [@sudip-md](https://github.com/sudip-md), [@ankitguptaindia](https://github.com/ankitguptaindia), [@jeffpaul](https://github.com/jeffpaul) via [#212](https://github.com/10up/safe-svg/pull/212), [#213](https://github.com/10up/safe-svg/pull/213)).
* **Security:** Add svg sanitization on the `wp_handle_sideload_prefilter` filter (props [@dkotter](https://github.com/dkotter), [@xknown](https://github.com/xknown), [@iamdharmesh](https://github.com/iamdharmesh) via [GHSA-3vr7-86pg-hf4g](https://github.com/10up/safe-svg/security/advisories/GHSA-3vr7-86pg-hf4g)).
* **Security:** Bump `braces` from 3.0.2 to 3.0.3, `pac-resolver` from 7.0.0 to 7.0.1, `socks` from 2.7.1 to 2.8.3, `ws` from 7.5.9 to 7.5.10 and remove `ip` (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9) via [#206](https://github.com/10up/safe-svg/pull/206)).
* **Security:** Bump `axios` from 1.6.7 to 1.7.4 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#218](https://github.com/10up/safe-svg/pull/218)).

= 2.2.5 - 2024-06-27 =
* **Added:** New filter, `safe_svg_current_user_can_upload`, allowing more control over who can upload SVG files (props [@dkotter](https://github.com/dkotter), [@iamdharmesh](https://github.com/iamdharmesh) via [#193](https://github.com/10up/safe-svg/pull/193)).
* **Fixed:** Fatal error when applying the `admin_post_thumbnail_html` filter with just two arguments (props [@kmgalanakis](https://github.com/kmgalanakis), [@dkotter](https://github.com/dkotter), [@liz1kiweno](https://github.com/liz1kiweno) via [#196](https://github.com/10up/safe-svg/pull/196)).
* **Fixed:** Prevent PHP fatal error when the value of the filtered block categories is not an array (props [@kmgalanakis](https://github.com/kmgalanakis), [@dkotter](https://github.com/dkotter), [@cguidog](https://github.com/cguidog) via [#200](https://github.com/10up/safe-svg/pull/200)).
* **Fixed:** Handled PHP warning when the `$image_meta` is not an array (props [@faisal-alvi](https://github.com/faisal-alvi), [@dkotter](https://github.com/dkotter), [@drazenbebic](https://github.com/drazenbebic), [@kirtangajjar](https://github.com/kirtangajjar) via [#203](https://github.com/10up/safe-svg/pull/203)).

= 2.2.4 - 2024-03-28 =
* **Changed:** Upgrade the `download-artifact` from v3 to v4 (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul) via [#181](https://github.com/10up/safe-svg/pull/181)).
* **Changed:** Replaced `lee-dohm/no-response` with `actions/stale` to help with closing no-response/stale issues (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#183](https://github.com/10up/safe-svg/pull/183)).
* **Fixed:** Ensure the svg file can be loaded before we try accessing it's attributes (props [@dkotter](https://github.com/dkotter), [@metashield-ie](https://github.com/metashield-ie), [@ocean90](https://github.com/ocean90), [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi) via [#186](https://github.com/10up/safe-svg/pull/186)).
* **Fixed:** Ensure we don't throw JS errors in the Classic Editor when the optimizer feature is turned on (props [@dkotter](https://github.com/dkotter), [@turtlepod](https://github.com/turtlepod), [@faisal-alvi](https://github.com/faisal-alvi) via [#187](https://github.com/10up/safe-svg/pull/187)).
* **Security:** Bump `webpack-dev-middleware` from 5.3.3 to 5.3.4 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#185](https://github.com/10up/safe-svg/pull/185)).
* **Security:** Bump `express` from 4.18.2 to 4.19.2 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#188](https://github.com/10up/safe-svg/pull/188)).

= 2.2.3 - 2024-03-20 =
* **Added:** Support for the WordPress.org plugin preview (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#167](https://github.com/10up/safe-svg/pull/167)).
* **Changed:** Bump WordPress "tested up to" version 6.5 (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#180](https://github.com/10up/safe-svg/pull/180)).
* **Changed:** Clean up NPM dependencies and update node to v20 (props [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#172](https://github.com/10up/safe-svg/pull/172)).
* **Fixed:** Refactor the `svg_dimensions` function to be more performant (props [@sksaju](https://github.com/sksaju), [@cjyabraham](https://github.com/cjyabraham), [@bmarshall511](https://github.com/bmarshall511), [@Hercilio1](https://github.com/Hercilio1), [@darylldoyle](https://github.com/darylldoyle) via [#154](https://github.com/10up/safe-svg/pull/154), [#174](https://github.com/10up/safe-svg/pull/174)).
* **Fixed:** Address fatal JS error when optimization is enabled and an item is published without blocks (props [@psorensen](https://github.com/psorensen), [@tictag](https://github.com/tictag), [@dkotter](https://github.com/dkotter) via [#173](https://github.com/10up/safe-svg/pull/173)).
* **Security:** Bump `axios` from 0.25.0 to 1.6.2 and `@wordpress/scripts` from 26.0.0 to 26.18.0 (props [@dependabot](https://github.com/apps/dependabot), [@ravinderk](https://github.com/ravinderk) via [#166](https://github.com/10up/safe-svg/pull/166)).
* **Security:** Bump `follow-redirects` from 1.15.3 to 1.15.6 and `ip` from 1.1.8 to 1.1.9 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#169](https://github.com/10up/safe-svg/pull/169), [#177](https://github.com/10up/safe-svg/pull/177)).

= 2.2.2 - 2023-11-21 =
* **Changed:** Bump WordPress "tested up to" version 6.4 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/jeffpaul) via [#162](https://github.com/10up/safe-svg/pull/162), [#163](https://github.com/10up/safe-svg/pull/163)).
* **Fixed:** Ensure CSS applies properly to the SVG Icon block when added via `theme.json` (props [@tobeycodes](https://github.com/tobeycodes), [@dkotter](https://github.com/dkotter) via [#161](https://github.com/10up/safe-svg/pull/161)).

= 2.2.1 - 2023-10-23 =
* **Changed:** Update to `apiVersion` 3 for our SVG Icon block (props [@fabiankaegy](https://github.com/fabiankaegy), [@ravinderk](https://github.com/ravinderk), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#133](https://github.com/10up/safe-svg/pull/133)).
* **Fixed:** Address an error due to the SVG Icon block using the `fill-rule` attribute (props [@zamanq](https://github.com/zamanq), [@jeffpaul](https://github.com/jeffpaul), [@iamdharmesh](https://github.com/iamdharmesh) via [#152](https://github.com/10up/safe-svg/pull/152)).
* **Security:** Bump `postcss` from 8.4.20 to 8.4.31 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#155](https://github.com/10up/safe-svg/pull/155)).
* **Security:** Bump `@cypress/request` from 2.88.12 to 3.0.1 and `cypress` from 10.11.0 to 13.3.0 (props [@dependabot](https://github.com/apps/dependabot), [@ravinderk](https://github.com/ravinderk) via [#156](https://github.com/10up/safe-svg/pull/156)).
* **Security:** Bump `@babel/traverse` from 7.20.12 to 7.23.2 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#158](https://github.com/10up/safe-svg/pull/157)).

= 2.2.0 - 2023-08-21 =
* **Added:** New settings that give the ability to select which user roles can upload SVG files (props [@dhanendran](https://github.com/dhanendran), [@csloisel](https://github.com/csloisel), [@faisal-alvi](https://github.com/faisal-alvi), [@dkotter](https://github.com/dkotter) via [#76](https://github.com/10up/safe-svg/pull/76)).
* **Added:** SVG optimization during upload via SVGO. Feature is disabled by default but can be enabled using the `safe_svg_optimizer_enabled` filter (props [@gsarig](https://github.com/gsarig), [@peterwilsoncc](https://github.com/peterwilsoncc), [@Sidsector9](https://github.com/Sidsector9), [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi), [@dkotter](https://github.com/dkotter), [@ravinderk](https://github.com/ravinderk) via [#79](https://github.com/10up/safe-svg/pull/79), [#145](https://github.com/10up/safe-svg/pull/145)).
* **Added:** Spacing and color controls added to SVG block (props [@bmarshall511](https://github.com/bmarshall511), [@iamdharmesh](https://github.com/iamdharmesh) via [#135](https://github.com/10up/safe-svg/pull/135)).
* **Added:** Mochawesome reporter added for Cypress test report (props [@jayedul](https://github.com/jayedul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#124](https://github.com/10up/safe-svg/pull/124)).
* **Changed:** Update [Support Level](https://github.com/10up/safe-svg#support-level) from `Active` to `Stable` (props [@Sidsector9](https://github.com/Sidsector9), [@iamdharmesh](https://github.com/iamdharmesh) via [#100](https://github.com/10up/safe-svg/pull/100)).
* **Changed:** Update name of SVG block from Safe SVG Icon to Inline SVG (props [@bmarshall511](https://github.com/bmarshall511), [@iamdharmesh](https://github.com/iamdharmesh) via [#135](https://github.com/10up/safe-svg/pull/135)).
* **Changed:** Bump WordPress "tested up to" version 6.3 (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#144](https://github.com/10up/safe-svg/pull/144)).
* **Changed:** Update the Dependency Review GitHub Action (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9) via [#128](https://github.com/10up/safe-svg/pull/128)).
* **Fixed:** Add namespace to the `class_exists` check (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#120](https://github.com/10up/safe-svg/pull/120)).
* **Fixed:** Ensure Sanitizer class is properly imported (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#121](https://github.com/10up/safe-svg/pull/121)).
* **Fixed:** Remove an unneeded global (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#122](https://github.com/10up/safe-svg/pull/122)).
* **Fixed:** Use absolute path in require (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#123](https://github.com/10up/safe-svg/pull/123)).
* **Fixed:** Ensure custom classname added to SVG block is output on the front-end (props [@bmarshall511](https://github.com/bmarshall511), [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#130](https://github.com/10up/safe-svg/pull/130)).
* **Fixed:** Ensure `SimpleXML` exists before using it (props [@sdmtt](https://github.com/sdmtt), [@faisal-alvi](https://github.com/faisal-alvi) via [#140](https://github.com/10up/safe-svg/pull/140)).
* **Fixed:** Fix markdown issues in the readme (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#119](https://github.com/10up/safe-svg/pull/119)).
* **Security:** Bump `semver` from 5.7.1 to 5.7.2 (props [@dependabot](https://github.com/apps/dependabot) via [#134](https://github.com/10up/safe-svg/pull/134)).
* **Security:** Bump `word-wrap` from 1.2.3 to 1.2.5 (props [@dependabot](https://github.com/apps/dependabot) via [#141](https://github.com/10up/safe-svg/pull/141)).
* **Security:** Bump `tough-cookie` from 4.1.2 to 4.1.3 and `@cypress/request` from 2.88.10 to 2.88.12 (props [@dependabot](https://github.com/apps/dependabot) via [#146](https://github.com/10up/safe-svg/pull/146)).

[View historical changelog details here](https://github.com/10up/safe-svg/blob/develop/CHANGELOG.md).

== Upgrade Notice ==

= 2.2.6 =
Note that this release bumps the WordPress minimum version from 5.7 to 6.4.

= 1.9.10 =
Important: bumped the WordPress minimum version from 4.0 to 4.7 and the PHP minimum version from 5.6 to 7.0.
