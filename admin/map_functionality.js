var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

var baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Initialize layers
var dataLayer = L.geoJSON(null);
var filteredData1 = L.geoJSON(null); // P Requests
var filteredData2 = L.geoJSON(null); // T Requests
var filteredData3 = L.geoJSON(null); // P Offers
var filteredData4 = L.geoJSON(null); // T Offers
var filteredData5 = L.geoJSON(null); // A Rescuers
var filteredData6 = L.geoJSON(null); // I Rescuers

var baseMarker; // Variable to hold the base marker

var requestIcon = L.icon({
    iconUrl: '../requestIcon.png', // Path to your request icon
    iconSize: [40, 40],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var offerIcon = L.icon({
    iconUrl: '../offerIcon.png', // Path to your offer icon
    iconSize: [40, 40],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var baseIcon = L.icon({
    iconUrl: '../baseIcon.png',
    iconSize: [40, 40],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var rescuerIcon = L.icon({
    iconUrl: '../rescuerIcon.png',
    iconSize: [40, 40],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

// Function for fetching and adjusting base coordinates with draggabble marker
function fetchBaseCoords() {
    fetch('warehouse_get_coordinates.php')
        .then(response => response.json())
        .then(data => {
            var latitude = data.latitude;   //fetched coordinates
            var longitude = data.longitude;

            var warehouseMarker = L.marker([latitude, longitude], { icon: baseIcon, draggable: true }).addTo(map);   // Create a draggable marker at the fetched coordinates

            warehouseMarker.bindPopup("Warehouse location: " + warehouseMarker.getLatLng().toString()).openPopup(); // Bind the initial popup content

            function updatePopup() {                                                                                // Function to update the popup with new coordinates after dragging
                warehouseMarker.getPopup().setContent("Warehouse location: " + warehouseMarker.getLatLng().toString());
                warehouseMarker.openPopup();
            }

            var initialPosition = warehouseMarker.getLatLng();                                                      // Save initial position

            warehouseMarker.on('dragend', function(e) {                                                             // Add event listener for 'dragend' event to update the popup content
                var newPosition = e.target.getLatLng();
                var confirmMove = confirm("Do you want to move the marker to the new location?");
                if (confirmMove) {
                    fetch('warehouse_update_coordinates.php', {                                                     // Send updated coordinates to the server
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'latitude=' + newPosition.lat + '&longitude=' + newPosition.lng
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === "success") {
                            updatePopup();                                                                          // Update popup with new position
                            initialPosition = newPosition;                                                          // Update initial position to the new confirmed position
                        } else {
                            alert(result.message);
                            warehouseMarker.setLatLng(initialPosition);                                             // Revert to the initial position if update fails
                            updatePopup();                                                                          // Update popup with the reverted position
                        }
                    })
                    .catch(error => {
                        console.error('Error updating coordinates:', error);
                        warehouseMarker.setLatLng(initialPosition);                                                 // Revert to the initial position on error
                        updatePopup();                                                                              // Update popup with the reverted position
                    });
                } else {
                    warehouseMarker.setLatLng(initialPosition);                                                     // Revert to the initial position
                    updatePopup();                                                                                  // Update popup with the reverted position
                }
            });
        })
        .catch(error => console.error('Error fetching coordinates:', error));
}

function fetchPendingRequests() {
    fetch('admin_map_requests.php')
        .then(response => response.json())
        .then(geoJsonData => {
            filteredData1 = L.geoJSON(geoJsonData, {
                pointToLayer: function (feature, latlng) {
                    // Create a marker with the custom icon
                    return L.marker(latlng, { icon: requestIcon });
                },
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(`Citizen: ${citizen.name} ${citizen.surname}<br>
                    Phone: ${citizen.phone}<br>
                    Location: ${citizen.latitude}, ${citizen.longitude}`);
                }
            });

            console.log('Pending requests fetched');
            applyFilter(); // Apply the current filters after fetching data
        })
        .catch(error => console.error('Error fetching pending requests:', error));
}

function fetchXX() {
    fetch('XX.php')
        .then(response => response.json())
        .then(geoJsonData => {
            filteredDataXX = L.geoJSON(geoJsonData, {
                pointToLayer: function (feature, latlng) {
                    // Create a marker with the custom icon
                    return L.marker(latlng, { icon: XXIcon });
                },
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(`<div class="tasktainer request" style="margin: 0px; min-width: 150px; padding-right:5px;">
                    <div class="text">
                        <p class="bold-text">${feature.properties.quantity} ${feature.properties.productName}</p>
                        <p class="subtext">${feature.properties.surname} ${feature.properties.name}</p>
                        <p class="subtext">${feature.properties.phone}</p>
                        <p class="subtext">${feature.properties.createdAt}</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <button class="button smallgreen" onclick="takeOnRequest(${feature.properties.requestId})">Take On</button>
                    </div>
              </div>`);
                }
            });

            console.log('XX fetched');
            applyFilter(); // Apply the current filters after fetching data
        })
        .catch(error => console.error('Error fetching pending requests:', error));
}

function applyFilter(filterId) {
    if (filterId) {
        var filterButton = document.getElementById(filterId);
        filterButton.classList.toggle('active');
        console.log(`Filter ${filterId} toggled to ${filterButton.classList.contains('active') ? 'active' : 'inactive'}`);
    }

    var activeFilters = [];
    for (var i = 1; i <= 6; i++) {
        if (document.getElementById('filter' + i).classList.contains('active')) {
            activeFilters.push('filter' + i);
        }
    }
    console.log('Active filters:', activeFilters);

    map.eachLayer(function (layer) {
        if (layer !== baseLayer && layer !== baseMarker) {
            console.log('Removing layer:', layer);
            map.removeLayer(layer);
        }
    });

    baseLayer.addTo(map);
    console.log('Base layer added to map');

    function drawLine(start, end) {
        const line = L.polyline([start, end], {
            color: '#ff0000', // Customize the line color
            weight: 2, // Customize the line thickness
            opacity: 0.4 // Set the opacity to 50%
        }).addTo(map);
    }   

    // Add filtered data layers based on active filters
    if (activeFilters.length > 0) {
        activeFilters.forEach(function (filter) {
            switch (filter) {
                case 'filter1':
                    filteredData1.addTo(map);
                    console.log('Adding filteredData1 to map');
                    break;
                case 'filter2':
                    filteredData2.addTo(map);
                    console.log('Adding filteredData2 to map');
                    break;
                case 'filter3':
                    filteredData3.addTo(map);
                    console.log('Adding filteredData3 to map');
                    break;
                case 'filter4':
                    filteredData4.addTo(map);
                    console.log('Adding filteredData4 to map');
                    break;
                case 'filter5':
                    filteredData4.addTo(map);
                    console.log('Adding filteredData4 to map');
                    break;
                case 'filter6':
                    filteredData4.addTo(map);
                    console.log('Adding filteredData4 to map');
                    break;
                default:
                    console.log('No matching filter found for', filter);
            }
        });
    } else {
        dataLayer.addTo(map);
        console.log('Adding dataLayer to map');
    }

    // Ensure the base marker is always added to the map
    if (baseMarker) {
        baseMarker.addTo(map);
        console.log('Base marker added to map in applyFilter');
    }

    if (selfMarker) {
        selfMarker.addTo(map);
        console.log('Self marker added to map in applyFilter');
    }
}

function initializeMap(){
    fetchBaseCoords();
    // Call the function to fetch self position when the map is initialized
    fetchPendingRequests(); // Fetch pending requests initially
    // fetchTakenRequests();
    // fetchPendingOffers(); // Fetch pending offers initially
    // fetchTakenOffers();
    // fetchRescuers(); // Fetch other rescuers
    // fetchInactiveRescuers();
    dataLayer.addTo(map);
}

initializeMap();