# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/), and will adhere to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - TBD

## [2.5.0] - 2026-09-04

### Added
- Link support for the SVG Inline block, including URL input, new tab toggle, and nofollow/sponsored rel options (props [@vegetable-bits](https://github.com/vegetable-bits), [@mgiannopoulos24](https://github.com/mgiannopoulos24), [@jeffpaul](https://github.com/jeffpaul), [@thrijith](https://github.com/thrijith), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter), [@pbiron](https://github.com/pbiron) via [#315](https://github.com/10up/safe-svg/pull/315))
- New `safe_svg_inline_use_shadow_dom` filter to control which inline SVGs are isolated in a shadow root, and new `safe_svg_inline_shadow_styles` filter to adjust the CSS injected alongside them (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328))
- New `safe_svg_remove_remote_references` filter to strip remote `url()`, `@import` and `image-set()` references, along with remote `href` targets, from uploaded SVGs. Off by default, because legitimate SVGs reference remote fonts and images but use this filter to turn it on (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328))

### Changed
- Bump tested up to header to indicate WordPress 6.9 support. (props [@navi151](https://github.com/navi151), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#290](https://github.com/10up/safe-svg/pull/290))
- Indicate support for WordPress 7.0 (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#311](https://github.com/10up/safe-svg/pull/311))
- Theme CSS can no longer target an inline SVG that carries its own `<style>` element, because stylesheets cannot reach into a shadow root. Style those SVGs from within the SVG itself, or opt out with the `safe_svg_inline_use_shadow_dom` filter. Inherited properties, including `color`/`currentColor` and custom properties, still apply as before, and SVGs without a `<style>` element are unaffected (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328))
- Updated blueprint file for WordPress.org live previews. (props [@fellyph](https://github.com/fellyph), [@username2](https://github.com/username2), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#287](https://github.com/10up/safe-svg/pull/287))
- Updated npm dependencies via `npm audit fix` (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#294](https://github.com/10up/safe-svg/pull/294))

### Other
- Changes - Updated `@wordpress/icons` to 11.4.0 (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#294](https://github.com/10up/safe-svg/pull/294))
- Developer - Add Patchstack security-reporting FAQ. (props [@jeffpaul](https://github.com/jeffpaul) via [#295](https://github.com/10up/safe-svg/pull/295))
- Developer - Added docs on recommendation against globally enabling SVG MIME types.  (props [@darylldoyle](https://github.com/darylldoyle), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#302](https://github.com/10up/safe-svg/pull/302))
- Developer - Bumped up Tested up to from 7.0 to 7.1 (props [@zamanq](https://github.com/zamanq), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#320](https://github.com/10up/safe-svg/pull/320))
- Developer - E2E tests: update 10up/cypress-wp-utils to 0.7.2. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#329](https://github.com/10up/safe-svg/pull/329))
- Developer - Introduce tests to ensure the plugin meta is correct. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#317](https://github.com/10up/safe-svg/pull/317))
- Developer - Replaced the `react-svg` dependency with a small internal component, so the block editor preview isolates SVG CSS the same way the front end does (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328))
- Feature - Added support for Enable Media Replace plugin (props [@gthayer](https://github.com/gthayer), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#285](https://github.com/10up/safe-svg/pull/285))

### Security
- Bump basic-ftp from 5.0.5 to 5.2.0 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#308](https://github.com/10up/safe-svg/pull/308))
- Bump lodash from 4.17.21 to 4.17.23 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#298](https://github.com/10up/safe-svg/pull/298))
- Bump lodash-es from 4.17.21 to 4.17.23 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#297](https://github.com/10up/safe-svg/pull/297))
- Bump phpunit/phpunit from 9.6.20 to 9.6.34 in the composer group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#304](https://github.com/10up/safe-svg/pull/304))
- Bump postcss-selector-parser from 6.1.2 to 6.1.4 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#330](https://github.com/10up/safe-svg/pull/330))
- Bump qs from 6.13.0 to 6.14.1 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#292](https://github.com/10up/safe-svg/pull/292))
- Bump qs from 6.14.1 to 6.14.2 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#306](https://github.com/10up/safe-svg/pull/306))
- Bump tar-fs from 3.0.9 to 3.1.1 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot) via [#283](https://github.com/10up/safe-svg/pull/283))
- Bump the npm_and_yarn group across 1 directory with 16 updates (props [@dependabot[bot]](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot), [@jeffpaul](https://github.com/jeffpaul) via [#324](https://github.com/10up/safe-svg/pull/324))
- Bump the npm_and_yarn group across 1 directory with 2 updates (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot) via [#309](https://github.com/10up/safe-svg/pull/309))
- Bump the npm_and_yarn group across 1 directory with 5 updates (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot) via [#291](https://github.com/10up/safe-svg/pull/291))
- Bump webpack from 5.98.0 to 5.105.0 in the npm_and_yarn group across 1 directory (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#305](https://github.com/10up/safe-svg/pull/305))
- Prevented direct access of PHP files. (props [@mehrazmorshed](https://github.com/mehrazmorshed), [@dkotter](https://github.com/dkotter) via [#300](https://github.com/10up/safe-svg/pull/300))
- The Inline SVG block now renders SVGs that carry their own `<style>` element inside a shadow root, so their CSS is scoped to the block instead of applying to the whole page (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#328](https://github.com/10up/safe-svg/pull/328))
- Update PHPCS to resolve OS Command Injection issue GHSA-hmqg-cxww-wqhq (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#322](https://github.com/10up/safe-svg/pull/322))
- Update WPCS to resolve arbitrary code execution issue GHSA-3pwp-g2mj-5p3v (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#322](https://github.com/10up/safe-svg/pull/322))
- Updates the underlying `enshrined/svg-sanitize` package to pull in a security fixes (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#327](https://github.com/10up/safe-svg/pull/327))

## [2.4.0] - 2025-09-22
### Added
- Ability to upload SVGs from more admin locations (props [@stormrockwell](https://github.com/stormrockwell), [@darylldoyle](https://github.com/darylldoyle), [@wpexplorer](https://github.com/wpexplorer), [@smerriman](https://github.com/smerriman), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#279](https://github.com/10up/safe-svg/pull/279)).

### Changed
- Added `$attachment_id` argument to filters `safe_svg_use_width_height_attributes` and `safe_svg_dimensions` (props [@roborourke](https://github.com/roborourke), [@dkotter](https://github.com/dkotter) via [#278](https://github.com/10up/safe-svg/pull/278)).

### Fixed
- Inconsistent or incorrect data type for `$svg` argument in the filters `safe_svg_use_width_height_attributes` and `safe_svg_dimensions` (props [@roborourke](https://github.com/roborourke), [@dkotter](https://github.com/dkotter) via [#278](https://github.com/10up/safe-svg/pull/278)).

## [2.3.3] - 2025-08-13
### Security
- Update the `enshrined/svg-sanitize` package from `0.19.0` to `0.22.0` to fix an issue with case-insensitive attributes slipping through the sanitiser and address PHP 8.4 deprecation warnings (props [@darylldoyle](https://github.com/darylldoyle), [@sudar](https://github.com/sudar), [@georgestephanis](https://github.com/georgestephanis), [@dkotter](https://github.com/dkotter), [@realazizk](https://github.com/realazizk) via [#268](https://github.com/10up/safe-svg/pull/268), [#272](https://github.com/10up/safe-svg/pull/272)).
- Bump `form-data` from 4.0.0 to 4.0.4 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#270](https://github.com/10up/safe-svg/pull/270)).
- Bump `tmp` from 0.2.3 to 0.2.5 and `@inquirer/editor` from 4.2.9 to 4.2.16 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#271](https://github.com/10up/safe-svg/pull/271)).

### Developer
- Configure dependabot to automatically create PR for `enshrined/svg-sanitize` (props [@sudar](https://github.com/sudar), [@georgestephanis](https://github.com/georgestephanis), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#269](https://github.com/10up/safe-svg/pull/269)).
- Fix typo in `dependency-review.yml` (props [@szepeviktor](https://github.com/szepeviktor), [@jeffpaul](https://github.com/jeffpaul) via [#276](https://github.com/10up/safe-svg/pull/276)).

## [2.3.2] - 2025-07-21
**Note that this release bumps the WordPress minimum version from 6.5 to 6.6.**

### Fixed
- Visual parity between the front end and the block editor (props [@s3rgiosan](https://github.com/s3rgiosan), [@dkotter](https://github.com/dkotter) via [#261](https://github.com/10up/safe-svg/pull/261), [#266](https://github.com/10up/safe-svg/pull/266)).

### Changed
- Bump WordPress "tested up to" version 6.8 (props [@godleman](https://github.com/godleman), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#251](https://github.com/10up/safe-svg/pull/251), [#254](https://github.com/10up/safe-svg/pull/254)).
- Bump WordPress minimum supported version to 6.6 (props [@godleman](https://github.com/godleman), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#254](https://github.com/10up/safe-svg/pull/254)).

### Security
- Bump `ws` from 7.5.10 to 8.18.0, `@wordpress/scripts` from 27.9.0 to 30.6.0, `nanoid` from 3.3.7 to 3.3.8 and `mocha` from 10.2.0 to 11.0.1 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#245](https://github.com/10up/safe-svg/pull/245)).
- Bump `@babel/runtime` from 7.23.9 to 7.27.0, `axios` from 1.7.4 to 1.8.4, `cookie` from 0.4.2 to 0.7.1, `express` from 4.21.0 to 4.21.2 and `@wordpress/e2e-test-utils-playwright` from 0.26.0 to 1.20.0 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#250](https://github.com/10up/safe-svg/pull/250)).
- Bump `http-proxy-middleware` from 2.0.6 to 2.0.9 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#253](https://github.com/10up/safe-svg/pull/253)).
- Bump `tar-fs` from 3.0.8 to 3.0.9 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#258](https://github.com/10up/safe-svg/pull/258)).
- Bump `bytes` from 3.0.0 to 3.1.2 and `compression` from 1.7.4 to 1.8.1 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#265](https://github.com/10up/safe-svg/pull/265)).

### Developer
- Update all third-party actions our workflows rely on to use versions based on specific commit hashes (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#248](https://github.com/10up/safe-svg/pull/248)).
- Updated GitHub Action workflow permissions (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#262](https://github.com/10up/safe-svg/pull/262)).

## [2.3.1] - 2024-12-05
### Fixed
- Revert changes made to how we determine custom dimensions for SVGs (props [@dkotter](https://github.com/dkotter), [@martinpl](https://github.com/martinpl), [@subfighter3](https://github.com/subfighter3), [@smerriman](https://github.com/smerriman), [@gigatyrant](https://github.com/gigatyrant), [@jeffpaul](https://github.com/jeffpaul), [@iamdharmesh](https://github.com/iamdharmesh) via [#238](https://github.com/10up/safe-svg/pull/238)).

## [2.3.0] - 2024-11-25
**Note that this release bumps the WordPress minimum version from 6.4 to 6.5.**

### Added
- New setting that allows large SVG files (roughly 10MB or greater) to be uploaded and sanitized properly (props [@kirtangajjar](https://github.com/kirtangajjar), [@faisal-alvi](https://github.com/faisal-alvi), [@darylldoyle](https://github.com/darylldoyle), [@manojsiddoji](https://github.com/manojsiddoji), [@dkotter](https://github.com/dkotter) via [#201](https://github.com/10up/safe-svg/pull/201)).
- New `get_svg_dimensions` function in order to reduce code duplication (props [@gabriel-glo](https://github.com/gabriel-glo), [@jeremymoore](https://github.com/jeremymoore), [@darylldoyle](https://github.com/darylldoyle), [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#216](https://github.com/10up/safe-svg/pull/216)).

### Changed
- Updated the `enshrined/svg-sanitize` package from 0.16.0 to 0.19.0 to fix a PHP 8.3 compatibility issue (props [@sksaju](https://github.com/sksaju), [@TylerB24890](https://github.com/TylerB24890), [@darylldoyle](https://github.com/darylldoyle), [@rolf-yoast](https://github.com/rolf-yoast), [@faisal-alvi](https://github.com/faisal-alvi) via [#214](https://github.com/10up/safe-svg/pull/214)).
- Update how image dimensions are passed in `get_image_tag_override` and `one_pixel_fix` methods (props [@gabriel-glo](https://github.com/gabriel-glo), [@jeremymoore](https://github.com/jeremymoore), [@darylldoyle](https://github.com/darylldoyle), [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter) via [#216](https://github.com/10up/safe-svg/pull/216)).
- Bump WordPress "tested up to" version to 6.7 (props [@colinswinney](https://github.com/colinswinney), [@jeffpaul](https://github.com/jeffpaul) via [#232](https://github.com/10up/safe-svg/pull/232), [#233](https://github.com/10up/safe-svg/pull/233)).
- Bump WordPress minimum from 6.4 to 6.5 (props [@colinswinney](https://github.com/colinswinney), [@jeffpaul](https://github.com/jeffpaul) via [#232](https://github.com/10up/safe-svg/pull/232), [#233](https://github.com/10up/safe-svg/pull/233)).
- Remove composer dev dependencies from archived project (props [@TylerB24890](https://github.com/TylerB24890), [@szepeviktor](https://github.com/szepeviktor), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#220](https://github.com/10up/safe-svg/pull/220)).

### Fixed
- Use proper block category for the Safe SVG Icon block (props [@kirtangajjar](https://github.com/kirtangajjar), [@fabiankaegy](https://github.com/fabiankaegy) via [#226](https://github.com/10up/safe-svg/pull/226)).

### Security
- Only allow SVG file types to be uploaded if our sanitizer is able to run on those files (props [@darylldoyle](https://github.com/darylldoyle), [@xknown](https://github.com/xknown), [@dkotter](https://github.com/dkotter) via [#228](https://github.com/10up/safe-svg/pull/228)).
- Bump `webpack` from 5.90.1 to 5.94.0 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#222](https://github.com/10up/safe-svg/pull/222)).
- Bump `ws` from 7.5.10 to 8.18.0, `serve-static` from 1.15.0 to 1.16.2 and `express` from 4.19.2 to 4.21.0 (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi) via [#227](https://github.com/10up/safe-svg/pull/227), [#230](https://github.com/10up/safe-svg/pull/230), [#234](https://github.com/10up/safe-svg/pull/234)).

### Developer
- Bump `@10up/cypress-wp-utils` from 0.2.0 to 0.4.0, `@wordpress/env` from 9.2.0 to 10.12.0, `cypress` from 13.3.0 to 13.16.0 and `cypress-mochawesome-reporter` from 3.4.0 to 3.8.2. Downgrades `@wordpress/scripts` to 27.9.0. Add additional E2E tests (props [@dkotter](https://github.com/dkotter), [@Lewiscowles1986](https://github.com/Lewiscowles1986) via [#234](https://github.com/10up/safe-svg/pull/234)).
- Update repo badges, add banner image (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#224](https://github.com/10up/safe-svg/pull/224), [#229](https://github.com/10up/safe-svg/pull/229)).

## [2.2.6] - 2024-08-28
**Note that this release bumps the WordPress minimum version from 5.7 to 6.4.**

### Changed
- Bump WordPress "tested up to" version to 6.6 (props [@sudip-md](https://github.com/sudip-md), [@ankitguptaindia](https://github.com/ankitguptaindia), [@jeffpaul](https://github.com/jeffpaul) via [#212](https://github.com/10up/safe-svg/pull/212), [#213](https://github.com/10up/safe-svg/pull/213)).
- Bump WordPress minimum from 5.7 to 6.4 (props [@sudip-md](https://github.com/sudip-md), [@ankitguptaindia](https://github.com/ankitguptaindia), [@jeffpaul](https://github.com/jeffpaul) via [#212](https://github.com/10up/safe-svg/pull/212), [#213](https://github.com/10up/safe-svg/pull/213)).

### Security
- Add svg sanitization on the `wp_handle_sideload_prefilter` filter (props [@dkotter](https://github.com/dkotter), [@xknown](https://github.com/xknown), [@iamdharmesh](https://github.com/iamdharmesh) via [GHSA-3vr7-86pg-hf4g](https://github.com/10up/safe-svg/security/advisories/GHSA-3vr7-86pg-hf4g)).
- Bump `braces` from 3.0.2 to 3.0.3, `pac-resolver` from 7.0.0 to 7.0.1, `socks` from 2.7.1 to 2.8.3, `ws` from 7.5.9 to 7.5.10 and remove `ip` (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9) via [#206](https://github.com/10up/safe-svg/pull/206)).
- Bump `axios` from 1.6.7 to 1.7.4 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#218](https://github.com/10up/safe-svg/pull/218)).

### Developer
- Update repo badges, add WordPress Playground badge (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#217](https://github.com/10up/safe-svg/pull/217)).

## [2.2.5] - 2024-06-27
### Added
- New filter, `safe_svg_current_user_can_upload`, allowing more control over who can upload SVG files (props [@dkotter](https://github.com/dkotter), [@iamdharmesh](https://github.com/iamdharmesh) via [#193](https://github.com/10up/safe-svg/pull/193)).

### Fixed
- Fatal error when applying the `admin_post_thumbnail_html` filter with just two arguments (props [@kmgalanakis](https://github.com/kmgalanakis), [@dkotter](https://github.com/dkotter), [@liz1kiweno](https://github.com/liz1kiweno) via [#196](https://github.com/10up/safe-svg/pull/196)).
- Prevent PHP fatal error when the value of the filtered block categories is not an array (props [@kmgalanakis](https://github.com/kmgalanakis), [@dkotter](https://github.com/dkotter), [@cguidog](https://github.com/cguidog) via [#200](https://github.com/10up/safe-svg/pull/200)).
- Handled PHP warning when the `$image_meta` is not an array (props [@faisal-alvi](https://github.com/faisal-alvi), [@dkotter](https://github.com/dkotter), [@drazenbebic](https://github.com/drazenbebic), [@kirtangajjar](https://github.com/kirtangajjar) via [#203](https://github.com/10up/safe-svg/pull/203)).

### Developer
- Added a "Testing" section in the `CONTRIBUTING.md` file (props [@kmgalanakis](https://github.com/kmgalanakis), [@jeffpaul](https://github.com/jeffpaul) via [#197](https://github.com/10up/safe-svg/pull/197)).
- Added the Repo Automator GitHub Action (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul) via [#198](https://github.com/10up/safe-svg/pull/198)).

## [2.2.4] - 2024-03-28
### Changed
- Upgrade the `download-artifact` from v3 to v4 (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul) via [#181](https://github.com/10up/safe-svg/pull/181)).
- Replaced `lee-dohm/no-response` with `actions/stale` to help with closing no-response/stale issues (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#183](https://github.com/10up/safe-svg/pull/183)).

### Fixed
- Ensure the svg file can be loaded before we try accessing it's attributes (props [@dkotter](https://github.com/dkotter), [@metashield-ie](https://github.com/metashield-ie), [@ocean90](https://github.com/ocean90), [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi) via [#186](https://github.com/10up/safe-svg/pull/186)).
- Ensure we don't throw JS errors in the Classic Editor when the optimizer feature is turned on (props [@dkotter](https://github.com/dkotter), [@turtlepod](https://github.com/turtlepod), [@faisal-alvi](https://github.com/faisal-alvi) via [#187](https://github.com/10up/safe-svg/pull/187)).

### Security
- Bump `webpack-dev-middleware` from 5.3.3 to 5.3.4 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#185](https://github.com/10up/safe-svg/pull/185)).
- Bump `express` from 4.18.2 to 4.19.2 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#188](https://github.com/10up/safe-svg/pull/188)).

## [2.2.3] - 2024-03-20
### Added
- Support for the WordPress.org plugin preview (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#167](https://github.com/10up/safe-svg/pull/167)).

### Changed
- Bump WordPress "tested up to" version 6.5 (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#180](https://github.com/10up/safe-svg/pull/180)).
- Clean up NPM dependencies and update node to v20 (props [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#172](https://github.com/10up/safe-svg/pull/172)).

### Fixed
- Refactor the `svg_dimensions` function to be more performant (props [@sksaju](https://github.com/sksaju), [@cjyabraham](https://github.com/cjyabraham), [@bmarshall511](https://github.com/bmarshall511), [@Hercilio1](https://github.com/Hercilio1), [@darylldoyle](https://github.com/darylldoyle) via [#154](https://github.com/10up/safe-svg/pull/154), [#174](https://github.com/10up/safe-svg/pull/174)).
- Address fatal JS error when optimization is enabled and an item is published without blocks (props [@psorensen](https://github.com/psorensen), [@tictag](https://github.com/tictag), [@dkotter](https://github.com/dkotter) via [#173](https://github.com/10up/safe-svg/pull/173)).

### Security
- Bump `axios` from 0.25.0 to 1.6.2 and `@wordpress/scripts` from 26.0.0 to 26.18.0 (props [@dependabot](https://github.com/apps/dependabot), [@ravinderk](https://github.com/ravinderk) via [#166](https://github.com/10up/safe-svg/pull/166)).
- Bump `follow-redirects` from 1.15.3 to 1.15.6 and `ip` from 1.1.8 to 1.1.9 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#169](https://github.com/10up/safe-svg/pull/169), [#177](https://github.com/10up/safe-svg/pull/177)).

## [2.2.2] - 2023-11-21
### Changed
- Bump WordPress "tested up to" version 6.4 (props [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/jeffpaul) via [#162](https://github.com/10up/safe-svg/pull/162), [#163](https://github.com/10up/safe-svg/pull/163)).

### Fixed
- Ensure CSS applies properly to the SVG Icon block when added via `theme.json` (props [@tobeycodes](https://github.com/tobeycodes), [@dkotter](https://github.com/dkotter) via [#161](https://github.com/10up/safe-svg/pull/161)).

## [2.2.1] - 2023-10-23
### Changed
- Update to `apiVersion` 3 for our SVG Icon block (props [@fabiankaegy](https://github.com/fabiankaegy), [@ravinderk](https://github.com/ravinderk), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#133](https://github.com/10up/safe-svg/pull/133)).

### Fixed
- Address an error due to the SVG Icon block using the `fill-rule` attribute (props [@zamanq](https://github.com/zamanq), [@jeffpaul](https://github.com/jeffpaul), [@iamdharmesh](https://github.com/iamdharmesh) via [#152](https://github.com/10up/safe-svg/pull/152)).

### Security
- Bump `postcss` from 8.4.20 to 8.4.31 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#155](https://github.com/10up/safe-svg/pull/155)).
- Bump `@cypress/request` from 2.88.12 to 3.0.1 and `cypress` from 10.11.0 to 13.3.0 (props [@dependabot](https://github.com/apps/dependabot), [@ravinderk](https://github.com/ravinderk) via [#156](https://github.com/10up/safe-svg/pull/156)).
- Bump `@babel/traverse` from 7.20.12 to 7.23.2 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#158](https://github.com/10up/safe-svg/pull/157)).

## [2.2.0] - 2023-08-21
### Added
- New settings that give the ability to select which user roles can upload SVG files (props [@dhanendran](https://github.com/dhanendran), [@csloisel](https://github.com/csloisel), [@faisal-alvi](https://github.com/faisal-alvi), [@dkotter](https://github.com/dkotter) via [#76](https://github.com/10up/safe-svg/pull/76)).
- SVG optimization during upload via SVGO. Feature is disabled by default but can be enabled using the `safe_svg_optimizer_enabled` filter (props [@gsarig](https://github.com/gsarig), [@peterwilsoncc](https://github.com/peterwilsoncc), [@Sidsector9](https://github.com/Sidsector9), [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi), [@dkotter](https://github.com/dkotter), [@ravinderk](https://github.com/ravinderk) via [#79](https://github.com/10up/safe-svg/pull/79), [#145](https://github.com/10up/safe-svg/pull/145)).
- Spacing and color controls added to SVG block (props [@bmarshall511](https://github.com/bmarshall511), [@iamdharmesh](https://github.com/iamdharmesh) via [#135](https://github.com/10up/safe-svg/pull/135)).
- Mochawesome reporter added for Cypress test report (props [@jayedul](https://github.com/jayedul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#124](https://github.com/10up/safe-svg/pull/124)).

### Changed
- Update [Support Level](https://github.com/10up/safe-svg#support-level) from `Active` to `Stable` (props [@Sidsector9](https://github.com/Sidsector9), [@iamdharmesh](https://github.com/iamdharmesh) via [#100](https://github.com/10up/safe-svg/pull/100)).
- Update name of SVG block from Safe SVG Icon to Inline SVG (props [@bmarshall511](https://github.com/bmarshall511), [@iamdharmesh](https://github.com/iamdharmesh) via [#135](https://github.com/10up/safe-svg/pull/135)).
- Bump WordPress "tested up to" version 6.3 (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#144](https://github.com/10up/safe-svg/pull/144)).
- Update the Dependency Review GitHub Action (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9) via [#128](https://github.com/10up/safe-svg/pull/128)).

### Fixed
- Add namespace to the `class_exists` check (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#120](https://github.com/10up/safe-svg/pull/120)).
- Ensure Sanitizer class is properly imported (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#121](https://github.com/10up/safe-svg/pull/121)).
- Remove an unneeded global (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#122](https://github.com/10up/safe-svg/pull/122)).
- Use absolute path in require (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#123](https://github.com/10up/safe-svg/pull/123)).
- Ensure custom classname added to SVG block is output on the front-end (props [@bmarshall511](https://github.com/bmarshall511), [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#130](https://github.com/10up/safe-svg/pull/130)).
- Ensure `SimpleXML` exists before using it (props [@sdmtt](https://github.com/sdmtt), [@faisal-alvi](https://github.com/faisal-alvi) via [#140](https://github.com/10up/safe-svg/pull/140)).
- Fix markdown issues in the readme (props [@szepeviktor](https://github.com/szepeviktor), [@iamdharmesh](https://github.com/iamdharmesh) via [#119](https://github.com/10up/safe-svg/pull/119)).

### Security
- Bump `semver` from 5.7.1 to 5.7.2 (props [@dependabot](https://github.com/apps/dependabot) via [#134](https://github.com/10up/safe-svg/pull/134)).
- Bump `word-wrap` from 1.2.3 to 1.2.5 (props [@dependabot](https://github.com/apps/dependabot) via [#141](https://github.com/10up/safe-svg/pull/141)).
- Bump `tough-cookie` from 4.1.2 to 4.1.3 and `@cypress/request` from 2.88.10 to 2.88.12 (props [@dependabot](https://github.com/apps/dependabot) via [#146](https://github.com/10up/safe-svg/pull/146)).

## [2.1.1] - 2023-04-05
### Changed
- Upgrade `@wordpress` npm package dependencies (props [@ggutenberg](https://github.com/ggutenberg), [@Sidsector9](https://github.com/Sidsector9) via [#108](https://github.com/10up/safe-svg/pull/108)).
- Bump WordPress "tested up to" version 6.2 (props [@ggutenberg](https://github.com/ggutenberg), [@Sidsector9](https://github.com/Sidsector9) via [#108](https://github.com/10up/safe-svg/pull/108)).
- Run our E2E tests on the zip generated by "Build release zip" action (props [@jayedul](https://github.com/jayedul), [@dkotter](https://github.com/dkotter) via [#106](https://github.com/10up/safe-svg/pull/106)).

### Fixed
- Only load our block CSS if a page has the SVG block in it and remove an extra slash in the CSS file path. Remove an unneeded JS block file (props [@dkotter](https://github.com/dkotter), [@freinbichler](https://github.com/freinbichler), [@IanDelMar](https://github.com/IanDelMar), [@ocean90](https://github.com/ocean90), [@Sidsector9](https://github.com/Sidsector9) via [#112](https://github.com/10up/safe-svg/pull/112)).
- Better error handling for environments that don't match our minimum PHP version (props [@dkotter](https://github.com/dkotter), [@ravinderk](https://github.com/ravinderk) via [#111](https://github.com/10up/safe-svg/pull/111)).

## [2.1.0] - 2023-03-22
### Added
- An SVG Gutenberg Block (props [@faisal-alvi](https://github.com/faisal-alvi), [@Sidsector9](https://github.com/Sidsector9), [@cr0ybot](https://github.com/cr0ybot), [@darylldoyle](https://github.com/darylldoyle), [@cbirdsong](https://github.com/cbirdsong), [@jeffpaul](https://github.com/jeffpaul) via [#80](https://github.com/10up/safe-svg/pull/80)).
- "Build release zip" GitHub Action (props [@iamdharmesh](https://github.com/iamdharmesh), [@dkotter](https://github.com/dkotter), [@faisal-alvi](https://github.com/faisal-alvi) via [#87](https://github.com/10up/safe-svg/pull/87)).

### Changed
- Bump minimum PHP version from 7.0 to 7.4 (props [@iamdharmesh](https://github.com/iamdharmesh), [@peterwilsoncc](https://github.com/peterwilsoncc), [@vikrampm1](https://github.com/vikrampm1) via [#82](https://github.com/10up/safe-svg/pull/82)).
- Bump minimum WordPress version from 4.7 to 5.7 (props [@iamdharmesh](https://github.com/iamdharmesh), [@peterwilsoncc](https://github.com/peterwilsoncc), [@vikrampm1](https://github.com/vikrampm1) via [#82](https://github.com/10up/safe-svg/pull/82)).
- Bump WordPress "tested up to" version 6.1 (props [@iamdharmesh](https://github.com/iamdharmesh), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#85](https://github.com/10up/safe-svg/pull/85)).

### Security
- Updates the underlying sanitisation library to pull in a security fix (props [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi), [@Cyxow](https://github.com/Cyxow) via [#105](https://github.com/10up/safe-svg/pull/105)).
- Bump `got` from 10.7.0 to 11.8.5 (props [@dependabot](https://github.com/apps/dependabot) via [#83](https://github.com/10up/safe-svg/pull/83)).
- Bump `@wordpress/env from` 4.9.0 to 5.6.0 (props [@dependabot](https://github.com/apps/dependabot) via [#83](https://github.com/10up/safe-svg/pull/83)).
- Bump `simple-git` from 3.9.0 to 3.16.0 (props [@dependabot](https://github.com/apps/dependabot) via [#88](https://github.com/10up/safe-svg/pull/88), [#99](https://github.com/10up/safe-svg/pull/99)).
- Bump `loader-utils` from 2.0.2 to 2.0.4 (props [@dependabot](https://github.com/apps/dependabot) via [#92](https://github.com/10up/safe-svg/pull/92)).
- Bump `json5` from 1.0.1 to 1.0.2 (props [@dependabot](https://github.com/apps/dependabot) via [#91](https://github.com/10up/safe-svg/pull/91)).
- Bump `decode-uri-component` from 0.2.0 to 0.2.2 (props [@dependabot](https://github.com/apps/dependabot) via [#93](https://github.com/10up/safe-svg/pull/93)).
- Bump `markdown-it` from 12.0.4 to 12.3.2 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#94](https://github.com/10up/safe-svg/pull/94)).
- Bump `@wordpress/scripts` from 19.2.4 to 25.1.0 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#94](https://github.com/10up/safe-svg/pull/94)).
- Bump `http-cache-semantics` from 4.1.0 to 4.1.1 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#101](https://github.com/10up/safe-svg/pull/101)).
- Bump `webpack` from 5.75.0 to 5.76.1 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#103](https://github.com/10up/safe-svg/pull/103)).
- Bump `svg-sanitizer` from 0.15.2 to 0.16.0 (props [@darylldoyle](https://github.com/darylldoyle), [@faisal-alvi](https://github.com/faisal-alvi), [@Cyxow](https://github.com/Cyxow) via [#105](https://github.com/10up/safe-svg/pull/105)).

## [2.0.3] - 2022-09-01
### Added
- More robust PHP testing (props [@iamdharmesh](https://github.com/iamdharmesh), [@faisal-alvi](https://github.com/faisal-alvi) via [#71](https://github.com/10up/safe-svg/pull/71), [#73](https://github.com/10up/safe-svg/pull/73)).

### Fixed
- Addressed PHPCS errors (props [@iamdharmesh](https://github.com/iamdharmesh), [@faisal-alvi](https://github.com/faisal-alvi) via [#73](https://github.com/10up/safe-svg/pull/73)).

## [2.0.2] - 2022-06-27
### Added
- Dependency security scanning (props [@jeffpaul](https://github.com/jeffpaul) via [#60](https://github.com/10up/safe-svg/pull/60)).
- End-to-end testing with Cypress (props [@iamdharmesh](https://github.com/iamdharmesh) via [#64](https://github.com/10up/safe-svg/pull/64)).

### Changed
- Bump WordPress version "tested up to" 6.0 (props [@dkotter](https://github.com/dkotter) via [#65](https://github.com/10up/safe-svg/issues/65)).

### Removed
- Redundant premium version upgrade link (props [@ocean90](https://github.com/ocean90), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#61](https://github.com/10up/safe-svg/pull/61)).
- Unneeded admin CSS fix for featured images (props [@AdamWills](https://github.com/AdamWills), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#63](https://github.com/10up/safe-svg/pull/63)).

## [2.0.1] - 2022-04-19
### Changed
- Documentation updates (props [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#50](https://github.com/10up/safe-svg/pull/50)).

### Fixed
- Ensure our height and width attributes are set before using them (props [@dkotter](https://github.com/dkotter), [@r8r](https://github.com/r8r), [@jerturowetz](https://github.com/jerturowetz), [@cadic](https://github.com/cadic) via [#51](https://github.com/10up/safe-svg/pull/51))
- Support for installing via packagist.org (props [@roborourke](https://github.com/roborourke), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#52](https://github.com/10up/safe-svg/pull/52)).

## [2.0.0] - 2022-04-06
### Added
- New filter, `safe_svg_use_width_height_attributes`, that can be used to change the order of attributes we use to determine the SVG dimensions (props [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#43](https://github.com/10up/safe-svg/pull/43)).

### Changed
- Documentation updates (props [@j-hoffmann](https://github.com/j-hoffmann), [@jeffpaul](https://github.com/jeffpaul), [@Zodiac1978](https://github.com/Zodiac1978) via [#39](https://github.com/10up/safe-svg/pull/39), [#42](https://github.com/10up/safe-svg/pull/42)).

### Fixed
- Use the `viewBox` attributes first for image dimensions. Ensure we don't use image dimensions that end with percent signs (props [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#43](https://github.com/10up/safe-svg/pull/43)).
- Make sure we use the full size SVG dimensions rather than the requested size, to avoid wrong sizes being used and duplicate height and width attributes (props [@dkotter](https://github.com/dkotter), [@cadic](https://github.com/cadic) via [#44](https://github.com/10up/safe-svg/pull/44)).
- Ensure the `tmp_name` and `name` properties exist before we use them (props [@dkotter](https://github.com/dkotter), [@aksld](https://github.com/aksld) via [#46](https://github.com/10up/safe-svg/pull/46)).

## [1.9.10] - 2022-02-23
**Note that this release bumps the WordPress minimum version from 4.0 to 4.7 and the PHP minimum version from 5.6 to 7.0.**

### Changed
- Bump WordPress minimum version from 4.0 to 4.7 (props [@cadic](https://github.com/cadic) via [#32](https://github.com/10up/safe-svg/pull/32)).
- Bump PHP minimum version from 5.6 to 7.0 (props [@mehidi258](https://github.com/mehidi258), [@iamdharmesh](https://github.com/iamdharmesh), [@amdd-tim](https://github.com/amdd-tim), [@darylldoyle](https://github.com/darylldoyle), [@jeffpaul](https://github.com/jeffpaul) via [#20](https://github.com/10up/safe-svg/pull/20)).
- Update `enshrined/svg-sanitize` from 0.13.3 to 0.15.2 (props [@mehidi258](https://github.com/mehidi258), [@iamdharmesh](https://github.com/iamdharmesh), [@amdd-tim](https://github.com/amdd-tim), [@darylldoyle](https://github.com/darylldoyle), [@jeffpaul](https://github.com/jeffpaul), [@cadic](https://github.com/cadic) via [#20](https://github.com/10up/safe-svg/pull/20), [#29](https://github.com/10up/safe-svg/pull/29)).
- Bump WordPress version "tested up to" 5.9 (props [@BBerg10up](https://github.com/BBerg10up), [@jeffpaul](https://github.com/jeffpaul), [@cadic](https://github.com/cadic) via [#14](https://github.com/10up/safe-svg/pull/14), [#27](https://github.com/10up/safe-svg/pull/27)).
- Updated library location and added a new build step (props [@darylldoyle](https://github.com/darylldoyle), [@dkotter](https://github.com/dkotter) via [#35](https://github.com/10up/safe-svg/pull/35), [#36](https://github.com/10up/safe-svg/pull/36)).
- Updated plugin assets and added docs and repo management workflows via GitHub Actions (props [Brooke Campbell](https://www.linkedin.com/in/brookecampbelldesign/), [@jeffpaul](https://github.com/jeffpaul) via [#16](https://github.com/10up/safe-svg/pull/16), [#26](https://github.com/10up/safe-svg/pull/26)).

### Fixed
- Double slash being added in SVG file URL for newer uploads (props [@mehulkaklotar](https://github.com/mehulkaklotar), [@smerriman](https://github.com/smerriman) via [#19](https://github.com/10up/safe-svg/pull/19)).
- Float value casting for SVGs when fetching width and height (props [@mehulkaklotar](https://github.com/mehulkaklotar), [@smerriman](https://github.com/smerriman) via [#19](https://github.com/10up/safe-svg/pull/19)).
- Use calculated size for SVGs instead of using `false` (props [@dkotter](https://github.com/dkotter), [@darylldoyle](https://github.com/darylldoyle), [@fritteli](https://github.com/fritteli) via [#23](https://github.com/10up/safe-svg/pull/23)).
- Add better file type checking when looking for SVG files (props [@davidhamann](https://github.com/davidhamann), [@dkotter](https://github.com/dkotter), [@darylldoyle](https://github.com/darylldoyle) via [#28](https://github.com/10up/safe-svg/pull/28)).

## [1.9.9] - 2020-05-07
### Fixed
- Issue where 100% width is accidentally converted to 100px width (props [@joehoyle](https://github.com/joehoyle)).

## [1.9.8] - 2020-05-07
### Changed
- Underlying library update.

## [1.9.7] - 2019-12-10
### Changed
- Underlying library update.

## [1.9.6] - 2019-11-07
### Security
- Underlying library update that fixes a security issue.

## [1.9.5] - 2019-11-04
### Security
- Underlying library update that fixes some security issues.

## [1.9.4] - 2019-08-21
### Fixed
- Bug causing lots of error log output to do with `safe_svg::fix_direct_image_output()`.

## [1.9.3] - 2019-02-19
### Fixed
- Bug causing 0 height and width SVGs.

## [1.9.2] - 2019-02-14
### Fixed
- Warning about an Illegal string offset.
- Issue if something other than a WP_Post object is passed in via the `wp_get_attachment_image_attributes` filter.

## [1.9.1] - 2019-01-29
### Fixed
- Warning that was being generated by a change made in 1.9.0.

## [1.9.0] - 2019-01-03
### Changed
- If an image is the correct ratio, allow skipping of the crop popup when setting header/logo images with SVGs.

## [1.8.1] - 2018-11-22
### Changed
- Don't let errors break upload if uploading an empty file.

### Fixed
- Featured image display in Gutenberg. Props [@dmhendricks](https://github.com/dmhendricks) :)

## [1.8.0] - 2018-11-04
### Added
- Pull SVG dimensions from the width/height or viewbox attributes of the SVG.
- role="img" attribute to SVGs.

## [1.7.1] - 2018-10-01
### Changed
- Underlying lib and added new filters for filtering allowed tags and attributes.

## [1.7.0] - 2018-10-01
### Added
- Allow devs to filter tags and attrs within WordPress.

## [1.6.1] - 2018-03-17
### Changed
- Images will now use the size chosen when inserted into the page rather than default to 2000px everytime.

## [1.6.0] - 2017-12-20
### Added
- Fairly big new feature - The library now allows `<use>` elements as long as they don't reference external files!

### Fixed
- You can now also embed safe image types within the SVG and not have them stripped (PNG, GIF, JPG).

## [1.5.3] - 2017-11-16
### Fixed
- 1.5.2 introduced an issue that can freeze the media library. This fixes that issue. Sorry!

## [1.5.2] - 2017-11-15
### Changed
- Tested with 4.9.0.

### Fixed
- Issue with SVGs when regenerating media.

## [1.5.1] - 2017-08-21
### Fixed
- PHP strict standards warning.

## [1.5.0] - 2017-06-20
### Changed
- Library update.
- role, aria- and data- attributes are now whitelisted to improve accessibility.

## [1.4.5] - 2017-06-18
### Changed
- Library update.

### Fixed
- Issues with defining the size of an SVG.

## [1.4.4] - 2017-06-07
### Fixed
- SVGs now display as featured images in the admin area.

## [1.4.3] - 2017-03-06
### Added
- WordPress 4.7.3 Compatibility.

### Changed
- Expanded SVG previews in media library.

## [1.4.2] - 2017-02-26
### Added
- Check / fix for when mb_* functions are not available.

## [1.4.1] - 2017-02-23
### Changed
- Underlying library to allow attributes/tags in all case variations.

## [1.4.0] - 2017-02-21
### Added
- Ability to preview SVG on both grid and list view in the wp-admin media area.

### Changed
- Underlying library version.

## [1.3.4] - 2017-02-20
### Fixed
- SVGZ uploads failing and not sanitising correctly.

## [1.3.3] - 2017-02-15
### Changed
- Allow SVGZ uploads.

## [1.3.2] - 2017-01-27
### Fixed
- Mime type issue in 4.7.1. Mad props to [@LewisCowles1986](https://github.com/LewisCowles1986).

## [1.3.1] - 2016-12-01
### Changed
- Underlying library version.

## [1.3.0] - 2016-10-10
### Changed
- Minify SVGs after cleaning so they can be loaded correctly through `file_get_contents`.

## [1.2.0] - 2016-02-27
### Added
- Support for camel case attributes such as viewBox.

## [1.1.1] - 2016-07-06
### Fixed
- Issue with empty svg elements self-closing.

## [1.1.0] - 2015-07-04
### Added
- I18n.
- da, de, en, es, fr, nl, and ru translations.

### Fixed
- Issue with filename not being pulled over on failed uploads.

## [1.0.0] - 2015-07-03
- Initial Release.

[Unreleased]: https://github.com/10up/safe-svg/compare/trunk...develop
[2.5.0]: https://github.com/10up/safe-svg/compare/2.4.0...2.5.0
[2.4.0]: https://github.com/10up/safe-svg/compare/2.3.3...2.4.0
[2.3.3]: https://github.com/10up/safe-svg/compare/2.3.2...2.3.3
[2.3.2]: https://github.com/10up/safe-svg/compare/2.3.1...2.3.2
[2.3.1]: https://github.com/10up/safe-svg/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/10up/safe-svg/compare/2.2.6...2.3.0
[2.2.6]: https://github.com/10up/safe-svg/compare/2.2.5...2.2.6
[2.2.5]: https://github.com/10up/safe-svg/compare/2.2.4...2.2.5
[2.2.4]: https://github.com/10up/safe-svg/compare/2.2.3...2.2.4
[2.2.3]: https://github.com/10up/safe-svg/compare/2.2.2...2.2.3
[2.2.2]: https://github.com/10up/safe-svg/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/10up/safe-svg/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/10up/safe-svg/compare/2.1.1...2.2.0
[2.1.1]: https://github.com/10up/safe-svg/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/10up/safe-svg/compare/2.0.3...2.1.0
[2.0.3]: https://github.com/10up/safe-svg/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/10up/safe-svg/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/10up/safe-svg/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/10up/safe-svg/compare/1.9.10...2.0.0
[1.9.10]: https://github.com/10up/safe-svg/compare/1.9.9...1.9.10
[1.9.9]: https://github.com/10up/safe-svg/compare/1.9.8...1.9.9
[1.9.8]: https://github.com/10up/safe-svg/compare/1.9.7...1.9.8
[1.9.7]: https://github.com/10up/safe-svg/compare/1.9.6...1.9.7
[1.9.6]: https://github.com/10up/safe-svg/compare/1.9.5...1.9.6
[1.9.5]: https://github.com/10up/safe-svg/compare/1.9.4...1.9.5
[1.9.4]: https://github.com/10up/safe-svg/compare/1.9.3...1.9.4
[1.9.3]: https://github.com/10up/safe-svg/tree/1.9.3
[1.9.2]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=2030675
[1.9.1]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=2020831
[1.9.0]: https://github.com/10up/safe-svg/compare/1.8.1...1.9.0
[1.8.1]: https://github.com/10up/safe-svg/tree/1.8.1
[1.8.0]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1968505
[1.7.1]: https://github.com/10up/safe-svg/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/10up/safe-svg/compare/1.6.1...1.7.0
[1.6.1]: https://github.com/10up/safe-svg/tree/1.6.1
[1.6.0]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1790304
[1.5.3]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1767971
[1.5.2]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1767107
[1.5.1]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1717074
[1.5.0]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1682064
[1.4.5]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1680702
[1.4.4]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1672159
[1.4.3]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1609079
[1.4.2]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1603943
[1.4.1]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1602282
[1.4.0]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1600797
[1.3.4]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1600043
[1.3.3]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1596667
[1.3.2]: https://plugins.trac.wordpress.org/browser/safe-svg/trunk?rev=1583740
[1.3.1]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1544361
[1.3.0]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1511922
[1.2.0]: https://plugins.trac.wordpress.org/browser/safe-svg?rev=1359493
[1.1.1]: https://plugins.trac.wordpress.org/browser/safe-svg/trunk?rev=1193752
[1.1.0]: https://github.com/10up/safe-svg/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/10up/safe-svg/tree/1.0.0
