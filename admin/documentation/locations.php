<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Locations
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<div class="postbox" style="width: 25%; overflow: auto; float: right; margin: 20px 0 20px;">
	<div class="inside">
		<h3>Filters</h3>
		<ul>
			<li><a href="#what_are_locations">What are locations?</a></li>
			<li><a href="#add_page_part_theme_locations">Add Page Part Theme Locations</a></li>
			<li><a href="#query_page_parts_by_theme_location">Query Page Parts by Theme Location</a></li>
		</ul>
	</div>
</div>

<div style="width: 65%;">

	<div id="what_are_locations">
		<h3>What are Locations?</h3>
		<p>Locations can be used if you want to display page parts in different areas of your templates. For example you may want to add many page parts to a page and show some below the content and some in a sidebar.</p>
		<p>They are similar to how locations are used by WordPress navigation menus - read more about <a href="https://codex.wordpress.org/Navigation_Menus" target="wordpress-org">Navigation Menus</a>.</p>
	</div>

	<div id="add_page_part_theme_locations">
		<h3>Add Page Part Theme Locations</h3>
		<p>The following example uses the <code>page_parts_locations</code> filter to register theme locations for post types. This will add an addition column into the Page Parts table in the admin where you can associate a Page Part with a theme location.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_locations.php' ); ?></p>
	</div>

	<div id="query_page_parts_by_theme_location">
		<h3>Query Page Parts by Theme Location</h3>
		<p>The following example shows how to query all page parts assigned to a location.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/query-page-part-locations.php' ); ?></p>
	</div>

</div>
