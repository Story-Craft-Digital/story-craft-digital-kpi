<?php

/**
 * Main file for the admin dashboard (Core Version).
 */
if (! defined('ABSPATH')) exit;

/**
 * Register the main menu and essential sub-menus.
 */
function scdkpi_create_admin_menu()
{

    $icon_svg_string = scdkpi_get_logo_svg();
    $icon_data_uri = 'data:image/svg+xml;base64,' . base64_encode($icon_svg_string);

    // --- MAIN MENU ---
    add_menu_page(
        __('StoryCraft Digital', 'story-craft-digital-kpi'),
        __('Story Craft', 'story-craft-digital-kpi'),
        'manage_options',
        'scdkpi-dashboard',
        'scdkpi_render_dashboard_page',
        $icon_data_uri,
        25
    );

    // --- SUB MENUS ---
    // 1. Dashboard (The 'Home' of your plugin)
    add_submenu_page('scdkpi-dashboard', esc_html__('Dashboard', 'story-craft-digital-kpi'), esc_html__('Dashboard', 'story-craft-digital-kpi'), 'manage_options', 'scdkpi-dashboard', 'scdkpi_render_dashboard_page');

    // 2. Blocks Page (Crucial: Let users enable/disable features)
    add_submenu_page('scdkpi-dashboard', esc_html__('Blocks', 'story-craft-digital-kpi'), esc_html__('Blocks', 'story-craft-digital-kpi'), 'manage_options', 'scdkpi-blocks', 'scdkpi_render_blocks_page');

    // 3. Leads Page (Display all leads)
    add_submenu_page('scdkpi-dashboard', esc_html__('Form Entries', 'story-craft-digital-kpi'), esc_html__('Form Entries', 'story-craft-digital-kpi'), 'manage_options', 'scdkpi-form-entries', 'scdkpi_render_leads_page');

    // 4. Hook for external submenu
    do_action('scdkpi_after_main_submenus');

    // 5. Settings (Include your custom theme & former 'Tools' status info here)
    add_submenu_page('scdkpi-dashboard', esc_html__('Settings', 'story-craft-digital-kpi'), esc_html__('Settings', 'story-craft-digital-kpi'), 'manage_options', 'scdkpi-settings', 'scdkpi_render_settings_page');

    // 6. Help (Documentation/Support)
    add_submenu_page('scdkpi-dashboard', esc_html__('Help', 'story-craft-digital-kpi'), esc_html__('Help', 'story-craft-digital-kpi'), 'manage_options', 'scdkpi-help', 'scdkpi_render_help_page');

    // --- 🚀 THE PRO UPGRADE CTA ---
    $icon_svg = scdkpi_get_logo_svg();
    $base64_icon = base64_encode($icon_svg);

    // We create a custom label with your logo as a background-mask or icon
    $pro_label = sprintf(
        '<span style="display:inline-flex; align-items:center; color:#9813ca; font-weight:700;">' .
            '<small style="display:inline-block; width:16px; height:16px; margin-right:6px; background:url(data:image/svg+xml;base64,%s) no-repeat center; background-size:contain;"></small>' .
            '%s</span>',
        esc_attr($base64_icon),
        esc_html__('Upgrade to Pro', 'story-craft-digital-kpi')
    );

    add_submenu_page(
        'scdkpi-dashboard',
        __('Upgrade to Pro', 'story-craft-digital-kpi'),
        $pro_label,
        'manage_options',
        'scdkpi-upgrade-pro',
        'scdkpi_render_pro_page'
    );
}
add_action('admin_menu', 'scdkpi_create_admin_menu');

/**
 * Add a body class for SCD admin pages
 */
function scdkpi_add_admin_body_class( $classes ) {
    // Check if the current request is for one of our pages safely
    $current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    if ( $current_page && 0 === strpos( $current_page, 'scdkpi-' ) ) {
        $classes .= ' scdkpi-admin-page';
    }

    return $classes;
}
add_filter( 'admin_body_class', 'scdkpi_add_admin_body_class' );

/**
 * Helper: Handle the Pro Landing Page (Manual only)
 */
