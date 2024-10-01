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
