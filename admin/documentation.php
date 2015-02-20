
<div class="wrap">
	<div id="icon-tools" class="icon32"></div>
	<h2>Page Parts Documentation</h2>

	<div class="tool-box">
		<h3 class="title">Filters</h3>
		<ul>
			<li><code><a href="#register_page_part_args">register_page_part_args</a></code></li>
			<li><code><a href="#post_part_post_type_link">post_part_post_type_link</a></code></li>
			<li><code><a href="#page_parts_supported_post_types">page_parts_supported_post_types</a></code></li>
			<li><code><a href="#page_parts_admin_columns">page_parts_admin_columns</a></code></li>
			<li><code><a href="#page_parts_admin_column_column_name">page_parts_admin_column_{$column_name}</a></code></li>
			<li><code><a href="#page_parts_locations">page_parts_locations</a></code></li>
		</ul>
		<h3 class="title">How to...</h3>
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

	<div id="register_page_part_args" class="tool-box">
		<h3 class="title">Alter Registered Post Type Args</h3>
		<p>Use the <code>register_page_part_args</code> filter to adjust the page parts registered post type args.<br />
			The example below hides the page parts menu from the admin.
		</p>
		<p>
			<textarea cols="70" rows="19" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Hide Page Parts Admin Menu
 *
 * @param   array  $args  Post type args.
 * @return  array         Args.
 */
function my_register_page_part_args( $args ) {

	$args['show_in_menu'] = false;

	return $args;

}

add_filter( 'register_page_part_args', 'my_register_page_part_args' );

?&gt;</textarea>
		</p>
	</div>

	<div id="post_part_post_type_link" class="tool-box">
		<h3 class="title">Change format of page part links</h3>
		<p>Use the <code>post_part_post_type_link</code> filter to change the format of page part links.<br />
			By default they link to their parent part and append their slug as an anchor, eg. <code>/parent-page/#my-slug</code>.<br />
			The example below instead appends the ID as a query string, eg. eg <code>/parent-page?page-part=123</code>.
		</p>
		<p>
			<textarea cols="70" rows="24" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Change format of page part links
 *
 * @param   string  $post_link  Page Part URL.
 * @param   object  $post       Post object.
 * @param   bool    $leavename  Optional, defaults to false. Whether to keep post name.
 * @param   bool    $sample     Optional, defaults to false. Is it a sample permalink.
 * @return  string              Page Part URL.
 */
function my_post_part_post_type_link( $post_link, $post, $leavename, $sample ) {

	if ( $post->post_parent > 0 ) {
		$post_link = add_query_arg( 'page-part', $post->ID, get_permalink( $post->post_parent ) );
	}

	return $post_link;

}

add_filter( 'post_part_post_type_link', 'my_post_part_post_type_link', 10, 4 );

?&gt;</textarea>
		</p>
	</div>

	<div id="page_parts_supported_post_types" class="tool-box">
		<h3 class="title">Add support for additional post types</h3>
		<p>Use the <code>page_parts_supported_post_types</code> filter to add support for post types other than pages, or remove support for the page post type. The example below adds support for posts:</p>
		<p>
			<textarea cols="70" rows="19" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Add Page Part Support For Posts
 *
 * @param   array  $post_types  Supported post types.
 * @return  array               Post types.
 */
function my_page_parts_supported_post_types( $post_types ) {

	$post_types[] = 'post';

	return $post_types;

}

add_filter( 'page_parts_supported_post_types', 'my_page_parts_supported_post_types' );

?&gt;</textarea>
		</p>
	</div>

	<div id="page_parts_nested" class="tool-box">
		<h3 class="title">Nested Page Parts (page parts within page parts)</h3>
		<p>It is possible to have have page parts within page parts. You should have to be careful to reset the query correctly after looping through the nested page parts.</p>
		<p>Firstly, add support for nested page parts:</p>
		<p>
			<textarea cols="70" rows="19" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Add Nested Page Part Support
 *
 * @param   array  $post_types  Supported post types.
 * @return  array               Post types.
 */
