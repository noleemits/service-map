<?php

// Register the REST API route
add_action('rest_api_init', function () {
    register_rest_route('myplugin/v1', '/save_service_areas', array(
        'methods'  => 'POST',
        'callback' => 'myplugin_save_service_areas',
        'permission_callback' => function () {
            return current_user_can('manage_options'); // Restrict access to admins or appropriate roles
        }
    ));
});

// Callback function to save service areas
function myplugin_save_service_areas(WP_REST_Request $request) {
    $included_service_areas = $request->get_param('included_service_areas');
    $excluded_service_areas = $request->get_param('excluded_service_areas');

    // Validate and decode the input JSON
    $included_service_areas = json_decode($included_service_areas, true);
    $excluded_service_areas = json_decode($excluded_service_areas, true);

    if (!is_array($included_service_areas) || !is_array($excluded_service_areas)) {
        return new WP_Error('invalid_data', 'Invalid data format', array('status' => 400));
    }

    // Get the current user ID
    $user_id = get_current_user_id();

    // Find or create the user's user_map post
    $user_map_post = get_posts(array(
        'post_type'      => 'user_map',
        'author'         => $user_id,
        'posts_per_page' => 1,
    ));

    if ($user_map_post) {
        $user_map_id = $user_map_post[0]->ID;

        // Get existing values for comparison
        $existing_selected_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'selected_service_areas', true));
        $existing_not_covered_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'not_covered_service_areas', true));

        // Ensure arrays are initialized to avoid data loss
        $existing_selected_service_areas = is_array($existing_selected_service_areas) ? $existing_selected_service_areas : [];
        $existing_not_covered_service_areas = is_array($existing_not_covered_service_areas) ? $existing_not_covered_service_areas : [];

        // Update only if there are actual changes
        if ($included_service_areas !== $existing_selected_service_areas) {
            update_post_meta($user_map_id, 'selected_service_areas', $included_service_areas);
        }

        if ($excluded_service_areas !== $existing_not_covered_service_areas) {
            update_post_meta($user_map_id, 'not_covered_service_areas', $excluded_service_areas);
        }

        return rest_ensure_response(array('status' => 'success', 'message' => 'Service areas saved successfully'));
    } else {
        return new WP_Error('no_post_found', 'No user map post found for this user', array('status' => 404));
    }
}


// Enqueue and localize the script
function enqueue_service_areas_script() {
    // Localize the nonce and API root URL for use in JavaScript
    wp_localize_script('service-areas-js', 'wpApiSettings', array(
        'root'  => esc_url_raw(rest_url()), // REST API root URL
        'nonce' => wp_create_nonce('wp_rest') // Nonce for validation
    ));
}

add_action('wp_enqueue_scripts', 'enqueue_service_areas_script');

//Prefill selection

// Pass service areas that already exist for pre-selection
function localize_existing_service_areas() {
    $user_id = get_current_user_id();

    // Query the User Map post created by the logged-in user
    $user_map_post = get_posts(array(
        'post_type' => 'user_map',
        'author'    => $user_id,
        'posts_per_page' => 1,
    ));

    $selected_service_areas = array();
    $not_covered_service_areas = array();

    if (!empty($user_map_post)) {
        $user_map_id = $user_map_post[0]->ID;

        $selected_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'selected_service_areas', true)) ?: array();
        $not_covered_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'not_covered_service_areas', true)) ?: array();
    }

    // Localize the existing service area data for JavaScript
    wp_localize_script('service-areas-script', 'existingServiceAreaData', array(
        'selected' => $selected_service_areas,
        'not_covered' => $not_covered_service_areas,
    ));
}
add_action('wp_enqueue_scripts', 'localize_existing_service_areas');
