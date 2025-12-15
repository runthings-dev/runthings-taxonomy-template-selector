=== Taxonomy Template ===
Contributors: runthings.dev
Tags: category, template, taxonomy, custom template, archive
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.0
License: GPLv2 or later

Assign custom templates to categories and taxonomies, just like page templates.

== Description ==

Assign custom templates to categories and other taxonomies, similar to how WordPress page templates work.

Select which taxonomies should have template selection enabled from the plugin settings. Then edit any term in those taxonomies to choose a custom template.

== Installation ==

1. Upload the plugin to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Dashboard => Taxonomy Template and select which taxonomies to enable
4. Create template files in your theme with the header comment `Category Template: Your Template Name`
5. Edit a category/term and select your template from the dropdown

== Frequently Asked Questions ==

= How do I create a custom template? =

Add a PHP file to your theme directory with this header comment:

`<?php
/**
 * Category Template: My Custom Template
 */`

= Which taxonomies are supported? =

All public taxonomies including categories, custom taxonomies from plugins like WooCommerce product categories, and any custom post type taxonomies.

== Changelog ==

= 1.0.0 =
* Forked from Advanced Category Template
* Added namespace and modern PHP structure
* Fixed security issues
* Added proper sanitization and escaping
* Renamed to runthings-taxonomy-template

== Features ==

* Select which taxonomies should have template selection enabled
* Choose templates per-term from the term edit screen
* Works with categories and custom taxonomies
* Lightweight with no dependencies

