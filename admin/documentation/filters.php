<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Filters
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
			<li><code><a href="#register_page_part_args">register_page_part_args</a></code></li>
			<li><code><a href="#post_part_post_type_link">post_part_post_type_link</a></code></li>
			<li><code><a href="#page_parts_supported_post_types">page_parts_supported_post_types</a></code></li>
			<li><code><a href="#page_parts_admin_columns">page_parts_admin_columns</a></code></li>
			<li><code><a href="#page_parts_admin_column_column_name">page_parts_admin_column_{$column_name}</a></code></li>
			<li><code><a href="#page_parts_locations">page_parts_locations</a></code></li>
			<li><code><a href="#page_parts_default_template_name">page_parts_default_template_name</a></code></li>
		</ul>
	</div>
</div>

<div style="width: 65%;">

	<div id="register_page_part_args">
		<h3>register_page_part_args</h3>
		<p>Use the <code>register_page_part_args</code> filter to adjust the page parts registered post type args.</p>
		<h4>Parameters</h4>
		<ul>
			<li><code>$args</code> <em>(array)</em> Register post type args.</li>
		</ul>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-register_page_part_args.php' ); ?></p>
	</div>

	<div id="post_part_post_type_link">
		<h3>post_part_post_type_link</h3>
		<p>Use the <code>post_part_post_type_link</code> filter to change the format of page part links.</p>
		<h4>Parameters</h4>
		<ul>
			<li><code>$post_link</code> <em>(string)</em> Page Part URL.</li>
			<li><code>$post</code> <em>(WP_Post)</em> Post object.</li>
			<li><code>$leavename</code> <em>(bool)</em> Optional, defaults to false. Whether to keep post name.</li>
			<li><code>$sample</code> <em>(bool)</em> Optional, defaults to false. Is it a sample permalink.</li>
		</ul>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-post_part_post_type_link.php' ); ?></p>
	</div>

	<div id="page_parts_supported_post_types">
		<h3>page_parts_supported_post_types</h3>
		<p>Use the <code>page_parts_supported_post_types</code> filter to add support for post types other than pages, or remove support for the page post type.</p>
		<h4>Parameters</h4>
		<ul>
			<li><code>$post_types</code> <em>(array)</em> Supported post types.</li>
		</ul>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_supported_post_types.php' ); ?></p>
	</div>

	<div id="page_parts_admin_columns">
		<h3>page_parts_admin_columns</h3>
		<p>Use the <code>page_parts_admin_columns</code> filter to add a page part table column.</p>
		<h4>Parameters</h4>
		<ul>
			<li><code>$columns</code> <em>(array)</em> Columns.</li>
		</ul>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_admin_columns.php' ); ?></p>
	</div>

	<div id="page_parts_admin_column_column_name">
		<h3>page_parts_admin_column_{$column_name}</h3>
		<p>Use the <code>page_parts_admin_column_{$column_name}</code> filter to add the display output for an page part table column.</p>
		<h4>Parameters</h4>
		<ul>
			<li><code>$value</code> <em>(string)</em> HTML output.</li>
			<li><code>$item</code> <em>(WP_Post)</em> Page part post object.</li>
		</ul>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_admin_column.php' ); ?></p>
	</div>

	<div id="page_parts_locations">
		<h3>page_parts_locations</h3>
		<p>Use the <code>page_parts_locations</code> filter to register theme locations for post types. This will add an addition column into the Page Parts table in the admin where you can associate a Page Part with a theme location.</p>
		<h4>Parameters</h4>
		<ul>
			<li><code>$locations</code> <em>(array)</em> Locations for post type.</li>
			<li><code>$post_type</code> <em>(string)</em> Post type.</li>
		</ul>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_locations.php' ); ?></p>
	</div>

	<div id="page_parts_default_template_name">
		<h3>page_parts_default_template_name</h3>
		<p>Use the <code>page_parts_default_template_name</code> filter to change the "Default Template" name.</p>
		<h4>Parameters</h4>
		<ul>
			<li><code>$name</code> <em>(string)</em> Default Template name.</li>
		</ul>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_default_template_name.php' ); ?></p>
	</div>

</div>
