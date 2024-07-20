var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

var redIcon = L.icon({
    iconUrl: 'images/testicon.png',
    iconSize: [40, 40]
});

fetch('warehouse_get_coordinates.php')
    .then(response => response.json())
    .then(data => {
        var latitude = data.latitude;
        var longitude = data.longitude;

        // Create a draggable marker at the fetched coordinates
        var warehouseMarker = L.marker([latitude, longitude], { icon: redIcon, draggable: true }).addTo(map);

        // Bind the initial popup content
        warehouseMarker.bindPopup("Warehouse location: " + warehouseMarker.getLatLng().toString()).openPopup();

        // Function to update the popup with new coordinates after dragging
        function updatePopup() {
            warehouseMarker.getPopup().setContent("Warehouse location: " + warehouseMarker.getLatLng().toString());
            warehouseMarker.openPopup();
        }

        // Save initial position
        var initialPosition = warehouseMarker.getLatLng();

        // Add event listener for 'dragend' event to update the popup content
        warehouseMarker.on('dragend', function(e) {
            var newPosition = e.target.getLatLng();
            var confirmMove = confirm("Do you want to move the marker to the new location?");
            if (confirmMove) {
                // Send updated coordinates to the server
                fetch('warehouse_update_coordinates.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'latitude=' + newPosition.lat + '&longitude=' + newPosition.lng
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === "success") {
                        updatePopup(); // Update popup with new position
                        initialPosition = newPosition; // Update initial position to the new confirmed position
                    } else {
                        alert(result.message);
                        warehouseMarker.setLatLng(initialPosition); // Revert to the initial position if update fails
                        updatePopup(); // Update popup with the reverted position
                    }
                })
                .catch(error => {
                    console.error('Error updating coordinates:', error);
                    warehouseMarker.setLatLng(initialPosition); // Revert to the initial position on error
                    updatePopup(); // Update popup with the reverted position
                });
            } else {
                warehouseMarker.setLatLng(initialPosition); // Revert to the initial position
                updatePopup(); // Update popup with the reverted position
            }
        });
    })
    .catch(error => console.error('Error fetching coordinates:', error));
