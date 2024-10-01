// function loadPolygonData(serviceAreaId, map, bounds, color) {
//     console.log("This should print but it not printing");
//     const serviceArea = serviceAreaData.find(area => area.id == serviceAreaId);

//     console.log('Service Area Data:', serviceAreaData);


//     if (serviceArea && serviceArea.coordinates) {
//         const polygonCoords = JSON.parse(serviceArea.coordinates);

//         const userPolygon = new google.maps.Polygon({
//             paths: polygonCoords,
//             strokeColor: color,
//             strokeOpacity: 0.8,
//             strokeWeight: 2,
//             fillColor: color,
//             fillOpacity: 0.35,
//         });

//         userPolygon.setMap(map);
//         polygonCoords.forEach(coord => bounds.extend(new google.maps.LatLng(coord.lat, coord.lng)));
//         map.fitBounds(bounds);
//     } else {
//         console.error('No coordinates found for service area:', serviceAreaId);
//     }
// }


// console.log(serviceAreaData); // Check if localized data is passed correctly

// function initUserMap() {
//     const map = new google.maps.Map(document.getElementById('user-map'), {
//         zoom: 5,
//         center: { lat: 39.8283, lng: -98.5795 } // Centered on the USA
//     });

//     const bounds = new google.maps.LatLngBounds();

//     if (serviceAreaData.selected_service_areas.length) {
//         serviceAreaData.selected_service_areas.forEach(serviceAreaId => {
//             loadPolygonData(serviceAreaId, map, bounds, '#0000FF');
//         });
//     }

//     if (serviceAreaData.not_covered_service_areas.length) {
//         serviceAreaData.not_covered_service_areas.forEach(serviceAreaId => {
//             loadPolygonData(serviceAreaId, map, bounds, '#FF0000');
//         });
//     }
// }

// document.addEventListener('DOMContentLoaded', initUserMap);
