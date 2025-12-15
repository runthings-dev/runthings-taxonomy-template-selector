<?php
/**
 * Settings Form Handler
 *
 * @package Runthings_Taxonomy_Template
 */

namespace Runthings\TaxonomyTemplate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings form handler class
 */
class Admin_Form {

	/**
	 * Process form submissions and render the settings page
	 *
	 * @return void
	 */
	public static function render() {
		// TODO: Remove after migration complete.
		self::maybe_migrate_legacy_options();

		$runthings_msg        = self::process_form_submission();
		$runthings_taxonomies = self::get_taxonomies();
		self::render_html( $runthings_msg, $runthings_taxonomies );
	}

	/**
	 * Migrate legacy options to new prefixed names (one-time migration)
	 * TODO: Remove this method after migration complete.
	 *
	 * @return void
	 */
	private static function maybe_migrate_legacy_options() {
		// Skip if already migrated.
		if ( get_option( 'runthings_taxonomy_template_taxonomies' ) ) {
			return;
		}

		$old_taxonomies = get_option( 'advance_category_template' );
		$old_status     = get_option( 'category_template_status' );
		$old_mappings   = get_option( 'category_templates' );

		if ( $old_taxonomies ) {
			add_option( 'runthings_taxonomy_template_taxonomies', $old_taxonomies );
		}
		if ( false !== $old_status ) {
			add_option( 'runthings_taxonomy_template_disabled', $old_status );
		}
		if ( $old_mappings ) {
			add_option( 'runthings_taxonomy_template_mappings', $old_mappings );
		}

		// Set defaults if still nothing.
		if ( ! get_option( 'runthings_taxonomy_template_taxonomies' ) ) {
			add_option( 'runthings_taxonomy_template_taxonomies', 'category' );
		}
		if ( ! get_option( 'runthings_taxonomy_template_disabled' ) ) {
			add_option( 'runthings_taxonomy_template_disabled', '0' );
		}
	}

	/**
	 * Process form submissions
	 *
	 * @return string Message HTML
	 */
	private static function process_form_submission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		$runthings_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';

		if ( isset( $_POST['template_settings'] ) && is_string( $_POST['template_settings'] ) ) {
			if ( wp_verify_nonce( $runthings_nonce, 'template_settings_action_save' ) ) {
				$runthings_post_category_name = array();
				if ( isset( $_POST['post_category_name'] ) && is_array( $_POST['post_category_name'] ) ) {
					$runthings_post_category_name = array_map( 'sanitize_text_field', wp_unslash( $_POST['post_category_name'] ) );
				}
				return self::save_template_settings( $runthings_post_category_name );
			} else {
				return '<div id="message" class="error"><p>' . esc_html__( 'Security checks failed.', 'runthings-taxonomy-template' ) . '</p></div>';
			}
		} elseif ( isset( $_REQUEST['template_reset'] ) && is_string( $_REQUEST['template_reset'] ) ) {
			if ( wp_verify_nonce( $runthings_nonce, 'template_settings_action_reset' ) ) {
				return self::reset_template_settings();
			} else {
				return '<div id="message" class="error"><p>' . esc_html__( 'Security checks failed.', 'runthings-taxonomy-template' ) . '</p></div>';
			}
		} elseif ( isset( $_REQUEST['plugin_disable'] ) && is_string( $_REQUEST['plugin_disable'] ) ) {
			if ( wp_verify_nonce( $runthings_nonce, 'template_settings_action_disable' ) ) {
				$runthings_disable = isset( $_POST['disable'] ) ? sanitize_text_field( wp_unslash( $_POST['disable'] ) ) : '';
				return self::disable_plugin_settings( $runthings_disable );
			} else {
				return '<div id="message" class="error"><p>' . esc_html__( 'Security checks failed.', 'runthings-taxonomy-template' ) . '</p></div>';
			}
		}

