<?php
/**
 * Admin column handler
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

namespace Runthings\TaxonomyTemplateSelector\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles template column in taxonomy list tables
 */
class Column {

	/**
	 * Admin instance for template retrieval
	 *
	 * @var \Runthings\TaxonomyTemplateSelector\Admin
	 */
	private $admin;

	/**
	 * Constructor
	 *
	 * @param \Runthings\TaxonomyTemplateSelector\Admin $admin Admin instance.
	 */
	public function __construct( $admin ) {
		$this->admin = $admin;
	}

	/**
	 * Register hooks for a taxonomy
	 *
	 * @param string $taxonomy_name Taxonomy name.
	 * @return void
	 */
	public function register_hooks( $taxonomy_name ) {
		add_filter( 'manage_edit-' . $taxonomy_name . '_columns', array( $this, 'add_template_column' ) );
		add_filter( 'manage_' . $taxonomy_name . '_custom_column', array( $this, 'render_template_column' ), 10, 3 );
		add_filter( 'default_hidden_columns', array( $this, 'hide_template_column_by_default' ), 10, 2 );
	}

	/**
	 * Add template column to taxonomy list table
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_template_column( $columns ) {
		$columns['taxonomy_template'] = __( 'Template', 'runthings-taxonomy-template-selector' );
		return $columns;
	}

	/**
	 * Render template column content
	 *
	 * @param string $content Column content.
	 * @param string $column_name Column name.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public function render_template_column( $content, $column_name, $term_id ) {
		if ( 'taxonomy_template' !== $column_name ) {
			return $content;
		}

		$template_mappings = get_option( 'runthings_taxonomy_template_selector_mappings' );
		if ( ! is_array( $template_mappings ) || ! isset( $template_mappings[ $term_id ] ) ) {
			return '—';
		}

		$template_file = $template_mappings[ $term_id ];
		if ( 'default' === $template_file || empty( $template_file ) ) {
			return '—';
		}

		// Try to get display name from templates list.
		$templates = $this->admin->get_taxonomy_templates();
		$display_name = array_search( $template_file, $templates, true );

		return esc_html( $display_name ? $display_name : $template_file );
	}

	/**
	 * Hide template column by default
	 *
	 * @param array     $hidden Array of hidden column names.
	 * @param \WP_Screen $screen Current screen object.
	 * @return array
	 */
	public function hide_template_column_by_default( $hidden, $screen ) {
		if ( isset( $screen->taxonomy ) && ! empty( $screen->taxonomy ) ) {
			$hidden[] = 'taxonomy_template';
		}
		return $hidden;
	}
}

