<?php
/**
 * Quick edit UI handler
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

namespace Runthings\TaxonomyTemplateSelector\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles quick edit template selection for taxonomy terms
 */
class QuickEdit {

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
	 * Register global hooks (once, not per taxonomy)
	 *
	 * @return void
	 */
	public function register_global_hooks() {
		add_action( 'quick_edit_custom_box', array( $this, 'render_quick_edit_field' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Render quick edit field
	 *
	 * @param string $column_name Column name.
	 * @param string $screen Screen type.
	 * @param string $taxonomy Taxonomy name (for term quick edit).
	 * @return void
	 */
	public function render_quick_edit_field( $column_name, $screen, $taxonomy = '' ) {
		if ( 'taxonomy_template' !== $column_name ) {
			return;
		}

		// Only render for taxonomy term lists.
		if ( 'edit-tags' !== $screen ) {
			return;
		}

		$templates = $this->admin->get_taxonomy_templates();
		?>
		<fieldset>
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_html_e( 'Template', 'runthings-taxonomy-template-selector' ); ?></span>
					<select name="runthings_taxonomy_template_selector" id="runthings_taxonomy_template_selector_quick_edit">
						<option value="default"><?php esc_html_e( 'Default Template', 'runthings-taxonomy-template-selector' ); ?></option>
						<?php
						ksort( $templates );
						foreach ( $templates as $name => $file ) {
							echo '<option value="' . esc_attr( $file ) . '">' . esc_html( $name ) . '</option>';
						}
						?>
					</select>
				</label>
			</div>
			<?php wp_nonce_field( 'runthings_taxonomy_template_selector_nonce_action', 'runthings_taxonomy_template_selector_nonce_field' ); ?>
		</fieldset>
		<?php
	}

	/**
	 * Enqueue scripts for quick edit
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'edit-tags.php' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'runthings-taxonomy-template-selector-quick-edit',
			plugins_url( 'assets/js/quick-edit.js', dirname( __DIR__ ) ),
			array( 'jquery', 'inline-edit-tax' ),
			RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_VERSION,
			true
		);
	}
}

