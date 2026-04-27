<?php

/**
 * Renders the content for the Blocks management page.
 *
 * @package Story_Craft_Digital_KPI
 */

if (! defined('ABSPATH')) {
    exit;
}
function scdkpi_render_blocks_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    // 1. SAVE LOGIC (Run this first)
    if (isset($_POST['scdkpi_save_blocks_settings']) && check_admin_referer('scdkpi_save_blocks_settings', 'scdkpi_blocks_nonce')) {
        $enabled_from_form = isset($_POST['enabled_blocks']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['enabled_blocks'])) : array();

        // We need all registered blocks to compare.
        $all_registered      = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $new_disabled_blocks = array();

        foreach ($all_registered as $name => $settings) {
            // Only care about your specific category
            if (isset($settings->category) && $settings->category === 'scdkpi-story-craft-digital') {
                if (! in_array($name, $enabled_from_form, true)) {
                    $new_disabled_blocks[] = $name;
                }
            }
        }

        update_option('scdkpi_disabled_blocks', $new_disabled_blocks);

        // Show a native WordPress success notice
?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'story-craft-digital-kpi'); ?></p>
        </div>
    <?php
    }

    // 2. DATA RETRIEVAL (Run this second so it gets the UPDATED options)
    $all_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
    $scd_blocks = array_filter($all_blocks, function ($block) {
        return isset($block->category) && $block->category === 'scdkpi-story-craft-digital';
    });

    // This will now fetch the fresh data we just saved above
    $disabled_blocks = get_option('scdkpi_disabled_blocks', []);

    ?>

    <style>
        /* Fixed proportion for mobile frame */
        .device-mobile-frame {
            width: 240px;
            aspect-ratio: 9 / 18.5;
            height: auto;
            display: flex;
            flex-direction: column;
        }

        /* 1. Center the preview image and prevent internal scrolling */
        .preview-window-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            overflow: hidden;
            /* Prevent internal scroll */
        }

        .preview-window-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            /* Keeps image aspect ratio and centers it */
        }

        /* 2. Custom scrollbar for the POPUP modal itself */
        .scdkpi-modal-scrollable {
            overflow-y: auto !important;
            scrollbar-width: thin;
            scrollbar-color: #9333ea #f1f5f9;
        }

        .scdkpi-modal-scrollable::-webkit-scrollbar {
            width: 6px;
        }

        .scdkpi-modal-scrollable::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .scdkpi-modal-scrollable::-webkit-scrollbar-thumb {
            background: #9333ea;
            border-radius: 10px;
        }

        /* GLOBAL MODERN SCROLLBAR */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(147, 51, 234, 0.6) transparent;
        }

        /* Chrome, Edge, Safari */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
            margin: 6px 0;
            /* spacing top/bottom */
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg,
                    rgba(147, 51, 234, 0.7),
                    rgba(147, 51, 234, 0.4));
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: content-box;
            transition: all 0.3s ease;
        }

        /* Hover effect */
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg,
                    rgba(147, 51, 234, 0.9),
                    rgba(147, 51, 234, 0.6));
        }

        /* Active (when dragging) */
        .custom-scrollbar::-webkit-scrollbar-thumb:active {
            background: rgba(147, 51, 234, 1);
        }

        .custom-scrollbar {
            scroll-behavior: smooth;
        }

        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            box-shadow: 0 0 6px rgba(147, 51, 234, 0.4);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
    </style>

    <div class="wrap scdkpi-blocks bg-slate-50 scdkpi-main-viewport">
        <?php
        // Simply call the function and assign the returned string to the variable
        $my_svg = scdkpi_get_logo_svg();

        // Pass the variable to your header function
        scdkpi_render_dashboard_header('SCD: Blocks', $my_svg);
        ?>
        <div class="scdkpi-scroll-area">
            <form method="post" action="" class="flex flex-col flex-grow overflow-hidden">
                <?php wp_nonce_field('scdkpi_save_blocks_settings', 'scdkpi_blocks_nonce'); ?>

                <div class="scdkpi-sticky-header mb-8 flex flex-col md:flex-row md:items-center justify-between bg-white p-4 md:p-5 rounded-2xl border border-slate-200 shadow-sm gap-4">
                    <div class="flex items-start gap-3 md:gap-4 flex-1">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 flex-shrink-0">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>

                        <div>
                            <span class="font-bold text-base md:text-lg text-slate-800 block leading-tight"><?php esc_html_e('Block Performance', 'story-craft-digital-kpi'); ?></span>
                            <p class="text-xs md:text-sm text-slate-500 mt-1">
                                <?php esc_html_e('Optimize your workflow by disabling unused blocks.', 'story-craft-digital-kpi'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="flex-shrink-0">
                        <button type="submit" name="scdkpi_save_blocks_settings" class="w-full md:w-auto px-6 md:px-8 py-2.5 md:py-3 bg-purple-600 text-white text-xs md:text-sm font-bold rounded-xl hover:bg-purple-700 transition-all duration-300 shadow-lg shadow-purple-100 cursor-pointer border-none">
                            <?php esc_attr_e('Save Changes', 'story-craft-digital-kpi'); ?>
                        </button>
                    </div>
                </div>
                <?php if (empty($scd_blocks)) : ?>
                    <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                        <p class="text-slate-400 font-medium"><?php esc_html_e('No blocks found in this category.', 'story-craft-digital-kpi'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <?php foreach ($scd_blocks as $block) : ?>
                            <?php $is_active = ! in_array($block->name, $disabled_blocks); ?>

                            <div class="group bg-white rounded-2xl border border-slate-200 p-5 transition-all duration-300 hover:shadow-xl hover:border-purple-300 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between">
                                        <div class="flex gap-4">
                                            <div class="w-12 h-12 flex items-center justify-center bg-purple-50 rounded-xl text-purple-600 transition-all duration-300 flex-shrink-0">
                                                <?php
                                                echo wp_kses(scdkpi_get_block_icon_svg($block->name), scdkpi_kses_allowed_html_svg()); ?>
                                            </div>

                                            <div>
                                                <h3 class="text-base font-bold text-slate-800 m-0 leading-tight">
                                                    <?php echo esc_html($block->title); ?>
                                                </h3>

                                                <div class="flex items-center gap-3 mt-2">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="scdkpi-status-indicator">
                                                            <?php if ($is_active) : ?>
                                                                <span class="scdkpi-status-ping"></span>
                                                            <?php endif; ?>
                                                            <span class="<?php echo $is_active ? 'scdkpi-status-dot-active' : 'scdkpi-status-dot'; ?>"></span>
                                                        </span>
                                                        <span class="text-[10px] font-bold uppercase tracking-wider <?php echo $is_active ? 'text-green-600' : 'text-slate-400'; ?>">
                                                            <?php echo $is_active ? esc_html__('Active', 'story-craft-digital-kpi') : esc_html__('Inactive', 'story-craft-digital-kpi'); ?>
                                                        </span>
                                                    </div>

                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            name="enabled_blocks[]"
                                                            value="<?php echo esc_attr($block->name); ?>"
                                                            class="sr-only peer"
                                                            <?php checked($is_active); ?>>
                                                        <div class="w-7 h-4 bg-slate-200 rounded-full peer peer-focus:ring-0 peer-checked:after:translate-x-3 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-purple-600"></div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <?php $previews = scdkpi_get_block_previews($block->name); ?>
                                        <?php if (! empty($previews['desktop'])) : ?>
                                            <button
                                                type="button"
                                                class="scdkpi-block-preview-trigger w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-purple-100 hover:text-purple-600 transition-all flex items-center justify-center flex-shrink-0"
                                                data-title="<?php echo esc_attr($block->title); ?>"
                                                data-name="<?php echo esc_attr($block->name); ?>"
                                                data-description="<?php echo esc_attr($block->description); ?>"
                                                data-desktop-src="<?php echo esc_url($previews['desktop']); ?>"
                                                data-mobile-src="<?php echo esc_url($previews['mobile']); ?>">
                                                <span class="w-4 h-4">
                                                    <?php echo wp_kses(scdkpi_info_svg(), scdkpi_kses_allowed_html_svg()); ?>
                                                </span>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="group/desc relative mt-4">
                                        <p class="text-sm text-slate-500 line-clamp-2 leading-snug">
                                            <?php echo esc_html($block->description); ?>
                                        </p>
                                        <div class="invisible opacity-0 group-hover/desc:visible group-hover/desc:opacity-100 absolute bottom-full left-0 mb-2 w-full min-w-[200px] p-3 bg-slate-800 text-white text-xs rounded-lg shadow-xl transition-all duration-200 z-10">
                                            <?php echo esc_html($block->description); ?>
                                            <div class="absolute top-full left-4 border-4 border-transparent border-t-slate-800"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>



                <div id="scdkpi-block-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
                    <div id="scdkpi-block-modal-overlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>

                    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-4xl m-auto max-h-[90vh] flex flex-col transition-all duration-300 scale-95 opacity-0 overflow-hidden">

                        <div class="px-4 sm:px-6 py-4 border-b border-slate-100 bg-white z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-start sm:items-center gap-3 w-full">
                                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 id="scdkpi-block-modal-title" class="text-base sm:text-xl font-bold text-slate-800 leading-tight mb-1"></h2>
                                    <p id="scdkpi-block-modal-name" class="text-[10px] text-slate-400 font-mono uppercase tracking-widest"></p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-3">
                                <div id="scdkpi-block-modal-tabs" class="grid grid-cols-2 w-full sm:flex sm:w-auto p-1 bg-slate-100 rounded-xl">
                                    <button data-tab="desktop" class="w-full sm:w-auto px-3 py-1.5 text-xs font-bold rounded-lg transition-all duration-200"><?php esc_html_e('Desktop', 'story-craft-digital-kpi'); ?></button>
                                    <button data-tab="mobile" py-1.5 text-xs font-bold rounded-lg transition-all duration-200"><?php esc_html_e('Mobile', 'story-craft-digital-kpi'); ?></button>
                                </div>
                                <button id="scdkpi-block-modal-close" type="button" class="w-8 h-8 flex items-center justify-center rounded-full !bg-slate-50 !text-slate-600 hover:bg-red-50 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-grow bg-slate-50 min-h-[500px] max-h-[80vh] overflow-y-auto custom-scrollbar">
                            <div class="p-4 sm:p-8 flex justify-center">

                                <div id="scdkpi-block-modal-content-desktop" class="tab-panel w-full h-full max-w-4xl mx-auto animate-in fade-in flex flex-col items-center justify-center">

                                    <div class="w-full max-w-[80%] aspect-[15/10] bg-slate-800 p-1.5 rounded-xl shadow-2xl border-[6px] border-slate-800 flex flex-col">

                                        <div class="bg-white rounded-md overflow-hidden flex flex-col flex-grow">

                                            <div class="bg-slate-100 px-3 py-1.5 flex items-center gap-2 border-b flex-shrink-0">
                                                <div class="flex gap-1">
                                                    <div class="w-2 h-2 rounded-full bg-red-400"></div>
                                                    <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                                    <div class="w-2 h-2 rounded-full bg-green-400"></div>
                                                </div>
                                            </div>

                                            <div class="bg-white overflow-y-auto custom-scrollbar flex-grow flex items-center justify-center">

                                                <img id="scdkpi-desktop-img" src="" alt="Desktop" class="w-auto h-auto max-w-full max-h-full block border-none">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-24 h-2 bg-slate-700 shadow-inner"></div>
                                    <div class="w-40 h-1.5 bg-slate-800 rounded-b-xl shadow-lg"></div>
                                </div>

                                <div id="scdkpi-block-modal-content-mobile" class="tab-panel hidden h-full animate-in fade-in flex items-center justify-center">
                                    <div class="device-mobile-frame relative mx-auto border-[8px] border-slate-900 rounded-[2.5rem] shadow-2xl bg-slate-900 overflow-hidden ring-4 ring-slate-800/10">
                                        <div class="absolute top-0 inset-x-0 h-7 bg-slate-900 z-20">
                                            <div class="mt-2.5 mx-auto w-16 h-3.5 bg-black rounded-full"></div>
                                        </div>

                                        <div class="h-full w-full bg-white pt-8 overflow-y-auto custom-scrollbar flex items-center justify-center">

                                            <img id="scdkpi-mobile-img" src="" alt="Mobile" class="w-auto h-auto max-w-full max-h-full block">

                                        </div>
                                        <div class="absolute bottom-1 inset-x-0 h-1 w-20 mx-auto bg-slate-300 rounded-full z-20 opacity-40"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-white border-t border-slate-100">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2"><?php esc_html_e('About this block', 'story-craft-digital-kpi'); ?></h4>
                                <p id="scdkpi-block-modal-description" class="text-sm text-slate-600 leading-relaxed max-w-2xl"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('scdkpi-block-modal');
            if (!modal) return;

            const overlay = document.getElementById('scdkpi-block-modal-overlay');
            const modalContent = modal.querySelector('.relative');
            const closeButton = document.getElementById('scdkpi-block-modal-close');
            const previewButtons = document.querySelectorAll('.scdkpi-block-preview-trigger');

            const titleEl = document.getElementById('scdkpi-block-modal-title');
            const nameEl = document.getElementById('scdkpi-block-modal-name');
            const descriptionEl = document.getElementById('scdkpi-block-modal-description');
            const desktopImg = document.getElementById('scdkpi-desktop-img');
            const mobileImg = document.getElementById('scdkpi-mobile-img');

            const tabsContainer = document.getElementById('scdkpi-block-modal-tabs');
            const tabButtons = tabsContainer.querySelectorAll('button');
            const tabPanels = modal.querySelectorAll('.tab-panel');

            function openModal(button) {
                // Set content
                titleEl.textContent = button.dataset.title;
                nameEl.textContent = button.dataset.name;
                descriptionEl.textContent = button.dataset.description;
                desktopImg.src = button.dataset.desktopSrc;
                mobileImg.src = button.dataset.mobileSrc;

                // Reset to Desktop tab
                switchTab(tabButtons[0]);

                // Reveal Modal
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';

                // Animate in
                requestAnimationFrame(() => {
                    overlay.classList.replace('opacity-0', 'opacity-100');
                    modalContent.classList.replace('opacity-0', 'opacity-100');
                    modalContent.classList.replace('scale-95', 'scale-100');
                });
            }

            function closeModal() {
                overlay.classList.replace('opacity-100', 'opacity-0');
                modalContent.classList.replace('opacity-100', 'opacity-0');
                modalContent.classList.replace('scale-100', 'scale-95');

                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    desktopImg.src = '';
                    mobileImg.src = '';
                    document.body.style.overflow = '';
                }, 300);
            }

            function switchTab(clickedTab) {
                const targetTab = clickedTab.dataset.tab;
                const targetPanelId = 'scdkpi-block-modal-content-' + targetTab;

                tabButtons.forEach(tab => {
                    const isActive = tab === clickedTab;
                    tab.className = isActive ?
                        'px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 bg-white text-purple-600 shadow-sm' :
                        'px-4 py-1.5 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700';
                });

                tabPanels.forEach(panel => {
                    panel.classList.toggle('hidden', panel.id !== targetPanelId);
                });
            }

            previewButtons.forEach(btn => btn.addEventListener('click', e => openModal(btn)));

            // ✅ UPDATE this listener:
            tabButtons.forEach(btn => btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation(); // Stop the click from traveling to the overlay
                switchTab(btn);
            }));

            closeButton.addEventListener('click', closeModal);

            // ✅ REPLACE your overlay listener with this:
            overlay.addEventListener('click', function(e) {
                // Only close if the click was exactly on the dark overlay, 
                // not on anything inside the modal.
                if (e.target === overlay) {
                    closeModal();
                }
            });
            document.addEventListener('keydown', e => e.key === "Escape" && closeModal());
        });
    </script>
<?php
}
