// Assuming 'selected_service_areas' and 'not_covered_service_areas' are being passed by wp_localize_script


function initUserMap() {

    const map = new google.maps.Map(document.getElementById('user-map'), {
        zoom: 5,
        center: { lat: 39.8283, lng: -98.5795 } // Centered on the USA
    });
    const bounds = new google.maps.LatLngBounds();


    // Assuming the data is passed via wp_localize_script as 'serviceAreaData'
    serviceAreaData.selected_service_areas.forEach(serviceAreaId => {
        loadPolygonData(serviceAreaId, map, bounds, '#0000FF'); // Blue for covered areas
    });

    serviceAreaData.not_covered_service_areas.forEach(serviceAreaId => {
        loadPolygonData(serviceAreaId, map, bounds, '#FF0000'); // Red for not covered areas
    });
}
