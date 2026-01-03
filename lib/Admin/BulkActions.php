<?php
/**
 * Bulk actions handler
 *
 * @package Runthings_Taxonomy_Template_Selector
 */

namespace Runthings\TaxonomyTemplateSelector\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles bulk template assignment
 */
class BulkActions {

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
		add_filter( 'bulk_actions-edit-' . $taxonomy_name, array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-' . $taxonomy_name, array( $this, 'handle_bulk_action' ), 10, 3 );
	}

	/**
	 * Register global hooks (call once, not per taxonomy)
	 *
	 * @return void
	 */
	public function register_global_hooks() {
		add_action( 'admin_notices', array( $this, 'bulk_action_admin_notice' ) );
	}

	/**
	 * Add bulk actions for template assignment
	 *
	 * @param array $actions Existing bulk actions.
	 * @return array
	 */
	public function add_bulk_actions( $actions ) {
		$templates = $this->admin->get_taxonomy_templates();

		if ( empty( $templates ) ) {
			return $actions;
		}

		$actions['set_taxonomy_template_default'] = __( 'Set Template: Default', 'runthings-taxonomy-template-selector' );

		foreach ( $templates as $name => $file ) {
			$actions[ 'set_taxonomy_template_' . sanitize_key( $file ) ] = sprintf(
				/* translators: %s: template name */
				__( 'Set Template: %s', 'runthings-taxonomy-template-selector' ),
				$name
			);
		}

		return $actions;
	}

	/**
	 * Handle bulk action for template assignment
	 *
	 * @param string $redirect_url Redirect URL.
	 * @param string $action       Action name.
	 * @param array  $term_ids     Selected term IDs.
	 * @return string
	 */
	public function handle_bulk_action( $redirect_url, $action, $term_ids ) {
		if ( 0 !== strpos( $action, 'set_taxonomy_template_' ) ) {
			return $redirect_url;
		}

		$template_key = str_replace( 'set_taxonomy_template_', '', $action );

		// Determine the template file to set.
		if ( 'default' === $template_key ) {
			$template_file = 'default';
		} else {
			$templates = $this->admin->get_taxonomy_templates();
			$template_file = null;

			foreach ( $templates as $file ) {
				if ( sanitize_key( $file ) === $template_key ) {
					$template_file = $file;
					break;
				}
			}

			if ( null === $template_file ) {
				return $redirect_url;
			}
		}

		$template_mappings = get_option( 'runthings_taxonomy_template_selector_mappings' );
		if ( ! is_array( $template_mappings ) ) {
			$template_mappings = array();
		}

		$updated = 0;
		foreach ( $term_ids as $term_id ) {
			if ( ! current_user_can( 'edit_term', $term_id ) ) {
				continue;
			}
			$template_mappings[ $term_id ] = $template_file;
			$updated++;
		}

		update_option( 'runthings_taxonomy_template_selector_mappings', $template_mappings );

		return add_query_arg( 'taxonomy_template_updated', $updated, $redirect_url );
	}

	/**
	 * Display admin notice after bulk action
	 *
	 * @return void
	 */
	public function bulk_action_admin_notice() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified in handle_bulk_action before redirect; this just displays the result
		if ( ! isset( $_GET['taxonomy_template_updated'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified in handle_bulk_action before redirect; intval ensures safe output
		$count = intval( $_GET['taxonomy_template_updated'] );

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of terms updated */
					_n(
						'Template updated for %d term.',
						'Template updated for %d terms.',
						$count,
						'runthings-taxonomy-template-selector'
					),
					$count
				)
			)
		);
	}
}

