var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

var redIcon = L.icon({ // red marker image
    iconUrl: 'images/testicon.png',
    iconSize: [40, 40]
});

var carOffDutyIcon = L.icon({ // car off duty image
    iconUrl: 'images/carOffDuty.png',
    iconSize: [40, 40]
});

var carOnDutyIcon = L.icon({ // car on duty image
    iconUrl: 'images/carOnDuty.jfif',
    iconSize: [40, 40]
});

var testmarker = L.marker([37.9338, 23.7275], { icon: redIcon, draggable: true }).addTo(map);

function updatePopup() {
    testmarker.getPopup().setContent("This is my test marker, θα μπορούσε στο μέλλον να είναι η βάση, δες εκφώνηση. Εκτυπώνω και τη τοποθεσία: " + testmarker.getLatLng().toString());
    testmarker.openPopup();
}

// Bind the initial popup content
testmarker.bindPopup("This is my test marker, θα μπορούσε στο μέλλον να είναι η βάση, δες εκφώνηση. Εκτυπώνω και τη τοποθεσία: " + testmarker.getLatLng().toString()).openPopup();

testmarker.on('dragend', updatePopup); // Add event listener for 'dragend' event to update the popup content

console.log(testmarker.toGeoJSON()); // Log the marker's GeoJSON representation

var markers = { // Marker categories
    rescuersOnDuty: [ // rescuers on duty
        L.marker([37.9838, 23.7275], { icon: carOnDutyIcon }),
        L.marker([37.9898, 23.7275], { icon: carOnDutyIcon })
    ],
    rescuersOffDuty: [ // rescuers off duty
        L.marker([37.9838, 23.7375], { icon: carOffDutyIcon }),
        L.marker([37.9898, 23.7375], { icon: carOffDutyIcon })
    ],
    pendingRequests: [ // pending requests
        L.marker([37.9038, 23.7475])
    ],
    completedRequests: [ // completed requests
        L.marker([37.9898, 23.7475])
    ],
    offers: [ // offers from citizens
        L.marker([37.9118, 23.7355])
    ]
};

var markerLayers = {}; // Add markers to the map and store their layer groups
for (var category in markers) {
    markerLayers[category] = L.layerGroup(markers[category]).addTo(map);
}

// Function to toggle markers
function toggleMarkers(category, show) {
    if (show) {
        map.addLayer(markerLayers[category]);
    } else {
        map.removeLayer(markerLayers[category]);
    }
}

// Add event listeners to checkboxes
document.querySelectorAll('.filter-checkbox').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        toggleMarkers(this.id, this.checked);
    });
});
