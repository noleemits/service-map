<div id="user-map" style="height: 400px;"></div>
<script>
    function loadPolygonData(serviceAreaId, map, bounds, color) {
        fetch(`/wp-json/wp/v2/service_areas/${serviceAreaId}`)
            .then(response => response.json())
            .then(data => {
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
    }

    function initUserMap() {
        const map = new google.maps.Map(document.getElementById('user-map'), {
            zoom: 5,
            center: {
                lat: 39.8283,
                lng: -98.5795
            }
        });
        const bounds = new google.maps.LatLngBounds();

        $selected_service_areas = get_post_meta($user_map_id, 'selected_service_areas', true);
        if (!$selected_service_areas) {
            $selected_service_areas = []; // Initialize as an empty array if null
        }

        $not_covered_service_areas = get_post_meta($user_map_id, 'not_covered_service_areas', true);
        if (!$not_covered_service_areas) {
            $not_covered_service_areas = []; // Initialize as an empty array if null
        }

    }

    document.addEventListener('DOMContentLoaded', initUserMap);
</script>