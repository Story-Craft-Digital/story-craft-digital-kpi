<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * A central library for block preview images.
 * Returns local URLs for block previews bundled with the plugin.
 */
function scdkpi_get_block_previews( $block_name ) {
    // Define the base path to your local images folder
    $base_url = plugins_url( 'assets/images/previews/block-previews/', SCDKPI_PLUGIN_FILE );

    $previews = [
        'scdkpi/scdkpi-blog-auto-scroller' => [
            'desktop' => $base_url . 'scdkpi-blog-auto-scroller-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-blog-auto-scroller-mobile.webp',
        ],
        'scdkpi/scdkpi-brand-slide-carousel' => [
            'desktop' => $base_url . 'scdkpi-brand-slide-carousel-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-brand-slide-carousel-mobile.webp',
        ],
        'scdkpi/scdkpi-feature-highlight-card' => [
            'desktop' => $base_url . 'scdkpi-feature-highlight-card-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-feature-highlight-card-mobile.webp',
        ],
        'scdkpi/scdkpi-multi-content-carousel' => [
            'desktop' => $base_url . 'scdkpi-multi-content-carousel-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-multi-content-carousel-mobile.webp',
        ],
        'scdkpi/scdkpi-multi-content-modern-carousel' => [
            'desktop' => $base_url . 'scdkpi-multi-content-modern-carousel-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-multi-content-modern-carousel-mobile.webp',
        ],
        'scdkpi/scdkpi-multi-image-hero-carousel' => [
            'desktop' => $base_url . 'scdkpi-multi-image-hero-carousel-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-multi-image-hero-carousel-mobile.webp',
        ],
        'scdkpi/scdkpi-smart-contact-form' => [
            'desktop' => $base_url . 'scdkpi-smart-contact-form-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-smart-contact-form-mobile.webp',
        ],
        'scdkpi/scdkpi-smart-feedback-form' => [
            'desktop' => $base_url . 'scdkpi-smart-feedback-form-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-smart-feedback-form-mobile.webp',
        ],
        'scdkpi/scdkpi-testimonials-carousel' => [
            'desktop' => $base_url . 'scdkpi-testimonials-carousel-desktop.webp',
            'mobile'  => $base_url . 'scdkpi-testimonials-carousel-mobile.webp',
        ],
        'scdkpi/blockname' => [
            'desktop' => $base_url . 'blockname-desktop.webp',
            'mobile'  => $base_url . 'blockname-mobile.webp',
        ],
    ];

    return $previews[ $block_name ] ?? ['desktop' => '', 'mobile' => ''];
}