function scdkpi_render_pro_page()
{
?>
    <div class="wrap scdkpi-main-viewport h-[80vh] flex items-center justify-center">

        <div class="relative max-w-xl w-full rounded-[2.5rem] overflow-hidden shadow-2xl border border-purple-100 bg-gradient-to-br from-white via-white to-purple-50">

            <!-- background decoration -->
            <div class="absolute -top-10 -right-10 text-purple-100 opacity-40 pointer-events-none">
                <svg class="w-64 h-64" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41a14.98 14.98 0 00-6.16 12.12c1.86.12 3.63-.3 5.18-1.18m11.16-11.16a3 3 0 11-4.24-4.24 3 3 0 014.24 4.24z" />
                </svg>
            </div>

            <div class="relative p-10 text-center">

                <!-- Logo -->
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-purple-100 flex items-center justify-center text-purple-600 shadow-inner">
                    <?php echo wp_kses_post(scdkpi_get_logo_svg()); ?>
                </div>

                <!-- Title -->
                <h1 class="text-3xl font-black text-slate-800 mb-3 tracking-tight">
                    <?php esc_html_e('Story Craft Digital', 'story-craft-digital-kpi'); ?>
                </h1>

                <p class="text-slate-500 text-lg mb-8 leading-relaxed max-w-md mx-auto">
                    <?php esc_html_e('Access advanced automation, premium blocks, and powerful lead management tools designed to scale your marketing workflows.', 'story-craft-digital-kpi'); ?>
                </p>

                <!-- Feature preview -->
                <div class="grid grid-cols-1 gap-3 text-sm mb-8 text-left max-w-sm mx-auto">

                    <div class="flex items-center gap-3 text-slate-600">
                        <span class="text-purple-500">✔</span>
                        <?php esc_html_e('Advanced Lead Automation', 'story-craft-digital-kpi'); ?>
                    </div>

                    <div class="flex items-center gap-3 text-slate-600">
                        <span class="text-purple-500">✔</span>
                        <?php esc_html_e('Premium Conversion Blocks', 'story-craft-digital-kpi'); ?>
                    </div>

                    <div class="flex items-center gap-3 text-slate-600">
                        <span class="text-purple-500">✔</span>
                        <?php esc_html_e('AI-Powered Marketing Tools', 'story-craft-digital-kpi'); ?>
                    </div>
                </div>

                <!-- CTA -->
                <a href="https://storycraft.digital"
                    target="_blank"
                    class="block w-full py-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold rounded-2xl hover:scale-[1.02] hover:shadow-xl transition-all no-underline text-lg">
                    <?php esc_html_e('Explore Story Craft Digital', 'story-craft-digital-kpi'); ?>
                </a>

                <!-- secondary -->
                <button onclick="window.history.back();"
                    class="mt-4 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors bg-transparent border-none cursor-pointer">
                    <?php esc_html_e('← Stay on this site', 'story-craft-digital-kpi'); ?>
                </button>

                <!-- footer -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <p class="text-[10px] text-slate-300 uppercase font-bold tracking-widest">
                        <?php esc_html_e('Secure external redirect', 'story-craft-digital-kpi'); ?>
                    </p>
                </div>

            </div>
        </div>
    </div>
<?php
}

/**
 * Global Enqueue for Admin Pages
 */
function scdkpi_enqueue_admin_assets( $hook ) {
    $current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

    if ( $current_page && 0 === strpos( $current_page, 'scdkpi-' ) ) {
        wp_enqueue_script( 'scdkpi-shared-libs' );
        wp_enqueue_style( 'scdkpi-shared-styles' );
    }
}
add_action( 'admin_enqueue_scripts', 'scdkpi_enqueue_admin_assets' );

/**
 * Loading Page Templates
 */
require_once __DIR__ . '/pages/page-dashboard.php';
require_once __DIR__ . '/pages/page-blocks.php';
require_once __DIR__ . '/pages/page-leads.php';
require_once __DIR__ . '/pages/page-settings.php';
require_once __DIR__ . '/pages/page-help.php';
