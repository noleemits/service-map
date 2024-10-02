<?php

// Enqueue map script and localize data
function enqueue_map_script() {
    // Enqueue the Google Maps script and your custom map script
    wp_enqueue_script('map-script', get_stylesheet_directory_uri() . '/service-areas/js/map-script.js', array(), null, true);

    $user_id = get_current_user_id();

    // Query the User Map post created by the logged-in user
    $user_map_post = get_posts(array(
        'post_type'      => 'user_map',
        'author'         => $user_id,
        'posts_per_page' => 1,
    ));

    // Initialize empty arrays for service area data
    $selected_service_area_data = array();
    $not_covered_service_area_data = array();

    // If the user has a map, fetch the data
    if (!empty($user_map_post)) {
        $user_map_id = $user_map_post[0]->ID;

        // Fetch the selected and not-covered service areas
        $selected_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'selected_service_areas', true));
        $not_covered_service_areas = maybe_unserialize(get_post_meta($user_map_id, 'not_covered_service_areas', true));

        // Fetch coordinates for selected service areas
        if (!empty($selected_service_areas) && is_array($selected_service_areas)) {
            foreach ($selected_service_areas as $service_area_id) {
                $coordinates = get_post_meta($service_area_id, 'polygon_coordinates', true);
                if (!empty($coordinates)) {
                    $selected_service_area_data[] = array(
                        'id' => $service_area_id,
                        'coordinates' => $coordinates
                    );
                }
            }
        }

        // Fetch coordinates for not-covered service areas
        if (!empty($not_covered_service_areas) && is_array($not_covered_service_areas)) {
            foreach ($not_covered_service_areas as $service_area_id) {
                $coordinates = get_post_meta($service_area_id, 'polygon_coordinates', true);
                if (!empty($coordinates)) {
                    $not_covered_service_area_data[] = array(
                        'id' => $service_area_id,
                        'coordinates' => $coordinates
                    );
                }
            }
        }
    }

    // Ensure arrays are passed even if they are empty to avoid errors
    wp_localize_script('map-script', 'serviceAreaData', array(
        'selected_service_areas'    => $selected_service_area_data,
        'not_covered_service_areas' => $not_covered_service_area_data,
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_map_script');

// Enqueue styles and other scripts if necessary
function enqueue_service_areas_assets() {
    wp_enqueue_style('service-areas-style', get_stylesheet_directory_uri() . '/service-areas/css/service-areas.css');
    wp_enqueue_script('service-areas-script', get_stylesheet_directory_uri() . '/service-areas/js/service-areas.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'enqueue_service_areas_assets');

//Localize all service areas data (optional, depends on use case)
function localize_service_areas_script() {
    $service_areas = get_posts(array(
        'post_type'   => 'service_areas',
        'numberposts' => -1
    ));

    $service_area_data = array();
    foreach ($service_areas as $area) {
        $service_area_data[] = array(
            'id'          => $area->ID,
            'title'       => $area->post_title,
            'coordinates' => get_post_meta($area->ID, 'polygon_coordinates', true)
        );
    }

    // Localize this data to the map script
    wp_localize_script('map-script', 'allServiceAreaData', $service_area_data);
}
add_action('wp_enqueue_scripts', 'localize_service_areas_script');

// Shortcode for the user map
function display_user_map_shortcode() {
    ob_start();
    include get_stylesheet_directory() . '/service-areas/partials/map-shortcode.php';
    return ob_get_clean();
}
add_shortcode('user_map', 'display_user_map_shortcode');


function register_service_areas_post_type() {
    register_post_type('service_areas', array(
        'label' => 'Service Areas',
        'public' => true,
        'show_in_rest' => true, // This enables the REST API for this post type
        'supports' => array('title', 'editor'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'service-areas'),
    ));
}
add_action('init', 'register_service_areas_post_type');

//Limit user to only one map
function limit_user_to_one_user_map($post_id, $post, $update) {
    // Ensure this only applies to 'user_map' post type
    if ($post->post_type !== 'user_map') {
        return;
    }

    // Get current user ID
    $user_id = get_current_user_id();

    // Check if the user is an admin (excludes admins from the restriction)
    if (current_user_can('administrator')) {
        return; // Admins can create multiple user maps, so return early
    }

    // Query for existing user map posts by this user
    $existing_user_map = new WP_Query(array(
        'post_type' => 'user_map',
        'author'    => $user_id,
        'post_status' => array('publish', 'pending', 'draft', 'private'),
        'posts_per_page' => 1,
        'post__not_in' => array($post_id), // Exclude current post if updating
    ));

    // If a user already has a user_map post, prevent creation of another
    if ($existing_user_map->have_posts()) {
        // Trash the current post if it's a new one
        if (!$update) {
            wp_trash_post($post_id);
        }

        // Show an error message and stop execution
        wp_die('You are only allowed to have one User Map post.');
    }
}
add_action('save_post', 'limit_user_to_one_user_map', 10, 3);


//Create map if it doesn't exist

function ensure_user_has_map() {
    // Get the current user ID
    $user_id = get_current_user_id();

    // Check if the user is logged in
    if (!$user_id) {
        return;
    }

    // Query for existing user map posts by this user
    $existing_user_map = new WP_Query(array(
        'post_type' => 'user_map',
        'author'    => $user_id,
        'post_status' => array('publish', 'pending', 'draft', 'private'),
        'posts_per_page' => 1,
    ));

    // If user doesn't have a map, create one
    if (!$existing_user_map->have_posts()) {
        $user_info = get_userdata($user_id);
        $user_name = $user_info->user_login;
        $user_email = $user_info->user_email;

        // Create new map post and set status to "publish"
        $post_data = array(
            'post_title'   => $user_name . ' - ' . $user_email,
            'post_type'    => 'user_map',
            'post_status'  => 'publish', // Changed to publish
            'post_author'  => $user_id,
        );

        $new_post_id = wp_insert_post($post_data);

        if ($new_post_id) {
            // Optionally, add meta data or other content to the newly created post
            // update_post_meta($new_post_id, 'meta_key', 'meta_value');
        }
    }
}
add_action('wp', 'ensure_user_has_map');
