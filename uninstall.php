<?php
/**
 * Uninstall handler
 *
 * @package Runthings_Taxonomy_Template
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete current plugin options only.
delete_option( 'runthings_taxonomy_template_taxonomies' );
delete_option( 'runthings_taxonomy_template_disabled' );
delete_option( 'runthings_taxonomy_template_mappings' );

