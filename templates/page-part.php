<?php

/**
 * @package     Page Parts
 * @subpackage  Page Part Default Template
 *
 * When using Page_Parts::get_page_part_template() to include a
 * Page Part template, if a custom Page Part template cannot
 * be found in the theme it will look for a 'page-part.php' template
 * in the root of the theme.
 *
 * If no valid template is found in the theme then this template
 * will be used as a fallback.
 */

?>

<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-header">
		<h2 class="entry-title"><?php the_title(); ?></h2>
	</div>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
</div>
