<?php

// Enqueue map script and localize data
function enqueue_map_script() {
    // Enqueue the Google Maps script and your custom map script
    wp_enqueue_script('map-script', get_stylesheet_directory_uri() . '/service-areas/js/map-script.js', array(), null, true);

    $user_id = get_current_user_id();

    // Query the User Map post created by the logged-in user
    $user_map_post = get_posts(array(
        'post_type' => 'user_map',
        'author'    => $user_id,
        'posts_per_page' => 1,
    ));

    // Assuming you are still using hardcoded post ID for testing
    $user_map_post_id = 4227;

    // Fetch the selected and not-covered service area IDs
    $selected_service_areas = get_post_meta($user_map_post_id, 'selected_service_areas', true);
    $not_covered_service_areas = get_post_meta($user_map_post_id, 'not_covered_service_areas', true);

    // Check if there are selected service areas
    if (!empty($selected_service_areas) && is_array($selected_service_areas)) {
        echo '<h3>Selected Service Areas:</h3>';
        echo '<ul>';
        foreach ($selected_service_areas as $area_id) {
            $area_title = get_the_title($area_id); // Assuming these are post IDs
            if ($area_title) {
                echo '<li>' . esc_html($area_title) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo 'No selected service areas found.';
    }

    // Check if there are not-covered service areas
    if (!empty($not_covered_service_areas) && is_array($not_covered_service_areas)) {
        echo '<h3>Not Covered Service Areas:</h3>';
        echo '<ul>';
        foreach ($not_covered_service_areas as $area_id) {
            $area_title = get_the_title($area_id); // Assuming these are post IDs
            if ($area_title) {
                echo '<li>' . esc_html($area_title) . '</li>';
            }
        }
        echo '</ul>';
    } else {
        echo 'No not-covered service areas found.';
    }


    if ($user_map_post) {
        $user_map_id = $user_map_post[0]->ID;

        // Get the selected service areas and not covered areas
        $selected_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'selected_service_areas', true));
        $not_covered_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'not_covered_service_areas', true));



        // Ensure the arrays are properly initialized
        $selected_service_areas = $selected_service_areas ?: array();
        $not_covered_service_areas = $not_covered_service_areas ?: array();

        // Localize script and pass the data
        wp_localize_script('map-script', 'serviceAreaData', array(
            'selected_service_areas'    => $selected_service_areas,
            'not_covered_service_areas' => $not_covered_service_areas,
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_map_script');


// Enqueue styles and other scripts if necessary
function enqueue_service_areas_assets() {
    wp_enqueue_style('service-areas-style', get_stylesheet_directory_uri() . '/service-areas/css/service-areas.css');
    wp_enqueue_script('service-areas-script', get_stylesheet_directory_uri() . '/service-areas/js/service-areas.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'enqueue_service_areas_assets');

//Localize

function localize_service_areas_script() {
    // Retrieve service areas as array
    $service_areas = get_posts(array(
        'post_type' => 'service_areas',
        'numberposts' => -1
    ));

    $service_area_data = array();
    foreach ($service_areas as $area) {
        $service_area_data[] = array(
            'id' => $area->ID,
            'title' => $area->post_title,
            'coordinates' => get_post_meta($area->ID, 'polygon_coordinates', true)
        );
    }

    // Localize this data to the map script
    wp_localize_script('map-script-handle', 'allServiceAreaData', $service_area_data);
}

add_action('wp_enqueue_scripts', 'localize_service_areas_script');

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
