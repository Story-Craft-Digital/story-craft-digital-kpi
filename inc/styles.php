<?php

/**
 * Style Loader for Story Craft Digital Core
 * Manages Tailwind CSS local builds across Dashboards, Add-ons, and Gutenberg.
 */

if (!defined('ABSPATH')) exit;

/**
 * 1. THE DASHBOARD LOADER
 * Automatically detects if the current page is an SCD Core or SCD Add-on page.
 */
function scdkpi_enqueue_dashboard_assets($hook_suffix)
{
    $screen = get_current_screen();
    $is_kpi_page = false;

    // A. Detect by Parent Menu (Catches Core + All Add-ons using scdkpi- prefix)
    if (isset($screen->parent_base) && strpos($screen->parent_base, 'scdkpi-') !== false) {
        $is_kpi_page = true;
    }

    // B. Detect by Prefix list (Backup safety)
    $scd_prefixes = ['scdkpi-', 'scdkpi', 'scdkpi-kpi'];
    if (!$is_kpi_page) {
        foreach ($scd_prefixes as $prefix) {
            if (strpos($hook_suffix, $prefix) !== false) {
                $is_kpi_page = true;
                break;
            }
        }
    }

    if ($is_kpi_page) {
        // Enqueue the Local Tailwind Build
        wp_enqueue_style(
            'scdkpi-tailwind',
            plugins_url('assets/tailwind-built.css', SCDKPI_PLUGIN_FILE),
            array(),
            SCDKPI_VERSION
        );

        // Enqueue Custom Resets (Alignment, Link Color, Button fixes)
        wp_enqueue_style(
            'scdkpi-custom',
            plugins_url('assets/custom.css', SCDKPI_PLUGIN_FILE),
            array('scdkpi-tailwind'),
            SCDKPI_VERSION
        );

        // Enqueue WP Admin Positioning Fixes
        wp_enqueue_style(
            'scdkpi-admin-protection',
            plugins_url('admin/assets/css/admin-protection.css', SCDKPI_PLUGIN_FILE),
            array('scdkpi-tailwind'),
            SCDKPI_VERSION
        );
    }
}
add_action('admin_enqueue_scripts', 'scdkpi_enqueue_dashboard_assets');

/**
 * 2. THE FRONTEND LOADER
 * Loads the built Tailwind CSS for your blocks on the actual website.
 */
function scdkpi_load_frontend_styles() {
    if (is_admin()) return;

    // 1. Identify "Legacy Dashboard" pages where we actually NEED the core CSS
    // Add your slugs to this array as you create them.
    $legacy_dashboards = ['kpi-login', 'kpi-dashboard'];
    
    $should_load_core = false;

    // Check by slug
    if (is_page($legacy_dashboards)) {
        $should_load_core = true;
    }

    // Check by shortcode (Optional: automatically loads if [scdkpi_dashboard] is present)
    $content = get_post() ? get_post()->post_content : '';
    if (has_shortcode($content, 'scdkpi_dashboard')) {
        $should_load_core = true;
    }

    // 2. Only load the heavy CSS if we are on a legacy dashboard page
    if ($should_load_core) {
        wp_enqueue_style(
            'scdkpi-tailwind-public',
            plugins_url('assets/tailwind-built.css', SCDKPI_PLUGIN_FILE),
            array(),
            SCDKPI_VERSION
        );

        wp_enqueue_style(
            'scdkpi-custom-styles',
            plugins_url('assets/custom.css', SCDKPI_PLUGIN_FILE),
            array('scdkpi-tailwind-public'),
            SCDKPI_VERSION
        );
    }
}
add_action('wp_enqueue_scripts', 'scdkpi_load_frontend_styles');

/**
 * 3. THE BLOCK EDITOR (GUTENBERG) LOADER
 * Replaces the CDN by injecting the local file into the Editor Iframe.
 */
function scdkpi_add_editor_styles()
{
    // This is the "Magic" function that tells the editor to use your file
    add_editor_style(plugins_url('assets/tailwind-built.css', SCDKPI_PLUGIN_FILE));
    add_editor_style(plugins_url('assets/custom.css', SCDKPI_PLUGIN_FILE));
}
add_action('after_setup_theme', 'scdkpi_add_editor_styles');
add_action('admin_init', 'scdkpi_add_editor_styles');

/**
 * 4. EDITOR COMPATIBILITY WRAPPER
 * Enqueue both Tailwind and Custom CSS specifically for the block editor.
 */
function scdkpi_enqueue_editor_specific_assets()
{
    // Enqueue Tailwind
    wp_enqueue_style(
        'scdkpi-editor-tailwind',
        plugins_url('assets/tailwind-built.css', SCDKPI_PLUGIN_FILE),
        array(),
        SCDKPI_VERSION
    );

    // MISSING LINK: Enqueue Custom CSS
    wp_enqueue_style(
        'scdkpi-editor-custom',
        plugins_url('assets/custom.css', SCDKPI_PLUGIN_FILE),
        array('scdkpi-editor-tailwind'),
        SCDKPI_VERSION
    );
}
add_action('enqueue_block_editor_assets', 'scdkpi_enqueue_editor_specific_assets');
