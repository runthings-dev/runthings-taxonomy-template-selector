<?php
/**
 * Admin UI handler
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

namespace Runthings\TaxonomyTemplateSelector;

use Runthings\TaxonomyTemplateSelector\Admin\TermEditor;
use Runthings\TaxonomyTemplateSelector\Admin\Column;
use Runthings\TaxonomyTemplateSelector\Admin\BulkActions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin UI for taxonomy template selection
 */
class Admin {

	/**
	 * Term editor instance
	 *
	 * @var TermEditor
	 */
	private $term_editor;

	/**
	 * Column instance
	 *
	 * @var Column
	 */
	private $column;

	/**
	 * Bulk actions instance
	 *
	 * @var BulkActions
	 */
	private $bulk_actions;

	/**
	 * Initialize admin hooks
	 */
	public function __construct() {
		$this->term_editor  = new TermEditor( $this );
		$this->column       = new Column( $this );
		$this->bulk_actions = new BulkActions( $this );

		add_action( 'init', array( $this, 'register_taxonomy_hooks' ), 99 );
	}

	/**
	 * Register hooks for all public taxonomies
	 *
	 * @return void
	 */
	public function register_taxonomy_hooks() {
		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );

		foreach ( $taxonomies as $taxonomy_name ) {
			$this->term_editor->register_hooks( $taxonomy_name );
			$this->column->register_hooks( $taxonomy_name );
			$this->bulk_actions->register_hooks( $taxonomy_name );
		}

		$this->bulk_actions->register_global_hooks();
	}

	/**
	 * Get taxonomy templates from theme
	 *
	 * Scans for "Taxonomy Template:" header (preferred) and
	 * "Category Template:" header (legacy, for backwards compatibility).
	 *
	 * @return array
	 */
	public function get_taxonomy_templates() {
		$templates = array();

		$theme_dir = get_template_directory();
		$stylesheet_dir = get_stylesheet_directory();

		$dirs_to_scan = array( $theme_dir );
		if ( $stylesheet_dir !== $theme_dir ) {
			$dirs_to_scan[] = $stylesheet_dir;
		}

		/**
		 * Filter the directories scanned for taxonomy templates.
		 *
		 * By default, scans the root of parent and child theme directories.
		 * Does not scan subdirectories - add full paths to include additional folders.
		 *
		 * @param array $dirs_to_scan Array of absolute directory paths to scan.
		 */
		$dirs_to_scan = apply_filters( 'runthings_taxonomy_template_selector_dirs', $dirs_to_scan );

		$headers = array(
			'TaxonomyTemplate' => 'Taxonomy Template',
			'CategoryTemplate' => 'Category Template', // Legacy support.
		);

		foreach ( $dirs_to_scan as $dir ) {
			$files = glob( $dir . '/*.php' );
			if ( ! is_array( $files ) ) {
				continue;
			}

			foreach ( $files as $template ) {
				$basename = basename( $template );
				if ( 'functions.php' === $basename ) {
					continue;
				}

				$file_data = get_file_data( $template, $headers );

				// Preferred: "Taxonomy Template:" header, fallback to legacy "Category Template:".
				$name = ! empty( $file_data['TaxonomyTemplate'] )
					? $file_data['TaxonomyTemplate']
					: $file_data['CategoryTemplate'];

				if ( ! empty( $name ) ) {
					$templates[ trim( $name ) ] = $basename;
				}
			}
		}

		/**
		 * Filter the discovered taxonomy templates.
		 *
		 * Allows adding templates without headers, removing templates,
		 * or modifying template display names.
		 *
		 * @param array $templates Array of templates as 'Template Name' => 'filename.php'.
		 */
		$templates = apply_filters( 'runthings_taxonomy_template_selector_list', $templates );

		return $templates;
	}
}

