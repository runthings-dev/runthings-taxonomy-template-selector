# Taxonomy Template

Assign archive templates to categories, tags and other taxonomy terms.

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue)](https://runthings.dev/wordpress-plugins/taxonomy-template/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF)](https://php.net/)
[![WordPress Version](https://img.shields.io/badge/wordpress-%3E%3D6.4-21759B)](https://wordpress.org/)

## Description

Assign custom archive templates to categories, tags and other taxonomy terms, similar to how WordPress page templates work.

Template selection is automatically enabled for all public taxonomies. Edit any term to choose a custom archive template.

### Features:

* Zero configuration - works out of the box for all public taxonomies
* Choose templates per-term from the term edit screen
* Works with categories, tags, and custom taxonomies
* Backwards compatible with legacy "Category Template:" headers
* Lightweight with no dependencies

### Links:

* [Plugin page](https://runthings.dev/wordpress-plugins/taxonomy-template/)
* [GitHub repository](https://github.com/runthings-dev/runthings-taxonomy-template)

## Installation

1. Upload the plugin to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create template files in your theme with the header comment `Taxonomy Template: Your Template Name`
4. Edit a category/term and select your template from the dropdown

## Frequently Asked Questions

### How do I create a custom template?

Add a PHP file to your theme directory with this header comment:

```php
<?php
/**
 * Taxonomy Template: My Custom Template
 */
```

### Which taxonomies are supported?

All public taxonomies including categories, tags, custom taxonomies from plugins like WooCommerce product categories, and any custom post type taxonomies.

### I'm using "Category Template:" in my theme files. Do I need to change it?

No. The plugin supports both `Taxonomy Template:` (recommended) and `Category Template:` (legacy) headers for backwards compatibility. Your existing templates will continue to work.

### Can I prevent data deletion when uninstalling?

Yes. Add this to your wp-config.php before uninstalling:

```php
define( 'RUNTHINGS_TAXONOMY_TEMPLATE_KEEP_DATA', true );
```

### Why don't I see the template dropdown?

Make sure your theme has at least one PHP file with either a `Taxonomy Template:` or `Category Template:` header comment. The dropdown only appears if templates are available to select.

### Can I add custom directories for template scanning?

Yes. Use the `runthings_taxonomy_template_dirs` filter to add additional directories. Note that subdirectories are not scanned automatically - you must add each folder path explicitly.

```php
add_filter( 'runthings_taxonomy_template_dirs', function( $dirs ) {
    $dirs[] = get_stylesheet_directory() . '/taxonomy-templates';
    return $dirs;
} );
```

### Can I add or remove templates without modifying theme files?

Yes. Use the `runthings_taxonomy_template_list` filter to modify the available templates. The filename is a path relative to your theme root - if using a child theme, it checks the child theme first, then falls back to the parent theme.

```php
add_filter( 'runthings_taxonomy_template_list', function( $templates ) {
    // Add a template from theme root
    $templates['My Custom Archive'] = 'custom-archive.php';
    // Add a template from a subdirectory
    $templates['Product Archive'] = 'template-parts/archive-product.php';
    // Remove one you don't want
    unset( $templates['Unwanted Template'] );
    return $templates;
} );
```

### How do I upgrade from Advanced Category Template?

This plugin is a fork of the original "Advanced Category Template" plugin by Praveen Goswami. It was adopted because the original plugin was removed from the WordPress.org plugin directory due to security issues.

Your existing template mappings will be migrated automatically when you activate this plugin. You can safely deactivate and delete the old plugin in any order.

## Screenshots

1. Template selection dropdown on the term edit screen.

![Template selection dropdown](screenshot-1.png)

## Changelog

### 1.0.0
* Forked from Advanced Category Template by Praveen Goswami
* Added namespace and modern PHP structure
* Fixed security issues
* Added proper sanitization and escaping
* Renamed to runthings-taxonomy-template
* Auto-enabled for all public taxonomies (no settings page needed)
* Added support for "Taxonomy Template:" header (with backwards compatibility for "Category Template:")

## License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, see [http://www.gnu.org/licenses/gpl-3.0.html](http://www.gnu.org/licenses/gpl-3.0.html).