		return '';
	}

	/**
	 * Get available taxonomies
	 *
	 * @return array
	 */
	private static function get_taxonomies() {
		$runthings_taxonomies    = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects', 'and' );
		$runthings_taxonomy_posts = get_object_taxonomies( 'post', 'objects' );
		$runthings_taxonomies    = array_merge( $runthings_taxonomies, $runthings_taxonomy_posts );
		unset( $runthings_taxonomies['post_format'], $runthings_taxonomies['post_tag'] );
		sort( $runthings_taxonomies );
		return $runthings_taxonomies;
	}

	/**
	 * Save template settings
	 *
	 * @param array $post_category_name Sanitized taxonomy names.
	 * @return string Message HTML.
	 */
	private static function save_template_settings( $post_category_name ) {
		if ( ! empty( $post_category_name ) ) {
			update_option( 'runthings_taxonomy_template_taxonomies', implode( ',', $post_category_name ) );
			return '<div id="message" class="updated below-h2"><p>' . esc_html__( 'Setting Saved.', 'runthings-taxonomy-template' ) . '</p></div>';
		} else {
			update_option( 'runthings_taxonomy_template_taxonomies', 'category' );
			return '<div id="message" class="error"><p>' . esc_html__( 'Please select at least one taxonomy.', 'runthings-taxonomy-template' ) . '</p></div>';
		}
	}

	/**
	 * Reset template settings
	 *
	 * @return string Message HTML.
	 */
	private static function reset_template_settings() {
		update_option( 'runthings_taxonomy_template_taxonomies', 'category' );
		update_option( 'runthings_taxonomy_template_disabled', '0' );
		return '<div id="message" class="updated below-h2"><p>' . esc_html__( 'Default Setting Saved.', 'runthings-taxonomy-template' ) . '</p></div>';
	}

	/**
	 * Disable/enable plugin
	 *
	 * @param string $disable Disable value.
	 * @return string Message HTML.
	 */
	private static function disable_plugin_settings( $disable ) {
		if ( $disable == 1 ) {
			update_option( 'runthings_taxonomy_template_disabled', '1' );
			return '<div id="message" class="updated below-h2"><p>' . esc_html__( 'Plugin Disabled Successfully.', 'runthings-taxonomy-template' ) . '</p></div>';
		} else {
			update_option( 'runthings_taxonomy_template_disabled', '0' );
			return '<div id="message" class="updated below-h2"><p>' . esc_html__( 'Plugin Enabled Successfully.', 'runthings-taxonomy-template' ) . '</p></div>';
		}
	}

	/**
	 * Render the settings HTML
	 *
	 * @param string $msg        Message to display.
	 * @param array  $taxonomies Available taxonomies.
	 * @return void
	 */
	private static function render_html( $msg, $taxonomies ) {
		$runthings_plugin_status = get_option( 'runthings_taxonomy_template_disabled' );
		?>
		<div class="wrap">
			<?php echo wp_kses_post( $msg ); ?>

			<div class="card">
				<h2><?php esc_html_e( 'Plugin Status', 'runthings-taxonomy-template' ); ?></h2>
				<form action="" method="post">
					<?php wp_nonce_field( 'template_settings_action_disable' ); ?>
					<p>
						<label>
							<input type="checkbox" name="disable" id="disable" value="1" <?php checked( $runthings_plugin_status, '1' ); ?>>
							<?php esc_html_e( 'Disable Plugin', 'runthings-taxonomy-template' ); ?>
						</label>
					</p>
					<p class="description"><?php esc_html_e( 'Check this box to disable the plugin functionality.', 'runthings-taxonomy-template' ); ?></p>
					<p>
						<input type="hidden" name="plugin_disable" value="disable" />
						<?php submit_button( __( 'Update Status', 'runthings-taxonomy-template' ), 'primary', 'Submit', false ); ?>
					</p>
				</form>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Enabled Taxonomies', 'runthings-taxonomy-template' ); ?></h2>
				<form action="" method="post">
					<?php wp_nonce_field( 'template_settings_action_save' ); ?>
					<fieldset>
						<?php
						foreach ( $taxonomies as $runthings_taxonomy ) {
							$runthings_is_checked     = false;
							$runthings_enabled_taxonomies = get_option( 'runthings_taxonomy_template_taxonomies' );
							if ( $runthings_enabled_taxonomies != '' ) {
								$runthings_taxonomy_list = explode( ',', $runthings_enabled_taxonomies );
								$runthings_is_checked    = in_array( $runthings_taxonomy->name, $runthings_taxonomy_list, true );
							}
							?>
							<p>
								<label>
									<input type="checkbox" name="post_category_name[]" value="<?php echo esc_attr( $runthings_taxonomy->name ); ?>" id="<?php echo esc_attr( $runthings_taxonomy->name ); ?>" <?php checked( $runthings_is_checked ); ?> />
									<?php echo esc_html( ucfirst( $runthings_taxonomy->object_type[0] ) . ' — ' . $runthings_taxonomy->label ); ?>
								</label>
							</p>
							<?php
						}
						?>
					</fieldset>
					<p class="description"><?php esc_html_e( 'Select which taxonomies should have template selection enabled.', 'runthings-taxonomy-template' ); ?></p>
					<p>
						<input type="hidden" name="template_settings" value="save" />
						<?php submit_button( __( 'Save Settings', 'runthings-taxonomy-template' ), 'primary', 'submit', false ); ?>
					</p>
				</form>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'Reset to Defaults', 'runthings-taxonomy-template' ); ?></h2>
				<form action="" method="post">
					<?php wp_nonce_field( 'template_settings_action_reset' ); ?>
					<p class="description"><?php esc_html_e( 'Reset all settings to defaults. Only the default post category will have template selection enabled.', 'runthings-taxonomy-template' ); ?></p>
					<p>
						<input type="hidden" name="template_reset" value="reset" />
						<?php submit_button( __( 'Reset to Defaults', 'runthings-taxonomy-template' ), 'secondary', 'Submit', false ); ?>
					</p>
				</form>
			</div>
		</div>
		<?php
	}
}

// Render when included.
Admin_Form::render();