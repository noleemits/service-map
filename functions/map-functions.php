<?php

// Shortcode for the user map
function display_user_map_shortcode() {
    ob_start();
    include get_stylesheet_directory() . '/service-areas/partials/map-shortcode.php';
    return ob_get_clean();
}
add_shortcode('user_map', 'display_user_map_shortcode');

// Register the REST field for exposing polygon data
function register_service_areas_meta_single() {
    register_rest_field(
        'service_areas',
        'polygon_coordinates',
        array(
            'get_callback'    => 'get_polygon_coordinates',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}
add_action('rest_api_init', 'register_service_areas_meta_single');

// Fetch polygon coordinates
function get_polygon_coordinates($object) {
    $polygon_coordinates = get_post_meta($object['id'], 'polygon_coordinates', true);
    return !empty($polygon_coordinates) ? $polygon_coordinates : null;
}
