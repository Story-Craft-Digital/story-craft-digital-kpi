<?php
if (! defined('ABSPATH')) exit;

/**
 * Story Craft Digital KPI: Lite Submission API
 * Handles public form submissions and basic lead insertion.
 */

function scdkpi_register_rest_routes()
{
    $namespace = defined('SCDKPI_API_NAMESPACE') ? SCDKPI_API_NAMESPACE : 'story-craft-digital-kpi/v1';

    // Submit Form
    register_rest_route($namespace, '/lead-form-submit', array(
        'methods'             => 'POST',
        'callback'            => 'scdkpi_handle_public_submission',
        'permission_callback' => function ($request) {
            $home_url = home_url();

            // Parse the domain from the home URL
            $site_domain = wp_parse_url($home_url, PHP_URL_HOST);

            // Get the origin of the request
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_ORIGIN'])) : '';
            $origin_domain = wp_parse_url($origin, PHP_URL_HOST);

            // Compare them
            if ($origin_domain !== $site_domain) {
                return new WP_Error('forbidden', 'External submissions not allowed', ['status' => 403]);
            }
            return true;
        }
    ));
}
add_action('rest_api_init', 'scdkpi_register_rest_routes');

/**
 * Global Helper: Inserts a lead into SCD custom tables.
 * This is available for the Add-on to use as well.
 */
function scdkpi_insert_lead($data, $overrides = [])
{
    global $wpdb;
    $current_time = gmdate('Y-m-d H:i:s');

    // 1. PREPARE THE CHECKING CRITERIA
    $check_form_id   = sanitize_text_field($data['form_id']);
    $check_source    = sanitize_text_field($data['source'] ?? 'Unknown');
    $check_fname     = sanitize_text_field($data['fields']['firstName'] ?? '');
    $check_lname     = sanitize_text_field($data['fields']['lastName'] ?? '');
    $check_email     = sanitize_email($data['fields']['email'] ?? '');
    $check_phone     = sanitize_text_field($data['fields']['phone'] ?? '');
    $check_subject   = sanitize_text_field($data['fields']['subject'] ?? '');
    $check_type      = sanitize_text_field($data['fields']['type'] ?? '');

    // 2. CHECK FOR DUPLICATE
    $raw_string = $check_form_id . $check_source . $check_fname . $check_lname . $check_email . $check_phone . $check_subject . $check_type;
    $submission_hash = md5($raw_string);

    $duplicate_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT submission_id FROM {$wpdb->prefix}scdkpi_form_submissions WHERE submission_hash = %s LIMIT 1",
            $submission_hash
        )
    );

    if ($duplicate_id) {
        return $duplicate_id;
    }

    // 3. INSERT MAIN RECORD
    $status = isset($overrides['lead_status']) ? sanitize_text_field($overrides['lead_status']) : 'new';
    $owner  = isset($overrides['contact_owner_id']) ? intval($overrides['contact_owner_id']) : 0;

    $wpdb->insert(
        $wpdb->prefix . 'scdkpi_form_submissions',
        [
            'form_id' => $check_form_id,
            'block_id' => sanitize_text_field($data['block_id'] ?? 'external'),
            'entry_id' => sanitize_text_field($data['entry_id'] ?? ''),
            'user_id' => get_current_user_id(),
            'submission_hash' => $submission_hash,
            'lead_status' => $status,
            'contact_owner_id' => $owner,
            'submitted_at' => $current_time,
            'last_activity' => $current_time
        ]
    );
    $submission_id = $wpdb->insert_id;
    if (!$submission_id) return false;

    // 4. STORE META FIELDS
    if (isset($data['fields']) && is_array($data['fields'])) {
        foreach ($data['fields'] as $key => $value) {
            if ('' !== $value) {
                $wpdb->insert(
                    "{$wpdb->prefix}scdkpi_form_submission_meta",
                    array(
                        'submission_id' => (int) $submission_id,
                        'meta_key'      => sanitize_key($key),
                        'meta_value'    => sanitize_textarea_field(is_array($value) ? implode(', ', $value) : $value)
                    ),
                    array('%d', '%s', '%s')
                );
            }
        }
    }

    // Explicitly ensure formsource is set (if not already in fields)
    $cache_key = 'scdkpi_source_check_' . $submission_id;
    $existing_source = wp_cache_get($cache_key);

    if (false === $existing_source) {
        $existing_source = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->prefix}scdkpi_form_submission_meta WHERE submission_id = %d AND meta_key = 'formsource' LIMIT 1",
            (int) $submission_id
        ));
        wp_cache_set($cache_key, $existing_source);
    }
    if (!$existing_source) {
        $wpdb->insert($wpdb->prefix . 'scdkpi_form_submission_meta', [
            'submission_id' => $submission_id,
            'meta_key' => 'formsource',
            'meta_value' => $check_source
        ]);
    }

    // 5. TIMELINE LOGGING
    $name = trim("$check_fname $check_lname") ?: 'Lead';
    $note = "New Lead via $check_source\nName: $name\nEmail: $check_email\nPhone: $check_phone";

    // Only append owner info if an owner is actually assigned
    if ($owner > 0) {
        $owner_name = get_the_author_meta('display_name', $owner);
        if ($owner_name) {
            $note .= "\n(Assigned to " . $owner_name . ")";
        }
    }

    // Sanitize the final note before DB insertion
    $note = sanitize_textarea_field($note);

    $wpdb->insert(
        "{$wpdb->prefix}scdkpi_form_submission_timeline",
        array(
            'submission_id' => (int) $submission_id,
            'user_id'       => (int) get_current_user_id(),
            'type'          => 'submission',
            'note'          => sanitize_textarea_field($note),
            'created_at'    => $current_time
        ),
        array('%d', '%d', '%s', '%s', '%s') // ✅ Explicitly define formats
    );

    // Clear all relevant caches
    wp_cache_delete('scdkpi_total_leads_count');
    wp_cache_delete('scdkpi_latest_leads');

    return $submission_id;
}

