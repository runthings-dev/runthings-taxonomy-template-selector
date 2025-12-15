=== Taxonomy Template ===
Contributors: runthingsdev
Tags: category, template, taxonomy, custom template, archive
Requires at least: 6.4
Requires PHP: 7.4
Tested up to: 6.9
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Assign templates to categories and other taxonomies, just like page templates.

== Description ==

Assign custom templates to categories and other taxonomies, similar to how WordPress page templates work.

Template selection is automatically enabled for all public taxonomies. Edit any term to choose a custom template.

**Features:**

* Zero configuration - works out of the box for all public taxonomies
* Choose templates per-term from the term edit screen
* Works with categories, tags, and custom taxonomies
* Backwards compatible with legacy "Category Template:" headers
* Lightweight with no dependencies

== Installation ==

1. Upload the plugin to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create template files in your theme with the header comment `Taxonomy Template: Your Template Name`
4. Edit a category/term and select your template from the dropdown

== Frequently Asked Questions ==

= How do I create a custom template? =

Add a PHP file to your theme directory with this header comment:

`<?php
/**
 * Taxonomy Template: My Custom Template
 */`

= Which taxonomies are supported? =

All public taxonomies including categories, tags, custom taxonomies from plugins like WooCommerce product categories, and any custom post type taxonomies.

= I'm using "Category Template:" in my theme files. Do I need to change it? =

No. The plugin supports both `Taxonomy Template:` (recommended) and `Category Template:` (legacy) headers for backwards compatibility. Your existing templates will continue to work.

= Can I prevent data deletion when uninstalling? =

Yes. Add this to your wp-config.php before uninstalling:

`define( 'RUNTHINGS_TAXONOMY_TEMPLATE_KEEP_DATA', true );`

= Why don't I see the template dropdown? =

Make sure your theme has at least one PHP file with either a `Taxonomy Template:` or `Category Template:` header comment. The dropdown only appears if templates are available to select.

= Can I add custom directories for template scanning? =

Yes. Use the `runthings_taxonomy_template_dirs` filter to add additional directories. Note that subdirectories are not scanned automatically - you must add each folder path explicitly.

`add_filter( 'runthings_taxonomy_template_dirs', function( $dirs ) {
    $dirs[] = get_stylesheet_directory() . '/taxonomy-templates';
    return $dirs;
} );`

= How do I upgrade from Advanced Category Template? =

This plugin is a fork of the original "Advanced Category Template" plugin by Praveen Goswami. It was adopted because the original plugin was removed from the WordPress.org plugin directory due to security issues.

Your existing template mappings will be migrated automatically when you activate this plugin. You can safely deactivate and delete the old plugin in any order.

== Screenshots ==

1. Template selection dropdown on the term edit screen.

== Changelog ==

= 1.0.0 =
* Forked from Advanced Category Template by Praveen Goswami
* Added namespace and modern PHP structure
* Fixed security issues
* Added proper sanitization and escaping
* Renamed to runthings-taxonomy-template
* Auto-enabled for all public taxonomies (no settings page needed)
* Added support for "Taxonomy Template:" header (with backwards compatibility for "Category Template:")
