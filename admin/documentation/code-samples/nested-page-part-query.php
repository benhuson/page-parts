<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Nested Page Part Query
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
