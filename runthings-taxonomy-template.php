<?php
/**
 * Plugin Name: Taxonomy Template
 * Description: Add custom template selection to category and other taxonomies
 * Author: Matthew Harris, runthings.dev
 * Author URI: https://runthings.dev/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: runthings-taxonomy-template
 *
 * @package Runthings_Taxonomy_Template
 */

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace Runthings\TaxonomyTemplate;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RUNTHINGS_TAXONOMY_TEMPLATE_VERSION', '1.0.0' );
define( 'RUNTHINGS_TAXONOMY_TEMPLATE_FILE', __FILE__ );
define( 'RUNTHINGS_TAXONOMY_TEMPLATE_DIR', plugin_dir_path( __FILE__ ) );
define( 'RUNTHINGS_TAXONOMY_TEMPLATE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class
 */
class Plugin {
		/**
		 * Constructor
		 */
		public function __construct() {
			register_activation_hook( RUNTHINGS_TAXONOMY_TEMPLATE_FILE, array( $this, 'activate' ) );

			add_filter( 'taxonomy_template', array( $this, 'filter_taxonomy_template' ) );
			add_filter( 'category_template', array( $this, 'filter_category_template' ) );

			// Enable for all public taxonomies.
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
			}
		}

		/**
		 * Plugin activation - migrate from old plugin if needed
		 *
		 * @return void
		 */
		public function activate() {
			// Migrate from old plugin options if they exist.
			$old_mappings = get_option( 'category_templates' );

			if ( $old_mappings && ! get_option( 'runthings_taxonomy_template_mappings' ) ) {
				add_option( 'runthings_taxonomy_template_mappings', $old_mappings );
			}
		}

		/**
		 * get category template from theme
		 */
		public function get_category_templates()
		{
			$post_templates = array();

			// Scan theme directory for PHP files with Category Template header
			$theme_dir = get_template_directory();
			$stylesheet_dir = get_stylesheet_directory();

			$dirs_to_scan = array($theme_dir);
			if ($stylesheet_dir !== $theme_dir) {
				$dirs_to_scan[] = $stylesheet_dir;
			}

			foreach ($dirs_to_scan as $dir) {
				$files = glob($dir . '/*.php');
				if (!is_array($files)) {
					continue;
				}

				foreach ($files as $template) {
					$basename = basename($template);
					if ($basename === 'functions.php') {
						continue;
					}

					$template_data = file_get_contents($template);
					if ($template_data === false) {
						continue;
					}

					$name = '';
					if (preg_match('|Category Template:(.*)$|mi', $template_data, $name)) {
						$name = trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $name[1]));
					}

					if (!empty($name)) {
						$post_templates[trim($name)] = $basename;
					}
				}
			}

			return $post_templates;
		}

		/**
		 * Render meta box on category edit screen
		 *
		 * @param mixed $tag Term object or empty.
		 * @return void
		 */
		public function render_meta_box( $tag ) {
			$term_id = '';
			if ( ! empty( $tag ) && is_object( $tag ) ) {
				$term_id = $tag->term_id;
			}
			$template_mappings  = get_option( 'runthings_taxonomy_template_mappings' );
			$selected_template = isset( $template_mappings[ $term_id ] ) ? $template_mappings[ $term_id ] : false;
			?>
			<?php wp_nonce_field( 'runthings_taxonomy_template_nonce_action', 'runthings_taxonomy_template_nonce_field' ); ?>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="taxonomy_template"><?php esc_html_e( 'Taxonomy Template', 'runthings-taxonomy-template' ); ?></label></th>
				<td>
					<select name="taxonomy_template" id="taxonomy_template">
						<option value='default'><?php esc_html_e( 'Default Template', 'runthings-taxonomy-template' ); ?></option>
						<?php $this->render_template_dropdown( $selected_template ); ?>
					</select>
					<br />
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
			$templates = $this->get_category_templates();
			ksort( $templates );
			foreach ( array_keys( $templates ) as $template ) {
				$selected = ( $default == $templates[ $template ] ) ? ' selected="selected"' : '';
				echo "\n\t<option value='" . esc_attr( $templates[ $template ] ) . "'" . esc_attr( $selected ) . '>' . esc_html( $template ) . '</option>';
			}
		}

		/**
		 * Save template selection
		 *
		 * @param int $term_id Term ID.
		 * @return void
		 */
		public function save_template( $term_id ) {
			if ( ! isset( $_POST['runthings_taxonomy_template_nonce_field'] ) ) {
				return;
			}

			$nonce = sanitize_text_field( wp_unslash( $_POST['runthings_taxonomy_template_nonce_field'] ) );
			if ( ! wp_verify_nonce( $nonce, 'runthings_taxonomy_template_nonce_action' ) ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( isset( $_POST['taxonomy_template'] ) ) {
				$template_mappings = get_option( 'runthings_taxonomy_template_mappings' );
				if ( ! is_array( $template_mappings ) ) {
					$template_mappings = array();
				}
				$template_mappings[ $term_id ] = sanitize_text_field( wp_unslash( $_POST['taxonomy_template'] ) );
				update_option( 'runthings_taxonomy_template_mappings', $template_mappings );
			}
		}

		/**
		 * Filter category template
		 *
		 * @param string $category_template Current template path.
		 * @return string
		 */
		public function filter_category_template( $category_template ) {
			$term_id           = absint( get_query_var( 'cat' ) );
			$template_mappings = get_option( 'runthings_taxonomy_template_mappings' );
			if ( isset( $template_mappings[ $term_id ] ) && $template_mappings[ $term_id ] != 'default' ) {
				$located_template = locate_template( $template_mappings[ $term_id ] );
				if ( ! empty( $located_template ) ) {
					return apply_filters( 'runthings_taxonomy_template_found', $located_template );
				}
			}
			return $category_template;
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
			$term_id           = $queried_term->term_id;
			$template_mappings = get_option( 'runthings_taxonomy_template_mappings' );
			if ( isset( $template_mappings[ $term_id ] ) && $template_mappings[ $term_id ] != 'default' ) {
				$located_template = locate_template( $template_mappings[ $term_id ] );
				if ( ! empty( $located_template ) ) {
					return apply_filters( 'runthings_taxonomy_template_found', $located_template );
				}
			}
			return $taxonomy_template;
		}

		/**
		 * Get singleton instance
		 *
		 * @return Plugin
		 */
		public static function get_instance() {
			static $instance = null;
			if ( null === $instance ) {
				$instance = new self();
			}
			return $instance;
		}
	}

Plugin::get_instance();
