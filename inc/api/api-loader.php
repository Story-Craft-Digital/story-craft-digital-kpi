<?php
/**
 * Story Craft Digital KPI API Loader
 * * Loads all public and free API functionality.
 *
 * @package Story_Craft_Digital_KPI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Load the Google Form Scraper (Free Version)
if ( file_exists( SCDKPI_PATH . 'inc/api/google-form-fields-fetch.php' ) ) {
    require_once SCDKPI_PATH . 'inc/api/google-form-fields-fetch.php';
}

// 2. Load the Lead Submission logic (The "Lite" version)
if ( file_exists( SCDKPI_PATH . 'inc/api/form-data-submit-lite.php' ) ) {
    require_once SCDKPI_PATH . 'inc/api/form-data-submit-lite.php';
}