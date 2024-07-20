var map = L.map('map').setView([37.9838, 23.7275], 13); // Centered on Athens, Greece

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

var redIcon = L.icon({
    iconUrl: 'images/testicon.png',
    iconSize: [40, 40]
});

// Fetch coordinates from the PHP script
fetch('getWarehouseCoordinates.php')
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

        // Add event listener for 'dragend' event to update the popup content
        warehouseMarker.on('dragend', updatePopup);
    })
    .catch(error => console.error('Error fetching coordinates:', error));
