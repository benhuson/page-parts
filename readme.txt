=== Page Parts ===
Contributors: husobj
Tags: pages, cms
Requires at least: 3.9
Tested up to: 6.4.3
Stable tag: 1.4.3
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage subsections of a page. Create 'page parts' as children of a page to display in different areas of your templates. Requires WordPress 3.4.

== Description ==

Manage subsections of a page. Create 'page parts' as children of a page to display in different areas of your templates. Requires WordPress 3.4.

For more information, view the documentation link on the admin plugins page after activating the plugin.

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I order Page Parts? =

Either enter numbers in the order fields or drag them into the order you want.

== Screenshots ==

1. Page Parts meta box.
2. Page Parts meta box with 3 Page Parts.
3. Support for Page Part theme locations (like theme_location for WordPress Menus) and featured images.
4. Page Part URLs are rewritten to their parent page passing the page part slug as an anchor.
5. Page Part meta box with link back to parent page and option to add a new sibling page part.

== Changelog ==

= Unreleased =

= 1.4.3 =

__Fixed__
- Page parts now work with parent set to pending/private/future/trash status.

= 1.4.2 =

__Fixed__
- Fix compatibility with WPML plugin where page part translations are duplicated.

= 1.4.1 =

__Fixed__
- Fix deprecated `contextual_help` implementation.

= 1.4 =

__Added__
- Apply `page_part_theme_templates_depth` filter when getting template images.
- Tested up to WordPress 5.8.2

__Fixed__
- Fix deprecated jQuery ready.

= 1.3.1 =

__Fixed__
- Fix page part permalink when parent is a child of other pages.

= 1.3 =

__Added__
- Add default template image filter `page_part_theme_default_template_image`.
- Allow found templates to be filtered before locating using the `page_part_locate_templates` filter.
- Added `page_part_theme_templates` filter to change the maximum folder depth where page part templates can be found in the theme.
- Add Template column to page parts admin table.

__Changed__
- Search 2 levels deep for Page Part templates in theme folder.

__Fixed__
- Fix revisions not saving.

= 1.2 =

__Added__
* Add `page_part_show_default_template` filter.
* Pass `$page-part` object to `page_part_theme_templates` filter.

__Changed__
* Use __construct() for class constructor methods.

= 1.1 =

__Added__
* Add `page-attributes` meta box to Page Parts (includes "order" field).
* Add `page_parts_default_template_name` filter so that the Default Template name can be changed in admin menus.
* Add `page-part-default` class to page parts with no template assigned.

= 1.0 =

__Added__
* Add support for Page Part templates.
* Show parent hierarchy in page parts admin.

__Changed__
* Improved documentation accessible via the plugins admin page.
* Use wp_update_post() when updating `menu_order` via AJAX.

__Fixed__
* If page part has no title, show “(no title)” in admin edit list table.

= 0.9 =

__Changed__
* Better handling of default permalinks with anchors (where page part is a child of another page part).

= 0.8 =

__Added__
* Add option to set parent ID manually (if page part is not connected to a post).
* Add page part column to post type admin pages.

__Changed__
* Don't show Page Parts meta box in admin nav menus.

__Fixed__
* Textdomain should be a string - using a variable causes issues for parsers.

__Security__
* Check and escape filtered URLs.

= 0.7 =

__Added__
* Added API to specify theme locations.
* Added theme locations documentation.

__Security__
* Tightened up AJAX security with better POST validation and nonces.

= 0.6 =

__Added__
* Add "Add new page part" button on page parts to add a new part to the parent.
* Add support for author, excerpt, custom-fields and revisions.
* Added contextual documentation.
* Added `page-parts` constant.

= 0.5 =

__Added__
* Add plugin documentation (link on plugins page).
* Add `page_parts_supported_post_types` filter to enable support for other post types.
* Added `page_parts_admin_columns` and `page_parts_admin_column_{$column_name}` filters for adding extra columns to the page parts table.

= 0.4 =

__Added__
* Improve drag and drop interface - uses a 'handle' so as to not interfere with links etc.

__Changed__
* Admin table displayed using `WP_List_Table` class.

__Fixed__
* Order now updated immediate after drag and drop via AJAX.

= 0.3 =

__Added__
* Shows post thumbnail if available.
* Added language support.
* Display page part status in admin list.
* Added `register_page_part_args` filter.

= 0.2 =

__Added__
* First public release.
