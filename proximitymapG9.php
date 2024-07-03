<!DOCTYPE html>
<html>
<head>
    <title>GROUP 9 PROXIMITY</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        #map { height: 600px; }
        #search { margin: 10px; }
    </style>
</head>
<body>
    <h1>Searching query based on proximity</h1>
    <div id="search">
        <label for="location">Location (lat,long):</label>
        <input type="text" id="location" placeholder="Enter location">
        <label for="radius">Radius (km):</label>
        <input type="number" id="radius" placeholder="Enter radius in km">
        <button onclick="searchByProximity()">Search</button>
    </div>
    <div id="map"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-geometryutil"></script>
    <script>
        var map = L.map('map').setView([1.4632, 103.6347], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        var treeLayer = L.layerGroup().addTo(map);
        var trees = [];

        // PHP code to fetch data from the database and generate JavaScript
        <?php
            $servername = 'localhost'; 
            $username = 'root';
            $password = '';
            $database = 'project';

            // Create connection
            $conn = new mysqli($servername, $username, $password, $database);

            // Check connection
            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            // Query the database for latitude and longitude data
            $sql = "SELECT * FROM treedata";
            $result = $conn->query($sql);

            // Generate JavaScript to add markers to the map
            if ($result->num_rows > 0) {
                echo "trees = [];\n";
                while ($row = $result->fetch_assoc()) {
                    echo "trees.push({ name: '{$row['NmPkk']}', latitude: {$row['Latitud']}, longitude: {$row['Longitud']} });\n";
                }
            } else {
                echo "console.log('No data found');\n";
            }

            // Close the database connection
            $conn->close();
        ?>

        function findTreesWithinRadius(trees, centerLat, centerLng, radiusKm) {
            var nearbyTrees = [];

            trees.forEach(function(tree) {
                var treeLatLng = L.latLng(tree.latitude, tree.longitude);
                var centerLatLng = L.latLng(centerLat, centerLng);
                var distance = centerLatLng.distanceTo(treeLatLng) / 1000; // convert to kilometers

                if (distance <= radiusKm) {
                    nearbyTrees.push({
                        name: tree.name,
                        distance: distance,
                        latlng: treeLatLng
                    });
                }
            });

            return nearbyTrees;
        }

        function fetchAndDisplayTrees(center, radiusKm) {
            var nearbyTrees = findTreesWithinRadius(trees, center.lat, center.lng, radiusKm);
            console.log("Nearby trees:", nearbyTrees);

            // Clear existing markers
            treeLayer.clearLayers();

            // Highlight nearby trees on the map
            nearbyTrees.forEach(function(tree) {
                var latlng = tree.latlng;
                L.marker(latlng).addTo(treeLayer)
                    .bindPopup(tree.name + "<br>Distance: " + Math.round(tree.distance * 1000) + " meters");
            });

            // Optionally add a marker for the search point
            L.marker(center).addTo(treeLayer)
                .bindPopup("Search Point<br>Lat: " + center.lat + "<br>Lng: " + center.lng);
        }

        function searchByProximity() {
            var location = document.getElementById("location").value;
            var radius = document.getElementById("radius").value;

		//Read data from OSM to generate the location to json file > jumpa the location from database in array 
            if (location && radius) {
                // Geocode the location using a simple API (like Nominatim)
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${location}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            var lat = parseFloat(data[0].lat);
                            var lon = parseFloat(data[0].lon);
                            var radiusKm = parseFloat(radius);

                            var center = L.latLng(lat, lon);
                            map.setView(center, 13);
                            fetchAndDisplayTrees(center, radiusKm);
                        } else {
                            alert("Location not found.");
                        }
                    });
            } else {
                alert("Please enter both location and radius.");
            }
        }
    </script>
</body>
</html>
