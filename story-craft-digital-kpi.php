<?php
/**
 * Plugin Name:       Story Craft Digital KPI
 * Plugin URI:        https://storycraft.digital/plugins/
 * Description:       A high-performance business intelligence and lead management framework. Effortlessly track KPIs, manage leads, and extend functionality with SCD-compatible add-ons.
 * Version:           1.0.0
 * Author:            Story Craft Digital
 * Author URI:        https://storycraft.digital
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       story-craft-digital-kpi
 * Requires at least: 6.2
 * Requires PHP:      7.4
 *
 * @package           Story_Craft_Digital_KPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Define Constants
define( 'SCDKPI_VERSION', '1.0.0' );
define( 'SCDKPI_PLUGIN_FILE', __FILE__ );
define( 'SCDKPI_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'SCDKPI_API_NAMESPACE' ) ) {
	define( 'SCDKPI_API_NAMESPACE', 'story-craft-digital-kpi/v1' );
}

/**
 * 2. Helper to load files safely
 * Prevents a "White Screen of Death" if a file is missing.
 */
function scdkpi_load_file( $path ) {
	if ( file_exists( SCDKPI_PATH . $path ) ) {
		require_once SCDKPI_PATH . $path;
	}
}

// 3. Load components using the safety helper
function scdkpi_init() {
	$files = [
		'inc/database-tables/forms-database-table.php',
		'inc/api/api-loader.php',
		'inc/blocks.php',
		'inc/styles.php',
		'inc/scripts.php',
		'inc/dashboard/pages/partials/partials-loader.php',
		'inc/utils/utils-loader.php',
		'inc/block-category/block-category.php',
		'inc/rich-schemas/plugin-schema.php',
		'inc/dashboard/main.php',
		'inc/ajax/ajax-main.php',
	];

	foreach ( $files as $file ) {
		scdkpi_load_file( $file );
	}
}
add_action( 'plugins_loaded', 'scdkpi_init' );

// 4. Robust Activation
register_activation_hook( __FILE__, 'scdkpi_activate' );

function scdkpi_activate() {
	$db_file = 'inc/database-tables/forms-database-table.php';
	
	if ( file_exists( SCDKPI_PATH . $db_file ) ) {
		require_once SCDKPI_PATH . $db_file;
		if ( function_exists( 'scdkpi_lead_tables_activate' ) ) {
			scdkpi_lead_tables_activate();
		}
	}
}

/**
 * Check if SCD Pro is installed and active.
 */
function scdkpi_is_pro_active() {
    if ( defined( 'SCDKPI_PRO_VERSION' ) ) {
        return true;
    }

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Update this to your new Pro slug whenever you rename that as well
    return is_plugin_active( 'story-craft-digital-kpi-pro/story-craft-digital-kpi-pro.php' );
}



/**
 * ✅ ADD TO Story Craft Digital KPI MAIN FILE
 * Global REST Security: Protects the story-craft-digital-kpi namespace across all plugins.
 */
add_filter('rest_authentication_errors', 'scdkpi_rest_auth_check');
function scdkpi_rest_auth_check($result) {
    if (!empty($result)) {
        return $result;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	if ( empty( $request_uri ) ) {
		return $result;
	}
    
    // Dynamically check for your namespace
    $namespace = SCDKPI_API_NAMESPACE; 

    if ( false === strpos( $request_uri, $namespace ) ) {
		return $result;
	}

    // --- PUBLIC EXCEPTIONS ---
    $public_endpoints = array(
		'external-sync',
		'login',
		'lead-form-submit',
	);

    $is_public = false;
	foreach ( $public_endpoints as $endpoint ) {
		if ( false !== strpos( $request_uri, $namespace . '/' . $endpoint ) ) {
			$is_public = true;
			break;
		}
	}

    if (!is_user_logged_in() && !$is_public) {
        return new WP_Error(
            'rest_forbidden', 
            __('Unauthorized Access.', 'story-craft-digital-kpi'), 
            array('status' => 401)
        );
    }

    return $result;
};