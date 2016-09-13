# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [1.0] - 2016-09-13

### Added
- Add support for Page Part templates.
- Show parent hierarchy in page parts admin.

### Changed
- Improved documentation accessible via the plugins admin page.
- Use `wp_update_post()` when updating `menu_order` via AJAX.

### Fixed
- If page part has no title, show “(no title)” in admin edit list table.

## [0.9] - 2015-09-18

### Changed
- Better handling of default permalinks with anchors (where page part is a child of another page part).

## [0.8] - 2015-09-18

### Added
- Add option to set parent ID manually (if page part is not connected to a post).
- Add page part column to post type admin pages.

### Changed
- Don't show Page Parts meta box in admin nav menus.

### Fixed
- Textdomain should be a string - using a variable causes issues for parsers.

### Security
- Check and escape filtered URLs.

## [0.7] - 2015-02-20

### Added
- Added API to specify theme locations.
- Added theme locations documentation.

### Security
- Tightened up AJAX security with better POST validation and nonces.

## [0.6] - 2014-11-18

### Added
- Add "Add new page part" button on page parts to add a new part to the parent.
- Add support for author, excerpt, custom-fields and revisions.
- Added contextual documentation.
- Added `page-parts` constant.

## [0.5] - 2014-08-28

### Added
- Add plugin documentation (link on plugins page).
- Add `page_parts_supported_post_types` filter to enable support for other post types.
- Added `page_parts_admin_columns` and `page_parts_admin_column_{$column_name}` filters for adding extra columns to the page parts table.

## [0.4] - 2014-07-10

### Added
- Improve drag and drop interface - uses a 'handle' so as to not interfere with links etc.

### Changed
- Admin table displayed using `WP_List_Table` class.

### Fixed
- Order now updated immediate after drag and drop via AJAX.

## [0.3] - 2013-10-30

### Added
- Shows post thumbnail if available.
- Added language support.
- Display page part status in admin list.
- Added `register_page_part_arg`' filter.

## [0.2] - 2012-06-14

### Added
- First public release.

[Unreleased]: https://github.com/benhuson/page-parts/compare/1.0...HEAD
[1.0]: https://github.com/benhuson/page-parts/compare/0.9...1.0
[0.9]: https://github.com/benhuson/page-parts/compare/0.8...0.9
[0.8]: https://github.com/benhuson/page-parts/compare/0.7...0.8
[0.7]: https://github.com/benhuson/page-parts/compare/0.6...0.7
[0.6]: https://github.com/benhuson/page-parts/compare/0.5...0.6
[0.5]: https://github.com/benhuson/page-parts/compare/0.4...0.5
[0.4]: https://github.com/benhuson/page-parts/compare/0.3...0.4
[0.3]: https://github.com/benhuson/page-parts/compare/0.2...0.3
[0.2]: https://github.com/benhuson/page-parts/compare/0.1...0.2