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
		<!-- Setting area -->
		<div class="templateFormType" style="width:70%; float:left;">
			<?php echo wp_kses_post( $msg ); ?>
			<fieldset class="temp-setting-fieldset">
				<legend class="temp-setting-legend"><strong><?php esc_html_e( 'Settings', 'runthings-taxonomy-template' ); ?></strong></legend>
				<form action="" method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'template_settings_action_disable' ); ?>
					<input type="checkbox" name="disable" id="disable" value="1" <?php checked( $runthings_plugin_status, '1' ); ?>> <label for="disable"><?php esc_html_e( 'Disable Plugin', 'runthings-taxonomy-template' ); ?></label>
					<input type="submit" name="Submit" value="<?php esc_attr_e( 'Disable', 'runthings-taxonomy-template' ); ?>" class="tmp-btn" />
					<input type="hidden" name="plugin_disable" value="disable" style="display:none;" />
				</form>
				<p class="temp-summery"><?php esc_html_e( 'Note: If you wish to disable plugin then checked above checkbox.', 'runthings-taxonomy-template' ); ?></p>
			</fieldset>
			<!-- Advanced setting area -->
			<fieldset class="temp-setting-fieldset">
				<legend class="temp-setting-legend"><strong><?php esc_html_e( 'General Settings', 'runthings-taxonomy-template' ); ?></strong></legend>
				<form action="" method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'template_settings_action_save' ); ?>
					<div class="type_chkbox_main">
						<?php
						foreach ( $taxonomies as $runthings_taxonomy ) {
							$runthings_is_checked     = false;
							$runthings_enabled_taxonomies = get_option( 'runthings_taxonomy_template_taxonomies' );
							if ( $runthings_enabled_taxonomies != '' ) {
								$runthings_taxonomy_list = explode( ',', $runthings_enabled_taxonomies );
								$runthings_is_checked    = in_array( $runthings_taxonomy->name, $runthings_taxonomy_list, true );
							}
							?>
							<div class="temp-cat-chkbox">
								<input type="checkbox" name="post_category_name[]" value="<?php echo esc_attr( $runthings_taxonomy->name ); ?>" id="<?php echo esc_attr( $runthings_taxonomy->name ); ?>" <?php checked( $runthings_is_checked ); ?> class="chkBox" />
								<label for="<?php echo esc_attr( $runthings_taxonomy->name ); ?>">
									<?php echo esc_html( ucfirst( $runthings_taxonomy->object_type[0] ) . '_' . $runthings_taxonomy->label ); ?>
								</label>
							</div>
							<?php
						}
						?>
					</div>
					<div class="tmpType_submit">
						<input type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'runthings-taxonomy-template' ); ?>" class="tmp-btn" />
						<input type="hidden" name="template_settings" value="save" style="display:none;" />
					</div>
				</form>
				<p class="temp-summery"><?php esc_html_e( 'Note: Select one or more taxonomies where you need to enable template selection.', 'runthings-taxonomy-template' ); ?></p>
			</fieldset>
			<!-- Default setting area -->
			<div class="defaultFormType" style="width:70%; float:left;">
				<fieldset class="temp-setting-fieldset">
					<legend class="temp-setting-legend"><strong><?php esc_html_e( 'Default Settings', 'runthings-taxonomy-template' ); ?></strong></legend>
					<form action="" method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'template_settings_action_reset' ); ?>
						<input type="submit" name="Submit" value="<?php esc_attr_e( 'Default Setting', 'runthings-taxonomy-template' ); ?>" class="tmp-btn" />
						<input type="hidden" name="template_reset" value="reset" style="display:none;" />
					</form>
					<p class="temp-summery"><?php esc_html_e( 'Note: If you are using default setting then taxonomy template will show only on default post category.', 'runthings-taxonomy-template' ); ?></p>
				</fieldset>
			</div>
		</div>
		<?php
	}
}

// Render when included.
Admin_Form::render();