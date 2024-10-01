<?php
// Include necessary files
include_once get_stylesheet_directory() . '/service-areas/functions/map-functions.php';
include_once get_stylesheet_directory() . '/service-areas/functions/form-functions.php';

// Enqueue scripts and styles
function enqueue_service_areas_assets() {
    wp_enqueue_style('service-areas-style', get_stylesheet_directory_uri() . '/service-areas/css/service-areas.css');
    wp_enqueue_script('service-areas-script', get_stylesheet_directory_uri() . '/service-areas/js/service-areas.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'enqueue_service_areas_assets');

//Localize script
// Assuming you enqueue your script properly
function enqueue_service_areas_scripts() {
    // Enqueue your script
    wp_enqueue_script('my-map-script', get_stylesheet_directory_uri() . '/service-areas/js/map-script.js', array(), '1.0', true);

    // Prepare data to pass to JavaScript
    $user_map_id = 123; // Retrieve your user map post ID dynamically
    $selected_service_areas = get_post_meta($user_map_id, 'selected_service_areas', true);
    $not_covered_service_areas = get_post_meta($user_map_id, 'not_covered_service_areas', true);

    // Ensure arrays
    if (!$selected_service_areas) {
        $selected_service_areas = [];
    }

    if (!$not_covered_service_areas) {
        $not_covered_service_areas = [];
    }

    // Pass the PHP data to JavaScript
    wp_localize_script('my-map-script', 'mapData', array(
        'selectedServiceAreas' => $selected_service_areas,
        'notCoveredServiceAreas' => $not_covered_service_areas
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_service_areas_scripts');
