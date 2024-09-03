var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

var baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Initialize layers
var dataLayer = L.geoJSON(null);
var filteredData1 = L.geoJSON(null); // Requests
var filteredData2 = L.geoJSON(null); // Offers
var filteredData3 = L.geoJSON(null); // My tasks
var filteredData4 = L.geoJSON(null); // Rescuers

var baseMarker; // Variable to hold the base marker
var selfMarker; // Global variable to store self marker

// Different marker icons
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

var myRequestIcon = L.icon({
    iconUrl: '../myRequestIcon.png', // Path to your request icon
    iconSize: [60, 60],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var myOfferIcon = L.icon({
    iconUrl: '../myOfferIcon.png', // Path to your offer icon
    iconSize: [60, 60],
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

var selfIcon = L.icon({
    iconUrl: '../selfIcon.png',
    iconSize: [60, 60],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

function fetchBaseCoords() {
    console.log('Fetching base coordinates...');
    fetch('get_base_coords.php')
    .then(response => {
        if (!response.ok) {
            console.error('Network response was not ok');
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Data received:', data);
        if (data.base && data.base.length > 0) {
            let base = data.base[0];
            console.log('Adding marker at:', base.latitude, base.longitude);

            // Remove the existing base marker if it exists
            if (baseMarker) {
                map.removeLayer(baseMarker);
            }

            // Create the base marker
            baseMarker = L.marker([base.latitude, base.longitude], { icon: baseIcon }).addTo(map)
                .bindPopup("Base Location: " + [base.latitude, base.longitude].toString());

            // Set the map view to the marker location
            map.setView([base.latitude, base.longitude], 13);

            console.log('Base marker added to the map');
        } else {
            console.error('No base data found');
        }
    })
    .catch(error => console.error('Error fetching coordinates:', error));
}

function fetchSelfPosition() {
    fetch('fetch_self_position.php')
        .then(response => response.json())
        .then(data => {
            const { latitude, longitude } = data;

            // Add a draggable marker for self
            selfMarker = L.marker([latitude, longitude], { icon: selfIcon, draggable: true }).addTo(map);

            // Listen for dragend event to update self position
            selfMarker.on('dragend', function (event) {
                const newPosition = event.target.getLatLng();
                updateSelfPosition(newPosition.lat, newPosition.lng);
            });
        })
        .catch(error => console.error('Error fetching self position:', error));
}

function updateSelfPosition(lat, lng) {
    fetch('update_self_position.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ latitude: lat, longitude: lng })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload the page to reflect the changes
            alert('Self position updated successfully.');
        } else {
            alert('Failed to update self position: ' + data.message);
        }
    })
    .catch(error => console.error('Error updating self position:', error));
}

function fetchPendingRequests() {
    fetch('map_requests.php')
        .then(response => response.json())
        .then(geoJsonData => {
            filteredData1 = L.geoJSON(geoJsonData, {
                pointToLayer: function (feature, latlng) {
                    // Create a marker with the custom icon
                    return L.marker(latlng, { icon: requestIcon });
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

            console.log('Pending requests fetched');
            applyFilter(); // Apply the current filters after fetching data
        })
        .catch(error => console.error('Error fetching pending requests:', error));
}

function fetchPendingOffers() {
    fetch('map_offers.php')
        .then(response => response.json())
        .then(geoJsonData => {
            filteredData2 = L.geoJSON(geoJsonData, {
                pointToLayer: function (feature, latlng) {
                    // Create a marker with the custom icon
                    return L.marker(latlng, { icon: offerIcon });
                },
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(`<div class="tasktainer offer" style="margin: 0px; min-width: 150px; padding-right:5px;">
                    <div class="text">
                        <p class="bold-text">${feature.properties.quantity} ${feature.properties.productName}</p>
                        <p class="subtext">${feature.properties.surname} ${feature.properties.name}</p>
                        <p class="subtext">${feature.properties.phone}</p>
                        <p class="subtext">${feature.properties.createdAt}</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <button class="button smallgreen" onclick="takeOnOffer(${feature.properties.offerId})">Take On</button>
                    </div>
              </div>`);
                }
            });

            console.log('Pending offers fetched');
            applyFilter(); // Apply the current filters after fetching data
        })
        .catch(error => console.error('Error fetching pending offers:', error));
}

function fetchMyTasks() {
    fetch('map_tasks.php')
        .then(response => response.json())
        .then(geoJsonData => {
            filteredData3 = L.geoJSON(geoJsonData, {
                pointToLayer: function (feature, latlng) {
                    let icon;

                    // Choose icon based on taskType
                    if (feature.properties.taskType === 'offer') {
                        icon = myOfferIcon;
                    } else if (feature.properties.taskType === 'request') {
                        icon = myRequestIcon;
                    }

                    return L.marker(latlng, { icon: icon });
                },
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(`<div class="tasktainer ${feature.properties.taskType}" style="margin: 0px; min-width: 150px;">
                    <div class="text">
                        <p class="bold-text">${feature.properties.quantity} ${feature.properties.productName}</p>
                        <p class="subtext">${feature.properties.surname} ${feature.properties.name}</p>
                        <p class="subtext">${feature.properties.phone}</p>
                        <p class="subtext">${feature.properties.createdAt}</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallred" onclick="cancelTask(${feature.properties.id})" style="color: white;">Cancel</a>
                        <a class="button smallgreen" onclick="finishTask(${feature.properties.id})" style="color: white;">Finish</a>
                    </div>
              </div>`);
                }
            });

            console.log('Active tasks fetched');
            applyFilter(); // Apply the current filters after fetching data
        })
        .catch(error => console.error('Error fetching active tasks:', error));
}

function fetchRescuers() {
    fetch('map_rescuers.php')
        .then(response => response.json())
        .then(geoJsonData => {
            filteredData4 = L.geoJSON(geoJsonData, {
                pointToLayer: function (feature, latlng) {
                    // Create a marker with the custom icon
                    return L.marker(latlng, { icon: rescuerIcon });
                },
                onEachFeature: function (feature, layer) {
                    layer.bindPopup(`<div class="tasktainer">
                        <div class="text" style="text-align: center; padding: 16px;">
                            <p>Vehicle's name: ${feature.properties.username}</p>
                        </div>
                    </div>`);
                }
            });

            console.log('Rescuer vehicles fetched');
            applyFilter(); // Apply the current filters after fetching data
        })
        .catch(error => console.error('Error fetching rescuer vehicles:', error));
}

function applyFilter(filterId) {
    if (filterId) {
        var filterButton = document.getElementById(filterId);
        filterButton.classList.toggle('active');
        console.log(`Filter ${filterId} toggled to ${filterButton.classList.contains('active') ? 'active' : 'inactive'}`);
    }

    var activeFilters = [];
    for (var i = 1; i <= 4; i++) {
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
            weight: 5, // Customize the line thickness
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
                    filteredData3.eachLayer(function (layer) {
                        drawLine(selfMarker.getLatLng(), layer.getLatLng());
                    });
                    break;
                case 'filter4':
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
    fetchSelfPosition();
    fetchPendingRequests(); // Fetch pending requests initially
    fetchPendingOffers(); // Fetch pending offers initially
    fetchMyTasks(); // Fetch my tasks
    fetchRescuers(); // Fetch other rescuers
    dataLayer.addTo(map);
}

initializeMap();