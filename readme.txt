=== Page Parts ===
Contributors: husobj
Tags: pages, cms
Requires at least: 3.4
Tested up to: 4.1.1
Stable tag: 0.7
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

= 0.7 =
* Added API to specify theme locations.
* Added theme locations documentation.
* Tightened up AJAX security with better POST validation and nonces.

= 0.6 =

* Add "Add new page part" button on page parts to add a new part to the parent.
* Add support for author, excerpt, custom-fields and revisions.
* Added contextual documentation.
* Added PAGE_PARTS_TEXTDOMAIN constant.

= 0.5 =

* Add plugin documentation (link on plugins page).
* Add 'page_parts_supported_post_types' filter to enable support for other post types.
* Added 'page_parts_admin_columns' and 'page_parts_admin_column_{$column_name}' filters for adding extra columns to the page parts table.

= 0.4 =

* Improve drag and drop interface - uses a 'handle' so as to not interfere with links etc.
* Order now updated immediate after drag and drop via AJAX.
* Admin table displayed using WP_List_Table class.

= 0.3 =

* Shows post thumbnail if available.
* Added language support.
* Display page part status in admin list.
* Added 'register_page_part_args' filter.

= 0.2 =

First public release.
