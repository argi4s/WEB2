<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="./style.css">
    <title>Rescuer Map</title>
</head>

<body>
    <div class="container" style="display: flex; justify-content: start;">
        <div class="container" style="width: 300px; margin: 1%;">
        
            <h1>My Tasks</h1>

            <div class="scroll-container" style="height: 50%;">
                <div class="tasktainer offer">
                    <div class="text">
                        <p class="bold-text">4 τούβλα κοκόρι</p>
                        <p class="subtext">Ηλίας Αργυράκης - 6946592549</p>
                        <p class="subtext">7/13/2024 6:34pm</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallred">Cancel</a>
                        <a class="button smallgreen">Finish</a>
                    </div>
                </div>
                <div class="tasktainer request">
                    <div class="text">
                        <p class="bold-text">4 τούβλα κοκόρι</p>
                        <p class="subtext">Ηλίας Αργυράκης - 6946592549</p>
                        <p class="subtext">7/13/2024 6:34pm</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallred">Cancel</a>
                        <a class="button smallgreen">Finish</a>
                    </div>
                </div>
                <div class="tasktainer request">
                    <div class="text">
                        <p class="bold-text">4 τούβλα κοκόρι</p>
                        <p class="subtext">Ηλίας Αργυράκης - 6946592549</p>
                        <p class="subtext">7/13/2024 6:34pm</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallred">Cancel</a>
                        <a class="button smallgreen">Finish</a>
                    </div>
                </div>
                <div class="tasktainer"></div>
            </div>
            
            <div class="container" style="height: auto; width: 100%; display: grid; justify-content: end; margin-top: 40px">
                <button class="filter-btn active" id="filter1" onclick="applyFilter('filter1')">Rescuers</button>
                <button class="filter-btn active" id="filter2" onclick="applyFilter('filter2')">Requests</button>
                <button class="filter-btn active" id="filter3" onclick="applyFilter('filter3')">Offers</button>
                <button class="filter-btn active" id="filter4" onclick="applyFilter('filter4')">My Tasks</button>
            </div>

            <a href="rescuer_main_page.html" class="button back" style="margin-top: 30px;">Back to Main Page</a>
 
        </div>
        <div class="map-container">
            <div id="map"></div>
        </div>
        <div class="container" style="width: 300px; margin: 1%">

            <h1>Available Requests</h1>

            <div class="scroll-container">
                <?php include 'fetch_requests.php'; ?>
            </div>

            <h1>Available Offers</h1>

            <div class="scroll-container">
                <div class="tasktainer offer">
                    <div class="text">
                        <p class="bold-text">4 τούβλα κοκόρι</p>
                        <p class="subtext">Ηλίας Αργυράκης - 6946592549</p>
                        <p class="subtext">7/13/2024 6:34pm</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallgreen">Take On</a>
                    </div>
                </div>
                <div class="tasktainer offer">
                    <div class="text">
                        <p class="bold-text">4 τούβλα κοκόρι</p>
                        <p class="subtext">Ηλίας Αργυράκης - 6946592549</p>
                        <p class="subtext">7/13/2024 6:34pm</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallgreen">Take On</a>
                    </div>
                </div>
                <div class="tasktainer offer">
                    <div class="text">
                        <p class="bold-text">4 τούβλα κοκόρι</p>
                        <p class="subtext">Ηλίας Αργυράκης - 6946592549</p>
                        <p class="subtext">7/13/2024 6:34pm</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallgreen">Take On</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="rescuer_map.js"></script>
</body>
</html>
