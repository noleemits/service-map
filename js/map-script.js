function loadPolygonData(serviceAreaId, map, bounds, color) {
    const serviceArea = serviceAreaData.selected_service_areas.find(area => area.id == serviceAreaId) ||
        serviceAreaData.not_covered_service_areas.find(area => area.id == serviceAreaId);

    if (serviceArea && serviceArea.coordinates) {
        const rawCoords = serviceArea.coordinates;

        // Split the string into an array of coordinate pairs
        const coordPairs = rawCoords.split(",").map(coord => coord.trim());

        // Check if we have pairs of coordinates
        if (coordPairs.length % 2 !== 0) {
            console.error("Error: Odd number of coordinate values. Coordinates should be in lat,lng pairs.");
            return;
        }

        // Convert each pair into { lat, lng } object
        const polygonCoords = [];
        for (let i = 0; i < coordPairs.length; i += 2) {
            const lng = parseFloat(coordPairs[i]);
            const lat = parseFloat(coordPairs[i + 1]);
            polygonCoords.push({ lat: lat, lng: lng });
        }


        if (polygonCoords.length === 0) {
            console.error("No valid polygon coordinates found.");
            return;
        }

        const userPolygon = new google.maps.Polygon({
            paths: polygonCoords,
            strokeColor: color,
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: color,
            fillOpacity: 0.35,
        });

        userPolygon.setMap(map);

        polygonCoords.forEach(coord => {
            bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
        });

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
