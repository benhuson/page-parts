<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Query Page Part Locations
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="12" wrap="off" style="width: 100%;" class="code">
&lt;?php

$page_parts = new WP_Query( array(
	'order'       => 'ASC',
	'orderby'     => 'menu_order',
	'post_parent' => get_the_ID(),
	'post_type'   => 'page-part',
	'meta_key'    => '_page_part_location',
	'meta_value'  => sanitize_key( 'Sidebar' )
) );

?&gt;</textarea>
