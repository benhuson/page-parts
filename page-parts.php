<?php

/*
Plugin Name: Page Parts
Plugin URI: https://github.com/benhuson/page-parts
Description: Manage subsections of a page.
Version: 1.4.1
Author: Ben Huson
Author URI: https://github.com/benhuson
License: GPL2
*/

define( 'PAGE_PARTS_VERSION', '1.4.1' );
define( 'PAGE_PARTS_FILE', __FILE__ );

require_once dirname( PAGE_PARTS_FILE ) . '/includes/class-page-parts.php';
require_once dirname( PAGE_PARTS_FILE ) . '/includes/class-page-part-template.php';
require_once dirname( PAGE_PARTS_FILE ) . '/includes/class-page-parts-templates.php';

global $Page_Parts;
$Page_Parts = new Page_Parts();