/**
 * Callback: Public block submission
 */
function scdkpi_handle_public_submission(WP_REST_Request $request)
{
    $params = $request->get_json_params();

    // 1. Determine Data Structure
    // Public forms send data in 'formData'. Internal CRM Manual Entry sends data in root.
    // We prioritize 'formData', then fallback to $params.
    $fields = isset($params['formData']) ? $params['formData'] : $params;

    // 2. Determine Source
    // Check specific 'formsource' key or fallback to context
    $source = $params['formsource'] ?? $fields['formsource'] ?? (($params['formId'] === 'manual_entry') ? 'Manual Entry' : 'Website Form');

    $lead_data = [
        'form_id'  => sanitize_text_field($params['formId'] ?? 'internal_crm'),
        'block_id' => sanitize_text_field($params['blockId'] ?? 'manual'),
        'entry_id' => sanitize_text_field($params['entryId'] ?? 'man_' . time()),
        'source'   => $source,
        'fields'   => $fields // Contains the actual form fields (First Name, Email, etc)
    ];

    // 3. Clean Data: Remove system keys so they don't get saved as Meta
    $system_keys = ['formId', 'blockId', 'googleFormLink', 'fieldMappings', 'entryId', 'override_owner_id', 'override_status', 'formData', 'formsource', 'source', 'Source'];
    foreach ($system_keys as $key) {
        unset($lead_data['fields'][$key]);
    }

    // 4. Handle Overrides (Only for CRM Users)
    $overrides = [];
    $nonce = $request->get_header('X-WP-Nonce');

    // ONLY verify if the user is logged in AND attempting an override
    if (current_user_can('scdkpi_access_crm')) {
        if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
            if (!empty($params['override_owner_id'])) {
                $overrides['contact_owner_id'] = intval($params['override_owner_id']);
            }
            if (!empty($params['override_status'])) {
                $overrides['lead_status'] = sanitize_text_field($params['override_status']);
            }
        } else {
            // If they are an admin but the nonce is missing/invalid, 
            // we stop them for security.
            return new WP_Error('rest_cookie_invalid', 'Security check failed.', ['status' => 403]);
        }
    }
    // If they aren't an admin, we just skip this whole block and proceed 
    // to step 5 (normal lead insertion).

    // 5. Insert Lead (Using Shared Helper)
    $submission_id = scdkpi_insert_lead($lead_data, $overrides);

    if ($submission_id) {

        // 6. Handle Legacy Google Forms Sync (Specific to Public Forms only)
        $google_form_link = esc_url_raw($params['googleFormLink'] ?? '');
        $mappings = $params['fieldMappings'] ?? [];

        if (!empty($google_form_link) && !empty($mappings)) {
            $google_data = [];
            // Map the fields based on mapping configuration
            foreach ($mappings as $k => $v) {
                // Check both locations for data to be safe
                $val = $fields[$k] ?? null;
                if ($val !== null && !empty($v)) {
                    $google_data[$v] = $val;
                }
            }

            if (!empty($google_data)) {
                $res = wp_remote_post($google_form_link, ['body' => $google_data]);
                $note = is_wp_error($res) ? 'Google Sync Failed' : 'Google Sync Success';

                // Add note to timeline
                global $wpdb;
                $current_time = gmdate('Y-m-d H:i:s');
                $wpdb->insert(
                    $wpdb->prefix . 'scdkpi_form_submission_timeline',
                    ['submission_id' => $submission_id, 'user_id' => 0, 'type' => 'submission', 'note' => $note, 'created_at' => $current_time],
                    ['%d', '%d', '%s', '%s', '%s']
                );
            }
        }

        return new WP_REST_Response(['success' => true, 'submission_id' => $submission_id], 200);
    }

    return new WP_Error('db_error', 'Failed to save lead', ['status' => 500]);
}
