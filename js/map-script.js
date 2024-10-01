document.addEventListener('DOMContentLoaded', function () {
    const map = new google.maps.Map(document.getElementById('user-map'), {
        zoom: 5,
        center: { lat: 39.8283, lng: -98.5795 } // Centered on the USA
    });
    const bounds = new google.maps.LatLngBounds();

    // Selected service areas (from PHP)
    if (mapData.selectedServiceAreas && Array.isArray(mapData.selectedServiceAreas)) {
        mapData.selectedServiceAreas.forEach(function (serviceAreaId) {
            if (serviceAreaId > 0) {
                loadPolygonData(serviceAreaId, map, bounds, '#0000FF'); // Blue for covered areas
            }
        });
    }

    // Not covered service areas (from PHP)
    if (mapData.notCoveredServiceAreas && Array.isArray(mapData.notCoveredServiceAreas)) {
        mapData.notCoveredServiceAreas.forEach(function (serviceAreaId) {
            if (serviceAreaId > 0) {
                loadPolygonData(serviceAreaId, map, bounds, '#FF0000'); // Red for not covered areas
            }
        });
    }
});
