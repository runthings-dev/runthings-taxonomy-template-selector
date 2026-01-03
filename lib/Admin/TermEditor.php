<?php
/**
 * Term editor UI handler
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

namespace Runthings\TaxonomyTemplateSelector\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles term edit screen template selection
 */
class TermEditor {

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
		add_action( $taxonomy_name . '_edit_form_fields', array( $this, 'render_meta_box' ) );
		add_action( $taxonomy_name . '_add_form_fields', array( $this, 'render_meta_box' ) );
		add_action( 'created_' . $taxonomy_name, array( $this, 'save_template' ) );
		add_action( 'edited_' . $taxonomy_name, array( $this, 'save_template' ) );
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
		$templates = $this->admin->get_taxonomy_templates();
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

