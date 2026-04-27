<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Registers the custom 'Story Craft Digital' block category.
 */
function scdkpi_register_block_category( $categories ) {
    $category_slugs = wp_list_pluck( $categories, 'slug' );
    if ( in_array( 'scdkpi-story-craft-digital', $category_slugs, true ) ) {
        return $categories;
    }

     array_unshift( $categories, [
        'slug'  => 'scdkpi-story-craft-digital',
        'title' => __( 'Story Craft Digital', 'story-craft-digital-kpi' ),
        // The icon will still be added via JavaScript.
    ]);

    return $categories;
}
add_filter( 'block_categories_all', 'scdkpi_register_block_category' );

/**
 * Enqueues the JS for the block category and passes the SVG icon to it.
 */
function scdkpi_enqueue_category_assets() {
    $script_handle = 'scdkpi-category-icon-script';

    wp_enqueue_script(
        $script_handle,
        plugin_dir_url( __FILE__ ) . 'block-category.js',
        [ 'wp-blocks', 'wp-dom-ready', 'wp-element' ],
        SCDKPI_VERSION, // Using your specific version constant
        true
    );

    $svg_icon = scdkpi_get_category_logo_for_category_svg();

    // Pass the SVG string as a JavaScript variable to our script.
    $script_data = sprintf(
        'var scdkpiCategoryIconSVG = %s;',
        wp_json_encode( $svg_icon )
    );
    wp_add_inline_script( $script_handle, $script_data, 'before' );
}
add_action( 'enqueue_block_editor_assets', 'scdkpi_enqueue_category_assets' );

