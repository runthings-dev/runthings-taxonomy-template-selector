<?php
/**
 * Plugin Name: Taxonomy Template
 * Description: Assign archive templates to categories, tags and other taxonomy terms
 * Author: runthingsdev
 * Author URI: https://runthings.dev/
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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

// Load classes.
require_once RUNTHINGS_TAXONOMY_TEMPLATE_DIR . 'lib/Admin.php';
require_once RUNTHINGS_TAXONOMY_TEMPLATE_DIR . 'lib/Template_Loader.php';

// Initialize.
new Admin();
new Template_Loader();

/**
 * Plugin activation - migrate from old plugin if needed
 *
 * @return void
 */
function activate() {
	$old_mappings = get_option( 'category_templates' );

	if ( is_array( $old_mappings ) && ! get_option( 'runthings_taxonomy_template_mappings' ) ) {
		add_option( 'runthings_taxonomy_template_mappings', $old_mappings );
	}
}
register_activation_hook( RUNTHINGS_TAXONOMY_TEMPLATE_FILE, __NAMESPACE__ . '\activate' );
