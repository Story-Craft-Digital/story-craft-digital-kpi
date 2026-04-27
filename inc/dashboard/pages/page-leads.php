<?php

/**
 * Renders the content for the Blocks management page.
 *
 * @package Story_Craft_Digital_KPI
 */

if (! defined('ABSPATH')) {
    exit;
}
function scdkpi_render_leads_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }
    global $wpdb;
    $submissions_table = "{$wpdb->prefix}scdkpi_form_submissions";
    $meta_table        = "{$wpdb->prefix}scdkpi_form_submission_meta";

    // 1. User Info
    $current_user = wp_get_current_user();
    $first_name   = $current_user->user_firstname;
    $last_name    = $current_user->user_lastname;
    $full_name    = (!empty($first_name)) ? $first_name . ' ' . $last_name : $current_user->display_name;

    // 2. Parameters.
	$per_page = 15;
	$paged    = isset( $_GET['paged'] ) ? max( 1, intval( wp_unslash( $_GET['paged'] ) ) ) : 1;
	$offset   = ( $paged - 1 ) * $per_page;
	$search   = isset( $_GET['scdkpi_s'] ) ? sanitize_text_field( wp_unslash( $_GET['scdkpi_s'] ) ) : '';

	// Nonce Verification for Search.
	if ( ! empty( $search ) ) {
		check_admin_referer( 'scdkpi_search_leads', 'scdkpi_search_nonce' );
	}

	// Sorting logic.
	$orderby    = isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'submitted_at';
	$order      = ( isset( $_GET['order'] ) && 'ASC' === strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) ) ? 'ASC' : 'DESC';
	$next_order = ( 'ASC' === $order ) ? 'DESC' : 'ASC';

	// 3. Query Preparation.
	// We use a boolean to decide whether to include the JOIN.
	$is_searching = ! empty( $search );
	$search_term  = $is_searching ? '%' . $wpdb->esc_like( $search ) . '%' : '';

	// 4. Fetch Data - Total Count.
    $total_leads = wp_cache_get( 'scdkpi_total_leads_count' );
    if ( false === $total_leads ) {
        if ( $is_searching ) {
            $total_leads = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(DISTINCT s.submission_id) FROM {$wpdb->prefix}scdkpi_form_submissions s 
                LEFT JOIN {$wpdb->prefix}scdkpi_form_submission_meta m_search ON s.submission_id = m_search.submission_id 
                WHERE (s.submission_id LIKE %s OR s.form_id LIKE %s OR m_search.meta_value LIKE %s)",
                $search_term, $search_term, $search_term
            ) );
        } else {
            $total_leads = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT submission_id) FROM {$wpdb->prefix}scdkpi_form_submissions" );
        }
        wp_cache_set( 'scdkpi_total_leads_count', $total_leads );
    }

    $total_pages = ceil( $total_leads / $per_page );
    
    // 5. Fetch Data
    if ( $is_searching ) {
        if ( 'submission_id' === $orderby ) {
            if ( 'ASC' === strtoupper( $order ) ) {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT s.* FROM {$wpdb->prefix}scdkpi_form_submissions s LEFT JOIN {$wpdb->prefix}scdkpi_form_submission_meta m_search ON s.submission_id = m_search.submission_id WHERE (s.submission_id LIKE %s OR s.form_id LIKE %s OR m_search.meta_value LIKE %s) ORDER BY s.submission_id ASC LIMIT %d, %d", $search_term, $search_term, $search_term, (int) $offset, (int) $per_page ) );
            } else {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT s.* FROM {$wpdb->prefix}scdkpi_form_submissions s LEFT JOIN {$wpdb->prefix}scdkpi_form_submission_meta m_search ON s.submission_id = m_search.submission_id WHERE (s.submission_id LIKE %s OR s.form_id LIKE %s OR m_search.meta_value LIKE %s) ORDER BY s.submission_id DESC LIMIT %d, %d", $search_term, $search_term, $search_term, (int) $offset, (int) $per_page ) );
            }
        } elseif ( 'last_activity' === $orderby ) {
            if ( 'ASC' === strtoupper( $order ) ) {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT s.* FROM {$wpdb->prefix}scdkpi_form_submissions s LEFT JOIN {$wpdb->prefix}scdkpi_form_submission_meta m_search ON s.submission_id = m_search.submission_id WHERE (s.submission_id LIKE %s OR s.form_id LIKE %s OR m_search.meta_value LIKE %s) ORDER BY s.last_activity ASC LIMIT %d, %d", $search_term, $search_term, $search_term, (int) $offset, (int) $per_page ) );
            } else {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT s.* FROM {$wpdb->prefix}scdkpi_form_submissions s LEFT JOIN {$wpdb->prefix}scdkpi_form_submission_meta m_search ON s.submission_id = m_search.submission_id WHERE (s.submission_id LIKE %s OR s.form_id LIKE %s OR m_search.meta_value LIKE %s) ORDER BY s.last_activity DESC LIMIT %d, %d", $search_term, $search_term, $search_term, (int) $offset, (int) $per_page ) );
            }
        } else {
            if ( 'ASC' === strtoupper( $order ) ) {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT s.* FROM {$wpdb->prefix}scdkpi_form_submissions s LEFT JOIN {$wpdb->prefix}scdkpi_form_submission_meta m_search ON s.submission_id = m_search.submission_id WHERE (s.submission_id LIKE %s OR s.form_id LIKE %s OR m_search.meta_value LIKE %s) ORDER BY s.submitted_at ASC LIMIT %d, %d", $search_term, $search_term, $search_term, (int) $offset, (int) $per_page ) );
            } else {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT s.* FROM {$wpdb->prefix}scdkpi_form_submissions s LEFT JOIN {$wpdb->prefix}scdkpi_form_submission_meta m_search ON s.submission_id = m_search.submission_id WHERE (s.submission_id LIKE %s OR s.form_id LIKE %s OR m_search.meta_value LIKE %s) ORDER BY s.submitted_at DESC LIMIT %d, %d", $search_term, $search_term, $search_term, (int) $offset, (int) $per_page ) );
            }
        }
    } else {
        if ( 'submission_id' === $orderby ) {
            if ( 'ASC' === strtoupper( $order ) ) {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}scdkpi_form_submissions ORDER BY submission_id ASC LIMIT %d, %d", (int) $offset, (int) $per_page ) );
            } else {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}scdkpi_form_submissions ORDER BY submission_id DESC LIMIT %d, %d", (int) $offset, (int) $per_page ) );
            }
        } elseif ( 'last_activity' === $orderby ) {
            if ( 'ASC' === strtoupper( $order ) ) {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}scdkpi_form_submissions ORDER BY last_activity ASC LIMIT %d, %d", (int) $offset, (int) $per_page ) );
            } else {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}scdkpi_form_submissions ORDER BY last_activity DESC LIMIT %d, %d", (int) $offset, (int) $per_page ) );
            }
        } else {
            if ( 'ASC' === strtoupper( $order ) ) {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}scdkpi_form_submissions ORDER BY submitted_at ASC LIMIT %d, %d", (int) $offset, (int) $per_page ) );
            } else {
                $leads = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}scdkpi_form_submissions ORDER BY submitted_at DESC LIMIT %d, %d", (int) $offset, (int) $per_page ) );
            }
        }
    }

	wp_cache_delete( 'scdkpi_leads_count' );
