<?php

/**
 * @package     Page Parts
 * @subpackage  Page Part Templates Class
 *
 * @since  1.3
 */

class Page_Part_Template {

	/**
	 * Post ID
	 *
	 * @since  1.3
	 *
	 * @var  integer
	 */
	private $post_id;

	/**
	 * Constructor
	 *
	 * @since  1.3
	 *
	 * @param  integer  $post_id  Post ID.
	 */
	public function __construct( $post_id ) {

		$this->post_id = absint( $post_id );

	}

	/**
	 * Get Template Slug
	 *
	 * @since  1.3
	 *
	 * @return  string  Template slug.
	 */
	public function get_slug() {

		return Page_Parts::get_page_part_template_slug( $this->post_id );

	}

	/**
	 * Get Validated Slug
	 *
	 * Returns slug if the template is defined and file exists.
	 *
	 * @since  1.3
	 *
	 * @return  string  Template slug.
	 */
	public function get_validated_slug() {

		$template = $this->get_slug();

		if ( ! empty( $template ) ) {

			$templates = new Page_Parts_Templates();
			$templates_data = $templates->get_page_part_templates();

			if ( in_array( $template, array_values( $templates_data ) ) ) {
				return $template;
			}

		}

		return '';

	}

	/**
	 * Get Template Name
	 *
	 * @since  1.3
	 *
	 * @return  string  Template name or slug if template no longer defined.
	 */
	public function get_name() {

		$template = $this->get_slug();

		if ( empty( $template ) ) {
			return '';
		}

		$templates = new Page_Parts_Templates();

		$key = array_search( $template, $templates->get_page_part_templates() );

		if ( false !== $key ) {
			return $key;
		}

		return $template;

	}

	/**
	 * Is Supported?
	 *
	 * Returns true if the template is defined and file exists for the theme.
	 *
	 * @since  1.3
	 *
	 * @return  boolean
	 */
	public function is_supported() {

		return ! empty( $this->get_validated_slug() );

	}

}
