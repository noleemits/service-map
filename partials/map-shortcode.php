<div id="user-map" style="height: 400px;"></div>
<script>
    console.log("Is this working?");

    function loadPolygonData(serviceAreaId, map, bounds, color) {

        fetch(`/wp-json/wp/v2/service_areas/${serviceAreaId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Service Area Data:', data); // Log the full data object to debug
                if (data.polygon_coordinates) {
                    // Continue processing polygons
                } else {
                    console.log('No polygon data found for service area:', serviceAreaId);
                }
                if (data.polygon_coordinates) {
                    const cleanedPolygon = data.polygon_coordinates.replace(/\\r\\n/g, '').replace(/\s+/g, '').replace(/\\/g, '').replace(/\'/g, '"');
                    const polygonCoords = JSON.parse(cleanedPolygon);

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

                }

            })
            .catch(error => console.error('Error fetching polygon data:', error));
        const polygonCoords = JSON.parse(cleanedPolygon);

    }