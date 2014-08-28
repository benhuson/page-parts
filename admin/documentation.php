
<div class="wrap">
	<div id="icon-tools" class="icon32"></div>
	<h2>Page Parts Documentation</h2>

	<div class="tool-box">
		<h3 class="title">Add support for additional post types</h3>
		<p>Use the 'page_parts_supported_post_types' filter to add support for post types other than pages, or remove support for the page post type. The example below adds support for posts:</p>
		<p>
			<textarea cols="70" rows="17" wrap="off" style="width: 100%;" class="code">&lt;?php

/**
 * Add Page Part Support For Posts
 */
function my_page_parts_supported_post_types( $post_types ) {

	$post_types[] = 'post';

	return $post_types;

}

add_filter( 'page_parts_supported_post_types', 'my_page_parts_supported_post_types' );


?&gt;</textarea>
		</p>
	</div>

</div>
