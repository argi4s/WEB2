var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

var dataLayer = L.geoJSON(your_geojson_data).addTo(map);
var filteredData1 = L.geoJSON(filtered_data_for_filter1);
var filteredData2 = L.geoJSON(filtered_data_for_filter2);
var filteredData3 = L.geoJSON(filtered_data_for_filter3);
var filteredData4 = L.geoJSON(filtered_data_for_filter4);

function applyFilter(filterId) {
    var filterButton = document.getElementById(filterId);
    
    // Toggle active class for button
    filterButton.classList.toggle('active');

    // Determine active filters
    var activeFilters = [];
    for (var i = 1; i <= 4; i++) {
        if (document.getElementById('filter' + i).classList.contains('active')) {
            activeFilters.push('filter' + i);
        }
    }

    // Clear previous layers
    map.eachLayer(function(layer) {
        if (layer !== baseLayer) {
            map.removeLayer(layer);
        }
    });

    // Add base layer back to map
    baseLayer.addTo(map);

    // Apply filters based on active buttons
    if (activeFilters.length > 0) {
        activeFilters.forEach(function(filter) {
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
            }
        });
    } else {
        // No filters are active, show all data
        dataLayer.addTo(map);
    }
}

var baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);