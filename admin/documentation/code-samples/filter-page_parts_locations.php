<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Filter page_parts_locations
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="30" wrap="off" style="width: 100%;" class="code">
&lt;?php

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
