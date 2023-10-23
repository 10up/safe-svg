=== Safe SVG ===
Contributors:      10up, enshrined, jeffpaul
Tags:              svg, sanitize, upload, sanitise, security, svg upload, image, vector, file, graphic, media, mime
Requires at least: 5.7
Tested up to:      6.3
Stable tag:        2.2.1
Requires PHP:      7.4
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website

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

= 2.1.1 - 2023-04-05 =
* **Changed:** Upgrade `@wordpress` npm package dependencies (props [@ggutenberg](https://github.com/ggutenberg), [@Sidsector9](https://github.com/Sidsector9) via [#108](https://github.com/10up/safe-svg/pull/108)).
* **Changed:** Bump WordPress "tested up to" version 6.2 (props [@ggutenberg](https://github.com/ggutenberg), [@Sidsector9](https://github.com/Sidsector9) via [#108](https://github.com/10up/safe-svg/pull/108)).
* **Changed:** Run our E2E tests on the zip generated by "Build release zip" action (props [@jayedul](https://github.com/jayedul), [@dkotter](https://github.com/dkotter) via [#106](https://github.com/10up/safe-svg/pull/106)).
* **Fixed:** Only load our block CSS if a page has the SVG block in it and remove an extra slash in the CSS file path. Remove an unneeded JS block file (props [@dkotter](https://github.com/dkotter), [@freinbichler](https://github.com/freinbichler), [@IanDelMar](https://github.com/IanDelMar), [@ocean90](https://github.com/ocean90), [@Sidsector9](https://github.com/Sidsector9) via [#112](https://github.com/10up/safe-svg/pull/112)).
* **Fixed:** Better error handling for environments that don't match our minimum PHP version (props [@dkotter](https://github.com/dkotter), [@ravinderk](https://github.com/ravinderk) via [#111](https://github.com/10up/safe-svg/pull/111)).

= 2.1.0 - 2023-03-22 =
* **Added:** An SVG Gutenberg Block (props [@faisal-alvi](https://github.com/faisal-alvi), [@Sidsector9](https://github.com/Sidsector9), [@cr0ybot](https://github.com/cr0ybot), [@darylldoyle](https://github.com/darylldoyle), [@cbirdsong](https://github.com/cbirdsong), [@jeffpaul](https://github.com/jeffpaul) via [#80](https://github.com/10up/safe-svg/pull/80)).
* **Added:** "Build release zip" GitHub Action (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@faisal-alvi](https://github.com/faisal-alvi) via [#87](https://github.com/10up/safe-svg/pull/87)).
* **Changed:** Bump minimum PHP version from 7.0 to 7.4 (props [@iamdharmesh](https://github.com/iamdharmesh), [@peterwilsoncc](https://github.com/peterwilsoncc), [@vikrampm1](https://github.com/vikrampm1) via [#82](https://github.com/10up/safe-svg/pull/82)).
* **Changed:** Bump minimum WordPress version from 4.7 to 5.7 (props [@iamdharmesh](https://github.com/iamdharmesh), [@peterwilsoncc](https://github.com/peterwilsoncc), [@vikrampm1](https://github.com/vikrampm1) via [#82](https://github.com/10up/safe-svg/pull/82)).
* **Changed:** Bump WordPress "tested up to" version 6.1 (props [@iamdharmesh](https://github.com/iamdharmesh), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#85](https://github.com/10up/safe-svg/pull/85)).
* **Security:** Updates the underlying sanitisation library to pull in a security fix (props [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi), [@Cyxow](https://github.com/Cyxow) via [#105](https://github.com/10up/safe-svg/pull/105)).
* **Security:** Bump `got` from 10.7.0 to 11.8.5 (props [@dependabot](https://github.com/apps/dependabot) via [#83](https://github.com/10up/safe-svg/pull/83)).
* **Security:** Bump `@wordpress/env from` 4.9.0 to 5.6.0 (props [@dependabot](https://github.com/apps/dependabot) via [#83](https://github.com/10up/safe-svg/pull/83)).
* **Security:** Bump `simple-git` from 3.9.0 to 3.16.0 (props [@dependabot](https://github.com/apps/dependabot) via [#88](https://github.com/10up/safe-svg/pull/88), [#99](https://github.com/10up/safe-svg/pull/99)).
* **Security:** Bump `loader-utils` from 2.0.2 to 2.0.4 (props [@dependabot](https://github.com/apps/dependabot) via [#92](https://github.com/10up/safe-svg/pull/92)).
* **Security:** Bump `json5` from 1.0.1 to 1.0.2 (props [@dependabot](https://github.com/apps/dependabot) via [#91](https://github.com/10up/safe-svg/pull/91)).
* **Security:** Bump `decode-uri-component` from 0.2.0 to 0.2.2 (props [@dependabot](https://github.com/apps/dependabot) via [#93](https://github.com/10up/safe-svg/pull/93)).
* **Security:** Bump `markdown-it` from 12.0.4 to 12.3.2 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#94](https://github.com/10up/safe-svg/pull/94)).
* **Security:** Bump `@wordpress/scripts` from 19.2.4 to 25.1.0 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#94](https://github.com/10up/safe-svg/pull/94)).
* **Security:** Bump `http-cache-semantics` from 4.1.0 to 4.1.1 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#101](https://github.com/10up/safe-svg/pull/101)).
* **Security:** Bump `webpack` from 5.75.0 to 5.76.1 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#103](https://github.com/10up/safe-svg/pull/103)).
* **Security:** Bump `svg-sanitizer` from 0.15.2 to 0.16.0 (props [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi), [@Cyxow](https://github.com/Cyxow) via [#105](https://github.com/10up/safe-svg/pull/105)).

= Earlier versions =
For the changelog of earlier versions, please refer to the [changelog on github.com](https://github.com/10up/safe-svg/blob/develop/CHANGELOG.md).

== Upgrade Notice ==
= 1.9.10 =
* Important: bumped the WordPress minimum version from 4.0 to 4.7 and the PHP minimum version from 5.6 to 7.0.
