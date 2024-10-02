<?php

// Shortcode for the service area form
function display_service_area_form_shortcode() {
    ob_start();
    include get_stylesheet_directory() . '/service-areas/partials/form-shortcode.php';
    return ob_get_clean();
}
add_shortcode('service_area_form', 'display_service_area_form_shortcode');


// Handle form submission and update the post meta for service areas
function save_service_areas(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $included_service_areas = $request->get_param('included_service_areas');
    $excluded_service_areas = $request->get_param('excluded_service_areas');

    // Get or create the user map post
    $user_map_post = get_posts(array('post_type' => 'user_map', 'author' => $user_id, 'posts_per_page' => 1));

    if ($user_map_post) {
        $user_map_id = $user_map_post[0]->ID;
    } else {
        $user_map_id = wp_insert_post(array('post_type' => 'user_map', 'post_title' => 'User Map for User ' . $user_id, 'post_author' => $user_id, 'post_status' => 'publish'));
    }

    // Update the user map post meta
    update_post_meta($user_map_id, 'selected_service_areas', $included_service_areas);
    update_post_meta($user_map_id, 'not_covered_service_areas', $excluded_service_areas);

    return new WP_REST_Response(array('status' => 'success', 'message' => 'Service areas saved successfully'), 200);
}
