# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/), and will adhere to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - TBD

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
