<?php

/**
 * @package     Page Parts
 * @subpackage  Documentation: Code Sample: Default Page Part Template
 *
 * @since  1.0
 */

// Don't allow direct load
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

?>

<textarea cols="70" rows="8" wrap="off" style="width: 100%;" class="code">
<div id="post-&lt;?php the_ID(); ?&gt;" &lt;?php post_class(); ?&gt;>
	<div class="entry-header">
		<h2 class="entry-title">&lt;?php the_title(); ?&gt;</h2>
	</div>
	<div class="entry-content">
		&lt;?php the_content(); ?&gt;
	</div>
</div></textarea>
