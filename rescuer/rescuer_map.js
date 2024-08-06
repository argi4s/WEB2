var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

var baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Initialize layers
var dataLayer = L.geoJSON(null);
var filteredData1 = L.geoJSON(null); // Requests

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

            applyFilter(); // Apply the current filters after fetching data
        })
        .catch(error => console.error('Error fetching pending requests:', error));
}

function applyFilter(filterId) {
    if (filterId) {
        var filterButton = document.getElementById(filterId);
        filterButton.classList.toggle('active');
    }

    var activeFilters = [];
    for (var i = 1; i <= 5; i++) {
        if (document.getElementById('filter' + i).classList.contains('active')) {
            activeFilters.push('filter' + i);
        }
    }

    map.eachLayer(function (layer) {
        if (layer !== baseLayer) {
            map.removeLayer(layer);
        }
    });

    baseLayer.addTo(map);

    if (activeFilters.length > 0) {
        activeFilters.forEach(function (filter) {
            switch (filter) {
                case 'filter1':
                    filteredData1.addTo(map);
                    break;
                case 'filter2':
                    filteredData2.addTo(map);
                    break;
                case 'filter3':
                    filteredData3.addTo(map);
                    break;
                case 'filter4':
                    filteredData4.addTo(map);
                    break;
                case 'filter5':
                    filteredData5.addTo(map);
                    break;
            }
        });
    } else {
        dataLayer.addTo(map);
    }
}

fetchPendingRequests(); // Fetch pending requests initially

dataLayer.addTo(map);