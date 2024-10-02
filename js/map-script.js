function loadPolygonData(serviceAreaId, map, bounds, color) {
    console.log("Loading polygon data for service area:", serviceAreaId);
    const serviceArea = serviceAreaData.selected_service_areas.find(area => area.id == serviceAreaId) ||
        serviceAreaData.not_covered_service_areas.find(area => area.id == serviceAreaId);

    if (serviceArea && serviceArea.coordinates) {
        const polygonCoords = JSON.parse(serviceArea.coordinates);

        const userPolygon = new google.maps.Polygon({
            paths: polygonCoords,
            strokeColor: color,
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: color,
            fillOpacity: 0.35,
        });

        userPolygon.setMap(map);
        polygonCoords.forEach(coord => bounds.extend(new google.maps.LatLng(coord.lat, coord.lng)));
        map.fitBounds(bounds);
    } else {
        console.error('No coordinates found for service area:', serviceAreaId);
    }
}

function initUserMap() {
    const map = new google.maps.Map(document.getElementById('user-map'), {
        zoom: 5,
        center: { lat: 39.8283, lng: -98.5795 } // Center of the USA
    });

    const bounds = new google.maps.LatLngBounds();

    // Load selected service areas
    if (serviceAreaData.selected_service_areas.length) {
        serviceAreaData.selected_service_areas.forEach(serviceArea => {
            loadPolygonData(serviceArea.id, map, bounds, '#0000FF');
        });
    }

    // Load not covered service areas
    if (serviceAreaData.not_covered_service_areas.length) {
        serviceAreaData.not_covered_service_areas.forEach(serviceArea => {
            loadPolygonData(serviceArea.id, map, bounds, '#FF0000');
        });
    }
}

document.addEventListener('DOMContentLoaded', initUserMap);
