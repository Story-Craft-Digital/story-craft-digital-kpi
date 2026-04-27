<?php
if (! defined('ABSPATH')) exit;

/**
 * Returns the central mapping of placeholder strings to local asset paths.
 */
function scdkpi_get_asset_map() {
    return [
    // Background and System Assets
    'scdkpi-pink-purple-bg-image.avif'           => 'assets/images/defaults/scdkpi-pink-purple-bg-image.avif',
    'scdkpi-contact-form-image.png'              => 'assets/images/defaults/scdkpi-contact-form-image.png',
    'scdkpi-featured-card-placeholder.png'      => 'assets/images/defaults/scdkpi-featured-card-placeholder.png',

    // Brand Carousel Assets
    'scdkpi-brand-carousel-image-1.png'         => 'assets/images/defaults/scdkpi-brand-carousel-image-1.png',
    'scdkpi-brand-carousel-image-2.png'         => 'assets/images/defaults/scdkpi-brand-carousel-image-2.png',
    'scdkpi-brand-carousel-image-3.png'         => 'assets/images/defaults/scdkpi-brand-carousel-image-3.png',
    'scdkpi-brand-carousel-image-4.png'         => 'assets/images/defaults/scdkpi-brand-carousel-image-4.png',
    'scdkpi-brand-carousel-image-5.png'         => 'assets/images/defaults/scdkpi-brand-carousel-image-5.png',

    // Multi-Content Carousel Assets (Combined from both JSON blocks)
    'scdkpi-carousel-image-holistic-strategy.png'   => 'assets/images/defaults/scdkpi-carousel-image-holistic-strategy.png',
    'scdkpi-carousel-image-brand-evaluation-scaled.png'    => 'assets/images/defaults/scdkpi-carousel-image-brand-evaluation-scaled.png',
    'scdkpi-carousel-image-interface-design.png'    => 'assets/images/defaults/scdkpi-carousel-image-interface-design.png',
    'scdkpi-carousel-image-precision-marketing-scaled.png' => 'assets/images/defaults/scdkpi-carousel-image-precision-marketing-scaled.png',
    'scdkpi-carousel-image-strategic-content.png'   => 'assets/images/defaults/scdkpi-carousel-image-strategic-content.png',
    'scdkpi-carousel-image-visual-storytelling.png' => 'assets/images/defaults/scdkpi-carousel-image-visual-storytelling.png',
    'scdkpi-carousel-image-digital-echo-system.png' => 'assets/images/defaults/scdkpi-carousel-image-digital-echo-system.png',
    'scdkpi-carousel-image-revenue-scaling.png'     => 'assets/images/defaults/scdkpi-carousel-image-revenue-scaling.png',
    'scdkpi-carousel-image-narrative-mastery.png'   => 'assets/images/defaults/scdkpi-carousel-image-narrative-mastery.png',
    'scdkpi-carousel-image-future-tech-potrait.png' => 'assets/images/defaults/scdkpi-carousel-image-future-tech-potrait.png',

    // Multi-Image Carousel Card Assets
    'scdkpi-multi-image-carousel-card1-image-1.png' => 'assets/images/defaults/scdkpi-multi-image-carousel-card1-image-1.png',
    'scdkpi-multi-image-carousel-card1-image-2.png' => 'assets/images/defaults/scdkpi-multi-image-carousel-card1-image-2.png',
    'scdkpi-multi-image-carousel-card2-image-1.png' => 'assets/images/defaults/scdkpi-multi-image-carousel-card2-image-1.png',
    'scdkpi-multi-image-carousel-card2-image-2.png' => 'assets/images/defaults/scdkpi-multi-image-carousel-card2-image-2.png',

    // Testimonial Assets
    'scdkpi-testimonial-image-aisha.png'         => 'assets/images/defaults/scdkpi-testimonial-image-aisha.png',
    'scdkpi-testimonial-image-ben.png'           => 'assets/images/defaults/scdkpi-testimonial-image-ben.png',
    'scdkpi-testimonial-image-david.png'         => 'assets/images/defaults/scdkpi-testimonial-image-david.png',
    'scdkpi-testimonial-image-elena.png'         => 'assets/images/defaults/scdkpi-testimonial-image-elena.png',
    'scdkpi-testimonial-image-kenji.png'         => 'assets/images/defaults/scdkpi-testimonial-image-kenji.png',
    'scdkpi-testimonial-image-mark.png'          => 'assets/images/defaults/scdkpi-testimonial-image-mark.png',
    'scdkpi-testimonial-image-priya.png'         => 'assets/images/defaults/scdkpi-testimonial-image-priya.png',
    'scdkpi-testimonial-image-sarah.png'         => 'assets/images/defaults/scdkpi-testimonial-image-sarah.png',
];
}

/**
 * Register all blocks: core blocks first, then add-on blocks.
 */
function scdkpi_register_all_blocks() {
    // 1. Define the base path once to avoid errors
    $base_dir = plugin_dir_path( SCDKPI_PLUGIN_FILE );
    $manifest_path = $base_dir . 'build/blocks-manifest.php';
    
    if ( file_exists( $manifest_path ) ) {
        $blocks = include $manifest_path;
        
        if ( is_array( $blocks ) ) {
            foreach ( $blocks as $block_folder => $block_data ) {
                
                // 2. Point exactly to the nested blocks folder inside build
                $block_path = $base_dir . "build/blocks/{$block_folder}";

                // 3. Check for the block.json specifically in that subfolder
                if ( file_exists( $block_path . '/block.json' ) ) {
                    register_block_type( $block_path );
                }
            }
        }
    }
    
    // 4. Fire the action for pro-addons to override
    do_action( 'scdkpi_register_addon_blocks' );
}
add_action('init', 'scdkpi_register_all_blocks', 10);

/**
 * ✅ ADDED: Helper to recursively swap placeholders for full URLs
 */
function scdkpi_resolve_placeholders( $attrs, $map ) {
    foreach ( $attrs as $key => $value ) {
        if ( is_array( $value ) ) {
            $attrs[ $key ] = scdkpi_resolve_placeholders( $value, $map );
        } elseif ( is_string( $value ) && isset( $map[ $value ] ) ) {
            $attrs[ $key ] = plugins_url( $map[ $value ], SCDKPI_PLUGIN_FILE );
        }
    }
    return $attrs;
}

/**
 * ✅ ADDED: Filter to fix image paths on the frontend for ALL SCDKPI blocks
 */
add_filter( 'render_block_data', function( $block ) {
    // Only target blocks in your namespace
    if ( isset( $block['blockName'] ) && strpos( $block['blockName'], 'scdkpi/' ) === 0 ) {
        $map = scdkpi_get_asset_map();
        if ( ! empty( $block['attrs'] ) ) {
            $block['attrs'] = scdkpi_resolve_placeholders( $block['attrs'], $map );
        }
    }
    return $block;
}, 10 );

/**
 * Disable blocks in the Gutenberg editor based on SCD settings.
 */
add_filter('allowed_block_types_all', function ($allowed_block_types, $editor_context) {
    $disabled_blocks = get_option('scdkpi_disabled_blocks', []);

    // If no blocks are disabled, stay with default behavior
    if (empty($disabled_blocks)) {
        return $allowed_block_types;
    }

    $registry = WP_Block_Type_Registry::get_instance();
    $all_registered = array_keys($registry->get_all_registered());

    // Filter out the disabled blocks
    $allowed = array_diff($all_registered, $disabled_blocks);

    return array_values($allowed);
}, 10, 2);
