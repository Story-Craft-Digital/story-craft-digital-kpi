<?php
/**
 * Fired when the plugin is uninstalled.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure uninstall is called properly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

/**
 * List of custom tables to drop
 */
$scdkpi_tables = [
    $wpdb->prefix . 'scdkpi_form_submissions',
    $wpdb->prefix . 'scdkpi_form_submission_meta',
    $wpdb->prefix . 'scdkpi_form_submission_timeline',
    $wpdb->prefix . 'scdkpi_tasks',
    $wpdb->prefix . 'scdkpi_task_logs',
    $wpdb->prefix . 'scdkpi_chats',
    $wpdb->prefix . 'scdkpi_notifications',
];

foreach ( $scdkpi_tables as $scdkpi_table ) {
	$wpdb->query( "DROP TABLE IF EXISTS " . esc_sql( $scdkpi_table ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
}

// 1. Delete main settings and disabled blocks list
delete_option( 'scdkpi_settings' );
delete_option( 'scdkpi_disabled_blocks' );

// 2. Clear any update check transients
delete_transient( 'scdkpi_update_check' );

// 3. Optional: Clear versioning data
delete_option( 'scdkpi_version' );

wp_cache_flush();