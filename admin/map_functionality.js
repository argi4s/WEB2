//Map Initialization

var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

//Icons from my images folder
var redIcon = L.icon({
    iconUrl: 'images/testicon.png',
    iconSize: [40, 40]
});

var greenIcon = L.icon({
    iconUrl: 'images/greenIcon.png',
    iconSize: [40,40]
});

var carIcon = L.icon({
    iconUrl: 'images/carOffDuty.png',
    iconSize: [40,40]
});

// Function for fetching and adjusting base coordinates with draggabble marker
fetch('warehouse_get_coordinates.php')
    .then(response => response.json())
    .then(data => {
        var latitude = data.latitude;   //fetched coordinates
        var longitude = data.longitude;

        var warehouseMarker = L.marker([latitude, longitude], { icon: redIcon, draggable: true }).addTo(map);   // Create a draggable marker at the fetched coordinates

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

// Function for fetching rescuer coordinates and adding them as non-draggable markers
fetch('get_coordinates.php')
    .then(response => response.json())
    .then(data => {
        // Add rescuers markers
        data.rescuers.forEach(function(rescuer) {
            L.marker([rescuer.latitude, rescuer.longitude], { icon: carIcon }).addTo(map)
                .bindPopup("Rescuer location: " + [rescuer.latitude, rescuer.longitude].toString());
        });

        // Add citizens markers
        data.citizens.forEach(function(citizen) {
            L.marker([citizen.latitude, citizen.longitude], { icon: greenIcon }).addTo(map)
                .bindPopup("Citizen location: " + [citizen.latitude, citizen.longitude].toString());
        });
    })
    .catch(error => console.error('Error fetching coordinates:', error));