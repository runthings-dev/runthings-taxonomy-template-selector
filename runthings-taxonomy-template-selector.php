<?php
/**
 * Plugin Name: TaxoSelect - Taxonomy Template Selector
 * Plugin URI: https://runthings.dev/wordpress-plugins/taxonomy-template-selector/
 * Description: Assign archive templates to categories, tags and other taxonomy terms
 * Author: runthingsdev
 * Author URI: https://runthings.dev/
 * Version: 1.3.1
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: runthings-taxonomy-template-selector
 *
 * @package Runthings_Taxonomy_Template_Selector
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

namespace Runthings\TaxonomyTemplateSelector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_VERSION', '1.3.1' );
define( 'RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_FILE', __FILE__ );
define( 'RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_DIR', plugin_dir_path( __FILE__ ) );

// Load classes.
require_once RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_DIR . 'lib/Admin/TermEditor.php';
require_once RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_DIR . 'lib/Admin/Column.php';
require_once RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_DIR . 'lib/Admin/BulkActions.php';
require_once RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_DIR . 'lib/Admin/QuickEdit.php';
require_once RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_DIR . 'lib/Admin.php';
require_once RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_DIR . 'lib/Template_Loader.php';

// Initialize.
new Admin();
new Template_Loader();

/**
 * Plugin activation - migrate from old option names if needed
 *
 * Checks for data from:
 * 1. runthings_taxonomy_template_mappings (v1.0.0 of this plugin)
 * 2. category_templates (original Advanced Category Template plugin)
 *
 * @return void
 */
function activate() {
	// Skip if new option already exists.
	if ( get_option( 'runthings_taxonomy_template_selector_mappings' ) ) {
		return;
	}

	// Try v1.0.0 option name first.
	$old_mappings = get_option( 'runthings_taxonomy_template_mappings' );
	if ( is_array( $old_mappings ) ) {
		add_option( 'runthings_taxonomy_template_selector_mappings', $old_mappings );
		return;
	}

	// Try original plugin option name.
	$legacy_mappings = get_option( 'category_templates' );
	if ( is_array( $legacy_mappings ) ) {
		add_option( 'runthings_taxonomy_template_selector_mappings', $legacy_mappings );
	}
}
register_activation_hook( RUNTHINGS_TAXONOMY_TEMPLATE_SELECTOR_FILE, __NAMESPACE__ . '\activate' );
