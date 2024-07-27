<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="./rescuer/style.css">
</head>
<body>
    <div class="container" style="height: auto; width: auto;">
        <div class="form-container" style="width: 900px;">
            <h2>Give some info about yourself</h2>
            <form id="registration-form" method="post" action="register.php">
                <div class="grid_parent">
                    <div class="grid_child">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" autocomplete="given-name" required>
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname:</label>
                            <input type="text" id="surname" name="surname" autocomplete="family-name" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="tel" id="phone" name="phone" autocomplete="tel" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" autocomplete="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="grid_child">
                        <label for="location">Please select your location:</label>
                        <div id="map" class="minimap-container"></div>
                        <input type="hidden" id="latitude" name="latitude" required>
                        <input type="hidden" id="longitude" name="longitude" required>
                    </div>
                </div>
                <div class="form-group" style="display: flex; justify-content: center; gap: 10px;">
                    <button type="submit" class="button green">Register</button>
                </div>
                <div class="form-group" style="display: flex; justify-content: center; gap: 10px;">
                    <span>Already have an account? Log in <a href="login.html" class="text-button">here!</a></span>
                </div>
            </form>
            <div id="message">
                <p id="error-message" style="color: red;">
                <?php
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'username_exists') {
                            echo "Username already exists. Please choose a different username.";
                        } elseif ($_GET['error'] == 'location_required') {
                            echo "Location must be selected on the map.";
                        }
                    }
                    ?>
                </p>
                <p id="success-message" style="color: green;">
                    <?php
                    if (isset($_GET['success']) && $_GET['success'] == 'registered') {
                        echo "Registration successful! You can now <a href='login.html'>log in</a>.";
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize the map and set its view to a default location
        var map = L.map('map').setView([37.9838, 23.7275], 13);

        // Load and display the tile layer on the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        // Placeholder for the marker and coordinates
        var marker;
        var latInput = document.getElementById('latitude');
        var lonInput = document.getElementById('longitude');

        // Add an event listener for map clicks
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lon = e.latlng.lng;

            // Update the marker position
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }

            // Update the hidden input fields
            latInput.value = lat;
            lonInput.value = lon;
        });
    </script>
</body>
</html>

