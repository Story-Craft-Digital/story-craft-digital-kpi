<?php
/**
 * Main loader for Story Craft Digital KPI dashboard partials.
 *
 * @package Story_Craft_Digital_KPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$scdkpi_header_path = __DIR__ . '/scdkpi-dashboard-admin-header.php';

if ( file_exists( $scdkpi_header_path ) ) {
	require_once $scdkpi_header_path;
}