?>


    <div class="wrap scdkpi-leads bg-slate-50 scdkpi-main-viewport">
        <?php
        // Simply call the function and assign the returned string to the variable
        $my_svg = scdkpi_get_logo_svg();

        // Pass the variable to your header function
        scdkpi_render_dashboard_header('SCD: Form Entries', $my_svg);
        ?>
        <div class="scdkpi-scroll-area">
            <div class=" bg-white overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-6 mb-6 p-4 rounded-2xl border border-slate-100 shadow-sm">

                <div class="flex items-center gap-3 shrink-0">
                    <span
                        class="w-12 h-12 flex items-center justify-center bg-purple-50 text-purple-600 rounded-xl font-black text-lg">
                        <?php echo (int) $total_leads; ?>
                    </span>
                    <span class="text-sm md:text-sm font-bold text-slate-400 uppercase tracking-widest"><?php esc_html_e('Total leads', 'story-craft-digital-kpi'); ?></span>
                </div>

                <form method="get" class="flex flex-col sm:flex-row flex-grow max-w-2xl items-stretch sm:items-center gap-2">
                    <?php
                    wp_nonce_field('scdkpi_search_leads', 'scdkpi_search_nonce');

                    $current_page_slug = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : 'scdkpi-form-entries';
                    ?>

                    <input type="hidden" name="page" value="<?php echo esc_attr($current_page_slug); ?>">

                    <div class="relative flex-grow flex items-center">
                        <div class="absolute left-0 w-10 md:w-12 flex items-center justify-center pointer-events-none z-20">
                            <svg class="h-3.5 w-3.5 md:h-4 md:w-4 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        <input type="text" name="scdkpi_s" value="<?php echo esc_attr($search); ?>"
                            placeholder="<?php esc_attr_e('Search name, phone...', 'story-craft-digital-kpi'); ?>" style="padding-left: 40px !important;"
                            class="w-full h-[40px] md:h-[44px] pr-4 bg-slate-50 border border-slate-200 rounded-xl text-xs md:text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all outline-none md:!pl-12">
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="purple-btn flex-grow sm:flex-grow-0 h-[40px] md:h-[44px] px-5 md:px-6 rounded-xl font-black text-[9px] md:text-[10px] uppercase tracking-widest flex items-center justify-center transition-all active:scale-95 whitespace-nowrap shadow-sm">
                            <?php esc_html_e('Search', 'story-craft-digital-kpi'); ?>
                        </button>

                        <?php if (!empty($search)): ?>
                            <a href="?page=<?php echo esc_attr($current_page_slug); ?>" style="text-decoration: none;"
                                class="h-[40px] md:h-[44px] px-4 flex items-center justify-center text-[9px] md:text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-red-500 border border-slate-100 hover:bg-red-50 rounded-xl transition-all">
                                <?php esc_html_e('Clear', 'story-craft-digital-kpi'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>


            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse min-w-[800px] md:min-w-full">
                    <thead class=" bg-slate-50">
                        <tr
                            class="border-b border-slate-100 text-[9px] md:text-[10px] uppercase font-black tracking-widest text-slate-400">
                            <th class="p-3 md:p-5 w-12 md:w-16">
                                <a href="<?php echo esc_url(add_query_arg(['orderby' => 'submission_id', 'order' => $next_order])); ?>"
                                    class="hover:text-purple-600 flex items-center gap-1">
                                    <?php esc_html_e('ID', 'story-craft-digital-kpi'); ?> <?php echo $orderby == 'submission_id' ? ($order == 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th class="p-3 md:p-5"><?php esc_html_e('Name', 'story-craft-digital-kpi'); ?></th>
                            <th class="p-3 md:p-5"><?php esc_html_e('Subject', 'story-craft-digital-kpi'); ?></th>
                            <th class="p-3 md:p-5"><?php esc_html_e('Communication', 'story-craft-digital-kpi'); ?></th>
                            <th class="p-3 md:p-5">
                                <a href="<?php echo esc_url(add_query_arg(['orderby' => 'submitted_at', 'order' => $next_order])); ?>"
                                    class="hover:text-purple-600 flex items-center gap-1">
                                    Date <?php echo $orderby == 'submitted_at' ? ($order == 'ASC' ? '↑' : '↓') : ''; ?>
                                </a>
                            </th>
                            <th class="p-3 md:p-5"><?php esc_html_e('Email', 'story-craft-digital-kpi'); ?></th>
                            <th class="p-3 md:p-5 text-right"><?php esc_html_e('Action', 'story-craft-digital-kpi'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        if ($leads) :
                            foreach ($leads as $lead) :
                                // ✅ FIXED: Used explicit prefix in query
                                $meta = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->prefix}scdkpi_form_submission_meta WHERE submission_id = %d", (int) $lead->submission_id), OBJECT_K);

                                $f_name        = isset($meta['firstname']) ? $meta['firstname']->meta_value : '';
                                $l_name        = isset($meta['lastname']) ? $meta['lastname']->meta_value : '';
                                $row_full_name = trim($f_name . ' ' . $l_name) ?: 'New Lead';
                                $phone         = isset($meta['phone']) ? $meta['phone']->meta_value : (isset($meta['mobile']) ? $meta['mobile']->meta_value : 'N/A');
                                $email         = isset($meta['email']) ? $meta['email']->meta_value : 'N/A';
                                $initial       = strtoupper(substr($f_name, 0, 1) . substr($l_name, 0, 1)) ?: '?';

                                $created_date = wp_date('M j, Y', strtotime($lead->submitted_at));
                                $created_time = wp_date('g:i A', strtotime($lead->submitted_at));
                        ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-3 md:p-5 text-[10px] md:text-xs text-slate-400 font-bold">
                                        #<?php echo esc_html((int) $lead->submission_id); ?></td>
                                    <td class="p-3 md:p-5">
                                        <div class="flex items-center gap-2 md:gap-3">
                                            <div
                                                class="w-6 h-6 md:w-8 md:h-8 shrink-0 rounded-full bg-purple-600 text-white flex items-center justify-center text-[8px] md:text-[10px] font-black border border-slate-200">
                                                <?php echo esc_html($initial); ?></div>
                                            <span
                                                class="font-bold text-slate-800 text-xs md:text-sm whitespace-nowrap"><?php echo esc_html($full_name); ?></span>
                                        </div>
                                    </td>
                                    <td class="p-3 md:p-5 text-xs md:text-sm text-slate-500 col-subject whitespace-nowrap">
                                        <?php echo esc_html(isset($meta['subject']->meta_value) ? $meta['subject']->meta_value : __('No Subject', 'story-craft-digital-kpi')); ?></td>

                                    <td class="p-3 md:p-5">
                                        <div class="flex items-center gap-2">
                                            <?php if ($phone !== 'N/A'):
                                                $whatsapp_phone = preg_replace('/[^0-9]/', '', $phone);
                                            ?>
                                                <a href="tel:<?php echo esc_attr($phone); ?>"
                                                    class="text-xs md:text-sm font-medium text-slate-600 hover:text-purple-600 transition-colors whitespace-nowrap">
                                                    <?php echo esc_html($phone); ?>
                                                </a>

                                                <a href="https://wa.me/<?php echo esc_attr($whatsapp_phone); ?>" target="_blank"
                                                    class="text-slate-400 hover:text-[#25D366] transition-colors"
                                                    title="Message on WhatsApp">
                                                    <svg class="w-3.5 h-3.5 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                                    </svg>
                                                </a>

                                                <button type="button" onclick="scdkpiCopy('<?php echo esc_js($phone); ?>', this)"
                                                    class="copy-btn !p-1">
                                                    <svg class="w-3 h-3 md:w-3.5 md:h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-xs md:text-sm text-slate-300">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="p-3 md:p-5">
                                        <div class="flex flex-col whitespace-nowrap">
                                            <span class="text-xs md:text-sm font-bold text-slate-700"><?php echo esc_html($created_date); ?></span>
                                            <span class="text-[9px] md:text-[10px] text-slate-400 uppercase font-medium"><?php echo esc_html($created_time); ?></span>
                                        </div>
                                    </td>

                                    <td class="p-3 md:p-5">
                                        <div class="flex items-center gap-2">
                                            <?php if ($email && $email !== 'N/A'): ?>
                                                <a href="mailto:<?php echo esc_attr(sanitize_email($email)); ?>"
                                                    class="text-xs md:text-sm text-slate-500 hover:text-purple-600 transition-colors whitespace-nowrap">
                                                    <?php echo esc_html($email); ?>
                                                </a>
                                                <button type="button" onclick="scdkpiCopy('<?php echo esc_js($email); ?>', this)"
                                                    class="copy-btn !p-1">
                                                    <svg class="w-3 h-3 md:w-3.5 md:h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-xs md:text-sm text-slate-300">N/A</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="p-3 md:p-5 text-right">
                                        <button type="button"
                                            class="scdkpi-view-lead-btn purple-btn px-3 md:px-5 py-1.5 md:py-2 rounded-lg md:rounded-xl text-[9px] md:text-[10px] font-black uppercase tracking-widest shadow-sm"
                                            data-id="<?php echo (int) $lead->submission_id; ?>"
                                            data-name="<?php echo esc_attr($row_full_name); ?>"
                                            data-phone="<?php echo esc_attr($phone); ?>"
                                            data-email="<?php echo esc_attr($email); ?>"
                                            data-meta='<?php echo esc_attr( wp_json_encode( $meta ) ); ?>' >
                                            <?php esc_html_e('View', 'story-craft-digital-kpi'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr>
                                <td colspan="7" class="p-0 border-none">
                                    <div
                                        class="sticky left-0 w-[calc(100vw-4rem)] md:w-full flex justify-center items-center py-12 px-6">
                                        <div class="text-center">
                                            <div
                                                class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 mb-3">
                                                <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            </div>
                                            <p class="text-slate-400 font-bold text-sm tracking-tight leading-tight">
                                                No Form Entries found.
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="scdkpi-lead-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center">
                <div id="scdkpi-lead-overlay" class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"></div>

                <div class="relative bg-white rounded-[2rem] shadow-2xl flex flex-col mx-4 overflow-hidden">

                    <div class="px-8 py-6 border-b border-slate-50 flex-shrink-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 id="m-name" class="text-xl font-black text-slate-800 mb-1"></h2>
                                <div class="flex items-center gap-2">
                                    <span id="m-id"
                                        class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100"></span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Lead
                                        details</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a id="m-wa" href="#" target="_blank"
                                    class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center hover:bg-green-600 hover:text-white transition-all"><svg
                                        class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                    </svg></a>
                                <a id="m-tel" href="#"
                                    class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all"><svg
                                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg></a>
                                <a id="m-mail" href="#"
                                    class="w-8 h-8 rounded-full bg-slate-50 text-slate-600 flex items-center justify-center hover:bg-slate-900 hover:text-white transition-all"><svg
                                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg></a>
                                <button onclick="scdkpiCloseModal()"
                                    class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:text-red-500 font-bold">✕</button>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow overflow-y-auto p-8 bg-slate-50/50" id="m-meta-container"></div>
                </div>
            </div>
        </div>
        <div class="scdkpi-footer-sticky">

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                <span class="text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-widest">
                    <?php
                    printf(
                        /* translators: 1: Current number of leads displayed, 2: Total number of leads available. */
                        esc_html(_n('Showing %1$s of %2$s Lead', 'Showing %1$s of %2$s Leads', (int) $total_leads, 'story-craft-digital-kpi')),
                        '<span>' . (int) count($leads) . '</span>',
                        '<span>' . (int) $total_leads . '</span>'
                    );
                    ?>
                </span>
            </div>

            <div class="flex items-center gap-1.5">

                <?php if ($paged > 1): ?>
                    <a href="<?php echo esc_url(add_query_arg(['paged' => $paged - 1, 'scdkpi_s' => $search])); ?>"
                        style="text-decoration: none;"
                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-purple-600 hover:border-purple-200 hover:bg-purple-50 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                <?php else: ?>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-200 border border-slate-100 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>
                <?php endif; ?>

                <div class="px-4 h-8 flex items-center bg-slate-50 border border-slate-100 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                        <?php
                        printf(
                            /* translators: 1: Current page number, 2: Total number of pages available. */
                            esc_html__('Page %1$s / %2$s', 'story-craft-digital-kpi'),
                            '<span class="text-purple-600 mx-1">' . (int) $paged . '</span>',
                            (int) $total_pages
                        );
                        ?>
                    </span>
                </div>

                <?php if ($paged < $total_pages): ?>
                    <a href="<?php echo esc_url(add_query_arg(['paged' => $paged + 1, 'scdkpi_s' => $search])); ?>"
                        style="text-decoration: none;"
                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-purple-600 hover:border-purple-200 hover:bg-purple-50 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                <?php else: ?>
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-200 border border-slate-100 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        function scdkpiCopy(text, btn) {
            // Attempt modern clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopyFeedback(btn);
                });
            } else {
                // Fallback for older browsers or non-HTTPS
                let textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopyFeedback(btn);
                } catch (err) {
                    console.error('Unable to copy', err);
                }
                document.body.removeChild(textArea);
            }
        }

        // Visual feedback function
        function showCopyFeedback(btn) {
            const originalHTML = btn.innerHTML;

            // Change to a green tick/checkmark
            btn.innerHTML = `
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            `;

            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 1500);
        }

        function scdkpiCloseModal() {
            document.getElementById('scdkpi-lead-modal').classList.add('hidden');
            document.body.classList.remove('scdkpi-modal-active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.scdkpi-view-lead-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const d = btn.dataset;
                    const meta = JSON.parse(d.meta);

                    document.getElementById('m-name').textContent = d.name;
                    document.getElementById('m-id').textContent = '#' + d.id;

                    const cleanPhone = d.phone.replace(/\D/g, '');
                    document.getElementById('m-wa').href = `https://wa.me/${cleanPhone}`;
                    document.getElementById('m-tel').href = `tel:${d.phone}`;
                    document.getElementById('m-mail').href = `mailto:${d.email}`;

                    let html = '';
                    // Add a simple escape helper at the top of your script
                    const escHtml = (str) => {
                        const p = document.createElement('p');
                        p.textContent = str;
                        return p.innerHTML;
                    };

                    // Then inside your forEach loop:
                    Object.entries(meta).forEach(([key, obj]) => {
                        if (obj.meta_value) {
                            const safeKey = escHtml(key.replace(/_/g, ' '));
                            const safeValue = escHtml(obj.meta_value);
                            html += `<div class="mb-4 border-b border-slate-100 pb-2">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">${safeKey}</label>
            <div class="text-sm font-bold text-slate-700">${safeValue}</div>
        </div>`;
                        }
                    });
                    document.getElementById('m-meta-container').innerHTML = html;
                    document.getElementById('scdkpi-lead-modal').classList.remove('hidden');
                    document.getElementById('scdkpi-lead-modal').classList.add('flex');
                    document.body.classList.add('scdkpi-modal-active');
                });
            });
            document.getElementById('scdkpi-lead-overlay').onclick = scdkpiCloseModal;
        });
    </script>
<?php
}
