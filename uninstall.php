<?php
/**
 * Uninstall handler
 *
 * @package Runthings_Taxonomy_Template
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Allow users to preserve data by defining RUNTHINGS_TAXONOMY_TEMPLATE_KEEP_DATA in wp-config.php.
if ( defined( 'RUNTHINGS_TAXONOMY_TEMPLATE_KEEP_DATA' ) && RUNTHINGS_TAXONOMY_TEMPLATE_KEEP_DATA ) {
	return;
}

// Delete plugin options.
delete_option( 'runthings_taxonomy_template_mappings' );

