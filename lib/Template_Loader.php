<?php
/**
 * Template loader
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

namespace Runthings\TaxonomyTemplateSelector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles template filtering for taxonomy archives
 */
class Template_Loader {

	/**
	 * Initialize template hooks
	 */
	public function __construct() {
		add_filter( 'taxonomy_template', array( $this, 'filter_taxonomy_template' ) );
		add_filter( 'category_template', array( $this, 'filter_taxonomy_template' ) );
	}

	/**
	 * Filter taxonomy template
	 *
	 * @param string $taxonomy_template Current template path.
	 * @return string
	 */
	public function filter_taxonomy_template( $taxonomy_template ) {
		$queried_term = get_queried_object();
		if ( ! $queried_term || ! isset( $queried_term->term_id ) ) {
			return $taxonomy_template;
		}

		$term_id = $queried_term->term_id;
		$template_mappings = get_option( 'runthings_taxonomy_template_mappings' );

		if ( isset( $template_mappings[ $term_id ] ) && 'default' !== $template_mappings[ $term_id ] ) {
			$located_template = locate_template( $template_mappings[ $term_id ] );
			if ( ! empty( $located_template ) ) {
				return apply_filters( 'runthings_taxonomy_template_selector_found', $located_template );
			}
		}

		return $taxonomy_template;
	}
}

