<?php
if (! defined('ABSPATH')) exit;

/**
 * Register shared libraries early.
 */
function scdkpi_register_shared_libs()
{
    $asset_file = plugin_dir_path(SCDKPI_PLUGIN_FILE) . 'build/shared.asset.php';

    if ( ! file_exists( $asset_file ) ) {
		return;
	}

    $asset_data = include $asset_file;

    // 1. Register shared JavaScript
    wp_register_script(
        'scdkpi-shared-libs',
        plugins_url('build/shared.js', SCDKPI_PLUGIN_FILE),
        $asset_data['dependencies'],
        $asset_data['version'],
        false // Load in head to ensure it's available early
    );

    // ✅ 2. THE BRIDGE: Attach the settings to the script we just registered
    wp_localize_script('scdkpi-shared-libs', 'scdkpiSettings', array(
        'root'      => esc_url_raw(rest_url()),
        'namespace' => defined('SCDKPI_API_NAMESPACE') ? SCDKPI_API_NAMESPACE : 'story-craft-digital-kpi/v1',
        'nonce'     => wp_create_nonce('wp_rest'),
        'pluginUrl' => plugins_url('/', SCDKPI_PLUGIN_FILE),
        'assetMap'  => scdkpi_get_asset_map(),
    ));

    // 3. Register shared CSS
    $shared_css = plugin_dir_path(SCDKPI_PLUGIN_FILE) . 'build/shared.css';
    if (file_exists($shared_css)) {
        wp_register_style(
            'scdkpi-shared-styles',
            plugins_url('build/shared.css', SCDKPI_PLUGIN_FILE),
            array(),
            $asset_data['version']
        );
    }
}
add_action('init', 'scdkpi_register_shared_libs', 5);

/**
 * Enqueue shared libs in block editor with high priority.
 */
function scdkpi_enqueue_editor_libs()
{
    wp_enqueue_script('scdkpi-shared-libs');

    if (wp_style_is('scdkpi-shared-styles', 'registered')) {
        wp_enqueue_style('scdkpi-shared-styles');
    }
}
add_action('enqueue_block_editor_assets', 'scdkpi_enqueue_editor_libs', 5);


/**
 * Global helper to load shared assets.
 * Call this function from any admin page to load React, Flatpickr, etc.
 */
function scdkpi_load_shared_assets()
{
    wp_enqueue_script('scdkpi-shared-libs');
    wp_enqueue_style('scdkpi-shared-styles');
}
