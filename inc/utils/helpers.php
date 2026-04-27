<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns the SVG markup for the SCD brand icon.
 * This can be used for the admin menu, block categories, etc.
 *
 * @return string SVG markup.
 */

/**
 * Helper to allow SVG tags through wp_kses
 */
function scdkpi_kses_allowed_html_svg() {
    return array(
        'svg' => array(
            'class'           => true,
            'aria-hidden'     => true,
            'aria-labelledby' => true,
            'role'            => true,
            'viewbox'         => true,
            'width'           => true,
            'height'          => true,
            'xmlns'           => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
            'stroke-linejoin' => true,
        ),
        'g' => array( // Essential for grouped paths
            'fill'      => true,
            'stroke'    => true,
            'opacity'   => true,
            'transform' => true,
        ),
        'path' => array(
            'd'               => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
            'stroke-linejoin' => true,
            'opacity'         => true,
            'transform'       => true,
        ),
        'rect' => array(
            'x'               => true,
            'y'               => true,
            'width'           => true,
            'height'          => true,
            'rx'              => true,
            'ry'              => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
            'stroke-linejoin' => true,
            'opacity'         => true,
            'transform'       => true,
        ),
        'circle' => array(
            'cx'              => true,
            'cy'              => true,
            'r'               => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'opacity'         => true,
            'transform'       => true,
        ),
        'line' => array(
            'x1'              => true,
            'y1'              => true,
            'x2'              => true,
            'y2'              => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
            'transform'       => true,
        ),
        'polyline' => array(
            'points'          => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
            'stroke-linejoin' => true,
            'transform'       => true,
        ),
        'polygon' => array(
            'points'          => true,
            'fill'            => true,
            'stroke'          => true,
            'stroke-width'    => true,
            'stroke-linecap'  => true,
            'stroke-linejoin' => true,
            'transform'       => true,
        ),
        'text' => array(
            'x'           => true,
            'y'           => true,
            'font-size'   => true,
            'text-anchor' => true,
            'fill'        => true,
            'font-family' => true,
            'font-weight' => true,
            'transform'   => true,
        ),
    );
}