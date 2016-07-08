<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Fiter post_part_post_type_link
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="24" wrap="off" style="width: 100%;" class="code">
&lt;?php

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
		$post_link = esc_url_raw( add_query_arg( 'page-part', $post->ID, get_permalink( $post->post_parent ) ) );
	}

	return $post_link;

}

add_filter( 'post_part_post_type_link', 'my_post_part_post_type_link', 10, 4 );

?&gt;</textarea>
