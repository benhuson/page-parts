<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Templates
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
			<li><a href="#what_are_templates">What are Templates?</a></li>
			<li><a href="#define_a_custom_template">Define a Custom Template</a></li>
			<li><a href="#default_page_part_template">Default Page Part Template</a></li>
			<li><a href="#getting_a_page_part_template">Getting a Page Part Template</a></li>
			<li><a href="#page_parts_default_template_name">Changing the Default Template Name</a></li>
		</ul>
	</div>
</div>

<div style="width: 65%;">

	<div id="what_are_templates">
		<h3>What are Templates?</h3>
		<p>Templates are like WordPress page templates for page parts.</p>
		<p>They are defined in a similar way to WordPress page templates by adding a template namename to the header of the template file (see below).</p>
		<p>Templates are loaded using the <code>Page_Parts::get_page_part_template()</code> function. If no template is set or the template cannot be found then it will try to load a template called <code>page-part.php</code> in the root of your theme. If that does not exist if will default to using some very basic HTML to output the title and content of the page part.</p>
		<p>Page part posts with templates will have the CSS classes <code>page-part-template</code> and <code>page-part-template-{template}</code> applied.</p>
	</div>

	<div id="define_a_custom_template">
		<h3>Define a Custom Template</h3>
		<p>Define your template name by adding a <code>Page Part Name:</code> docblock to the top of your template file. Your template can exist in a sub-folder of your theme, it does not have to be in the root.</p>
		<p>Optionally, you can specify a template image by including the <code>Page Part Image:</code> docblock. This will activate and display the image-based template select admin interface which helps to give a clearer indication to the user of the template layout. The image path should be relative to the theme folder.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/custom-template-header.php' ); ?></p>
	</div>

	<div id="default_page_part_template">
		<h3>Default Page Part Template</h3>
		<p>If a custom template cannot be found for a page part it will fallback to using a <code>page-part.php</code> template file in the root of your theme if it exists.</p>
		<p>If the default page part template file does not exist, it will fallback to using a very simple template built-in to the Page Parts plugin (see code below). It is recommended that you create a <code>page-part.php</code> template in your theme root if you do not want to use this HTML.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/default-page-part-template.php' ); ?></p>
	</div>

	<div id="getting_a_page_part_template">
		<h3>Getting a Page Part Template</h3>
		<p>While looping through a WP_Query for page parts, use <code>Page_Parts::get_page_part_template()</code> to include the page part template for the current page part in the loop.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/page-part-template-loop.php' ); ?></p>
	</div>

	<div id="page_parts_default_template_name">
		<h3>Changing the Default Template Name</h3>
		<p>Use the <code>page_parts_default_template_name</code> filter to change the "Default Template" name.</p>
		<p><?php include( dirname( __FILE__ ) . '/code-samples/filter-page_parts_default_template_name.php' ); ?></p>
	</div>

</div>
