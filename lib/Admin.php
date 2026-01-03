<?php
/**
 * Admin UI handler
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

namespace Runthings\TaxonomyTemplateSelector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles admin UI for taxonomy template selection
 */
class Admin {

	/**
	 * Initialize admin hooks
	 */
	public function __construct() {
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
			add_action( $taxonomy_name . '_edit_form_fields', array( $this, 'render_meta_box' ) );
			add_action( $taxonomy_name . '_add_form_fields', array( $this, 'render_meta_box' ) );
			add_action( 'created_' . $taxonomy_name, array( $this, 'save_template' ) );
			add_action( 'edited_' . $taxonomy_name, array( $this, 'save_template' ) );

			// Admin columns.
			add_filter( 'manage_edit-' . $taxonomy_name . '_columns', array( $this, 'add_template_column' ) );
			add_filter( 'manage_' . $taxonomy_name . '_custom_column', array( $this, 'render_template_column' ), 10, 3 );
			add_filter( 'default_hidden_columns', array( $this, 'hide_template_column_by_default' ), 10, 2 );
		}
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
		$templates = $this->get_taxonomy_templates();
		$display_name = array_search( $template_file, $templates, true );

		return esc_html( $display_name ? $display_name : $template_file );
	}

	/**
	 * Hide template column by default
	 *
	 * @param array     $hidden Array of hidden column names.
	 * @param WP_Screen $screen Current screen object.
	 * @return array
	 */
	public function hide_template_column_by_default( $hidden, $screen ) {
		if ( isset( $screen->taxonomy ) && ! empty( $screen->taxonomy ) ) {
			$hidden[] = 'taxonomy_template';
		}
		return $hidden;
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

	/**
	 * Render meta box on taxonomy edit screen
	 *
	 * @param mixed $tag Term object or empty.
	 * @return void
	 */
	public function render_meta_box( $tag ) {
		$term_id = '';
		if ( ! empty( $tag ) && is_object( $tag ) ) {
			$term_id = $tag->term_id;
		}
		$template_mappings = get_option( 'runthings_taxonomy_template_selector_mappings' );
		$selected_template = isset( $template_mappings[ $term_id ] ) ? $template_mappings[ $term_id ] : false;
		?>
		<?php wp_nonce_field( 'runthings_taxonomy_template_selector_nonce_action', 'runthings_taxonomy_template_selector_nonce_field' ); ?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="runthings_taxonomy_template_selector"><?php esc_html_e( 'Taxonomy Template', 'runthings-taxonomy-template-selector' ); ?></label></th>
			<td>
				<select name="runthings_taxonomy_template_selector" id="runthings_taxonomy_template_selector">
					<option value='default'><?php esc_html_e( 'Default Template', 'runthings-taxonomy-template-selector' ); ?></option>
					<?php $this->render_template_dropdown( $selected_template ); ?>
				</select>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render template dropdown options
	 *
	 * @param string $default Currently selected template.
	 * @return void
	 */
	public function render_template_dropdown( $default = '' ) {
		$templates = $this->get_taxonomy_templates();
		ksort( $templates );
		foreach ( array_keys( $templates ) as $template ) {
			$selected = ( $default === $templates[ $template ] ) ? ' selected="selected"' : '';
			echo "\n\t<option value='" . esc_attr( $templates[ $template ] ) . "'" . $selected . '>' . esc_html( $template ) . '</option>';
		}
	}

	/**
	 * Save template selection
	 *
	 * @param int $term_id Term ID.
	 * @return void
	 */
	public function save_template( $term_id ) {
		if ( ! isset( $_POST['runthings_taxonomy_template_selector_nonce_field'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['runthings_taxonomy_template_selector_nonce_field'] ) );
		if ( ! wp_verify_nonce( $nonce, 'runthings_taxonomy_template_selector_nonce_action' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		if ( isset( $_POST['runthings_taxonomy_template_selector'] ) ) {
			$template_mappings = get_option( 'runthings_taxonomy_template_selector_mappings' );
			if ( ! is_array( $template_mappings ) ) {
				$template_mappings = array();
			}
			$template_mappings[ $term_id ] = sanitize_text_field( wp_unslash( $_POST['runthings_taxonomy_template_selector'] ) );
			update_option( 'runthings_taxonomy_template_selector_mappings', $template_mappings );
		}
	}
}

