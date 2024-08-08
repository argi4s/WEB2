var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

var baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Initialize layers
var dataLayer = L.geoJSON(null);
var filteredData1 = L.geoJSON(null); // Requests
var filteredData2 = L.geoJSON(null); // Offers

var baseMarker; // Variable to hold the base marker

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

var combinedIcon = L.icon({
    iconUrl: '../combinedIcon.png', // Path to your combined icon
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

function fetchPendingRequests() {
    fetch('map_requests.php')
        .then(response => response.json())
        .then(geoJsonData => {
            filteredData1 = L.geoJSON(geoJsonData, {
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

function applyFilter(filterId) {
    if (filterId) {
        var filterButton = document.getElementById(filterId);
        filterButton.classList.toggle('active');
        console.log(`Filter ${filterId} toggled to ${filterButton.classList.contains('active') ? 'active' : 'inactive'}`);
    }

    var activeFilters = [];
    for (var i = 1; i <= 5; i++) {
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
}


function initializeMap(){
    fetchBaseCoords();
    fetchPendingRequests(); // Fetch pending requests initially
    fetchPendingOffers(); // Fetch pending offers initially
    dataLayer.addTo(map);
}

initializeMap();