function my_support_nested_page_parts( $post_types ) {

	$post_types[] = 'page-part';

	return $post_types;

}

add_filter( 'page_parts_supported_post_types', 'my_support_nested_page_parts' );

?&gt;</textarea>
		</p>
		<p>Then, when looping through nested page parts:</p>
		<p>
			<textarea cols="70" rows="19" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Display page parts with nested page parts
 */

// Get top level page parts
$page_parts = new WP_Query( array(
	'order'       => 'ASC',
	'orderby'     => 'menu_order',
	'post_type'   => 'page-part',
	'post_parent' => get_the_ID()  // Current post/page ID
) );
if ( $page_parts->have_posts() ) {
	while ( $page_parts->have_posts() ) {
		$page_parts->the_post();

		// Display top level page part details here
		// e.g. the_title();

		// Loop through the current page part's page parts
		$page_parts_sub = new WP_Query( array(
			'order'       => 'ASC',
			'orderby'     => 'menu_order',
			'post_type'   => 'page-part',
			'post_parent' => get_the_ID()  // Current page part ID
		) );
		if ( $page_parts_sub->have_posts() ) {
			while ( $page_parts_sub->have_posts() ) {
				$page_parts_sub->the_post();

				// Display nested page part details here
				// e.g. the_title();

			}

			// After looping through all nested page parts, reset the current post to the current top level page page
			$page_parts->reset_postdata();
		}

	}

	// After looping through all page parts, reset the main query
	wp_reset_postdata();
}

?&gt;</textarea>
		</p>
	</div>

	<div id="page_parts_admin_columns" class="tool-box">
		<h3 class="title">Add an extra column to the page parts overview table</h3>
		<p>The following example uses the <code>page_parts_admin_columns</code> filter to add a page part ID column.</p>
		<p>
			<textarea cols="70" rows="19" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Add a column to display the page part ID
 *
 * @param   array  $columns  Columns.
 * @return  array            Updated columns.
 */
function my_page_parts_admin_columns( $columns ) {

	$columns['id'] = __( 'ID' );

	return $columns;

}

add_filter( 'page_parts_admin_columns', 'my_page_parts_admin_columns' );

?&gt;</textarea>
		</p>
	</div>

	<div id="page_parts_admin_column_column_name" class="tool-box">
		<h3 class="title">Handle the output of a column in the page parts overview table</h3>
		<p>The following example uses the <code>page_parts_admin_column_{$column_name}</code> filter to add the display output for an ID column, as created in the example above.</p>
		<p>
			<textarea cols="70" rows="17" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Add a column to display the page part ID
 *
 * @param   array  $columns  Columns.
 * @return  array            Updated columns.
 */
function my_page_parts_admin_column_id( $value, $item ) {

	return $item->ID;

}

add_filter( 'page_parts_admin_column_id', 'my_page_parts_admin_column_id', 10, 2 );

?&gt;</textarea>
		</p>
	</div>

	<div id="page_parts_locations" class="tool-box">
		<h3 class="title">Add Page Part Theme Locations</h3>
		<p>The following example uses the <code>page_parts_locations</code> filter to register theme locations for post types. This will add an addition column into the Page Parts table in the admin where you can associate a Page Part with a theme location.</p>
		<p>
			<textarea cols="70" rows="30" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Register Page Part Theme Locations
 *
 * @param   array   $locations  Locations for post type.
 * @param   string  $post_type  Post type.
 * @return  array               Locations for post type.
 */
function my_page_parts_locations( $locations, $post_type ) {

	// Page Locations
	if ( 'page' == $post_type ) {
		$locations[] = 'Left';
		$locations[] = 'Bottom';
	}

	// Post Locations
	if ( 'post' == $post_type ) {
		$locations[] = 'Sidebar';
		$locations[] = 'Right';
		$locations[] = 'Bottom';
	}

	return $locations;

}
add_filter( 'page_parts_locations', 'my_page_parts_locations', 10, 2 );

?&gt;</textarea>
		</p>
	</div>

</div>
