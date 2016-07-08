<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Setup
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
		<h3>Getting Started</h3>
		<ul>
			<li><a href="#supported_post_types">1. Supported Post Types</a></li>
			<li><a href="#query_page_parts">2. Query Page Parts</a></li>
			<li><a href="#add_default_template">3. Add Default Template</a></li>
			<li><a href="#add_custom_templates">4. Add Custom Templates</a></li>
			<li><a href="#add_page_part_theme_locations">5. Add Page Part Theme Locations</a></li>
			<li><a href="#further_customization">6. Further Customization</a></li>
		</ul>
	</div>
</div>

<div style="width: 65%;">

	<div id="supported_post_types">
		<h3>1. Supported Post Types</h3>
		<p>By default, page part support is automatically added for pages.</p>
		<p>If you need to add support for other post types or remove support for pages, use the <a href="<?php echo add_query_arg( 'tab', 'examples' ); ?>#page_parts_supported_post_types"><code>page_parts_supported_post_types</code></a> filter.</p>
		<p>Once you have added support for your required post types you will be able to add page parts when editing the post type.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_supported_post_types.php' ); ?></p>
	</div>

	<div id="query_page_parts">
		<h3>2. Query Page Parts</h3>
		<p>To display page parts in a template, use WP_Query to get and <a href="<?php echo add_query_arg( 'tab', 'templates' ); ?>#getting_a_page_part_template">loop through the page parts</a>.</p>
		<p>Use <code>Page_Parts::get_page_part_template()</code> to load the page part template in the loop.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/page-part-template-loop.php' ); ?></p>
	</div>

	<div id="add_default_template">
		<h3>3. Add Default Template <em>(optional)</em></h3>
		<p>The default built-in page part template is very basic, just outputting the title and content.</p>
		<p>It is recommended to add a <a href="<?php echo add_query_arg( 'tab', 'templates' ); ?>#default_page_part_template"><code>page-part.php</code></a> template in the root of your theme so that you can customize the appearance of page parts that do not have a custom template assigned.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/default-page-part-template.php' ); ?></p>
	</div>

	<div id="add_custom_templates">
		<h3>4. Add Custom Templates <em>(optional)</em></h3>
		<p>Define custom templates by adding a <a href="<?php echo add_query_arg( 'tab', 'templates' ); ?>#define_a_custom_template"><code>Page Part Name:</code></a> docblock to the top of your page part template files.</p>
		<p>It is recommended to use the <a href="https://developer.wordpress.org/reference/functions/post_class/" target="wordpress-org"><code>post_class()</code></a> function to add post-specific classes to you page part HTML element. If used, custom page part templates will automatically add the class <code>page-part-template-{filename}</code>. It is recommended to create a <code>page-parts</code> folder in your theme and add page part templates there.</p>
		<p>After defining templates you will be able to assign them to page parts when editing supported post types and page parts.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/custom-template.php' ); ?></p>
	</div>

	<div id="add_page_part_theme_locations">
		<h3>5. Add Page Part Theme Locations <em>(optional)</em></h3>
		<p>If you need to display page parts in multiple places in the same template, you can <a href="<?php echo add_query_arg( 'tab', 'locations' ); ?>">define locations</a> to which you can assign page parts.</p>
	</div>

	<div id="further_customization">
		<h3>6. Further Customization <em>(optional)</em></h3>
		<p>View the documentation about <a href="<?php echo add_query_arg( 'tab', 'filters' ); ?>">filters</a> and other <a href="<?php echo add_query_arg( 'tab', 'examples' ); ?>">examples</a>.</p>
	</div>

</div>
