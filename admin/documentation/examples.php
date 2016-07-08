<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Examples
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
		<h3>Examples</h3>
		<ul>
			<li><a href="#register_page_part_args">Alter Registered Post Type Args</a></li>
			<li><a href="#post_part_post_type_link">Change format of page part links</a></li>
			<li><a href="#page_parts_supported_post_types">Add support for additional post types</a></li>
			<li><a href="#page_parts_nested">Nested Page Parts (page parts within page parts)</a></li>
			<li><a href="#page_parts_admin_columns">Add an extra column to the page parts overview table</a></li>
			<li><a href="#page_parts_admin_column_column_name">Handle the output of a column in the page parts overview table</a></li>
			<li><a href="#page_parts_locations">Add Page Part theme locations</a></li>
		</ul>
	</div>
</div>

<div style="width: 65%;">

	<div id="register_page_part_args">
		<h3>Alter Registered Post Type Args</h3>
		<p>Use the <code>register_page_part_args</code> filter to adjust the page parts registered post type args.<br />
			The example below hides the page parts menu from the admin.
		</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-register_page_part_args.php' ); ?></p>
	</div>

	<div id="post_part_post_type_link">
		<h3>Change format of page part links</h3>
		<p>Use the <code>post_part_post_type_link</code> filter to change the format of page part links.<br />
			By default they link to their parent part and append their slug as an anchor, eg. <code>/parent-page/#my-slug</code>.<br />
			The example below instead appends the ID as a query string, eg. eg <code>/parent-page?page-part=123</code>.
		</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-post_part_post_type_link.php' ); ?></p>
	</div>

	<div id="page_parts_supported_post_types">
		<h3>Add support for additional post types</h3>
		<p>Use the <code>page_parts_supported_post_types</code> filter to add support for post types other than pages, or remove support for the page post type. The example below adds support for posts:</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_supported_post_types.php' ); ?></p>
	</div>

	<div id="page_parts_nested">
		<h3>Nested Page Parts (page parts within page parts)</h3>
		<p>It is possible to have have page parts within page parts. You should have to be careful to reset the query correctly after looping through the nested page parts.</p>
		<p>Firstly, add support for nested page parts:</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/nested-page-part-support.php' ); ?></p>
		<p>Then, when looping through nested page parts:</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/nested-page-part-query.php' ); ?></p>
	</div>

	<div id="page_parts_admin_columns">
		<h3>Add an extra column to the page parts overview table</h3>
		<p>The following example uses the <code>page_parts_admin_columns</code> filter to add a page part ID column.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_admin_columns.php' ); ?></p>
	</div>

	<div id="page_parts_admin_column_column_name">
		<h3>Handle the output of a column in the page parts overview table</h3>
		<p>The following example uses the <code>page_parts_admin_column_{$column_name}</code> filter to add the display output for an ID column, as created in the example above.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_admin_column.php' ); ?></p>
	</div>

	<div id="page_parts_locations">
		<h3>Add Page Part Theme Locations</h3>
		<p>The following example uses the <code>page_parts_locations</code> filter to register theme locations for post types. This will add an addition column into the Page Parts table in the admin where you can associate a Page Part with a theme location.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_locations.php' ); ?></p>
	</div>

</div>
