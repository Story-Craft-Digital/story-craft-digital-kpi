<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
add_action('rest_api_init', function () {
    // ✅ Use the global constant for consistency
    $namespace = defined('SCDKPI_API_NAMESPACE') ? SCDKPI_API_NAMESPACE : 'story-craft-digital-kpi/v1';

    register_rest_route($namespace, '/extract-google-form-data', array(
        'methods'  => 'POST',
        'callback' => 'scdkpi_extract_google_form_data',
        'permission_callback' => function () {
            // Restrict to admins/editors who can manage the plugin
            return current_user_can('manage_options');
        },
    ));
});
function scdkpi_extract_google_form_data(WP_REST_Request $request)
{
    $params = $request->get_json_params();
    $url = isset($params['url']) ? esc_url_raw($params['url']) : '';

    if (empty($url)) {
        return new WP_Error('invalid_url', 'No valid URL provided', array('status' => 400));
    }

    if ( strpos( $url, 'docs.google.com/forms' ) === false ) {
        return new WP_Error('forbidden_url', 'Only Google Form URLs are permitted.', array('status' => 403));
    }

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return new WP_Error('fetch_error', 'Failed to fetch form', array('status' => 400));
    }

    $html = wp_remote_retrieve_body($response);
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Extract form action
    $form = $xpath->query('//form[contains(@action, "/formResponse")]')->item(0);
    $formAction = $form ? $form->getAttribute('action') : null;

    // Extract 'data-response' attribute from <form>
    $dataResponseAttr = $form ? $form->getAttribute('data-response') : '';

    $entryFields = [];
    if (!empty($dataResponseAttr)) {
        // Extracting entry fields from JSON-like structure
        preg_match_all('/\["(\d+)",\[[^,]+,\d+,\["(.*?)"\]/', $dataResponseAttr, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $entryFields[] = [
                'name' => 'entry.' . $match[1],
                'label' => $match[2],
            ];
        }
    }

    return new WP_REST_Response([
        'action' => $formAction,
        'entries' => $entryFields,
    ], 200);
}
