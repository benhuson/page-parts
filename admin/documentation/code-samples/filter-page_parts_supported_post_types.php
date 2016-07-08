<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Nested Page Part Support
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="19" wrap="off" style="width: 100%;" class="code">
&lt;?php

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
