<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Page Part Template Loop
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="21" wrap="off" style="width: 100%;" class="code">
&lt;?php

// Query page parts
$page_parts = new WP_Query( array(
	'order'          => 'ASC',
	'orderby'        => 'menu_order',
	'post_parent'    => get_queried_object_id(),
	'post_type'      => 'page-part',
	'posts_per_page' => 100 // Be nice to server ;)
) );

// Loop through page parts
if ( $page_parts->have_posts() ) {
	while ( $page_parts->have_posts() ) {
		$page_parts->the_post();
		Page_Parts::get_page_part_template();
	}
	wp_reset_query();
}

?&gt;</textarea>
