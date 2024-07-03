<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integration G9 G10</title>
    <style>
        body { 
		font-family: times new roman, sans-serif; 
		}
        .container { 
		width: 90%; 
		margin: 0 auto; 
		}
        h1, h2 { 
		text-align: center; 
		}
        ul { 
		list-style-type: none; 
		padding: 0; 
		}
        li { 
		margin: 10px;
		}
        #map { 
		height: 600px; 
		} 
    </style>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>
<body>
    <div class="container">
        <h1>Search Query By Attributes and Proximity</h1>
        
        <!-- Search form -->
        <form action="integrationG910.php" method="GET">
            <label for="query">Tree Name:</label>
			<input type="text" name="query" placeholder="Enter NmPkk" required>
            <label for="radius">Radius (km):</label>
			<input type="number" name="radius" placeholder="Enter radius (km)" required>
            <button type="submit">Search</button>
        <hr>

        <?php
        // Check if the query parameter is set
        if (isset($_GET['query']) && isset($_GET['radius'])) {
            // Get the search query and radius from the URL parameters
            $searchQuery = $_GET['query'];
            $radius = floatval($_GET['radius']);
            
            // Default latitude and longitude for the center of the search area
            $latitude = 1.4624; // Replace with a default center latitude
            $longitude = 103.6447; // Replace with a default center longitude
            
            // Database connection parameters
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "project"; 
            
            // Create connection
            $conn = mysqli_connect($servername, $username, $password, $database);
            
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            // Prepare SQL query to fetch latitude, longitude, NmPkk, and site within the radius
            $sql = "SELECT latitud, longitud, NmPkk, site, 
                           (6371 * acos(cos(radians(?)) * cos(radians(latitud)) * cos(radians(longitud) - radians(?)) + sin(radians(?)) * sin(radians(latitud)))) AS distance
                    FROM treedata
                    HAVING distance < ?
                    AND NmPkk = ?";
            
            // Bind parameters
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dddds", $latitude, $longitude, $latitude, $radius, $searchQuery);
            
            // Execute query
            $stmt->execute();
            
            // Get result set
            $result = $stmt->get_result();
            
            // Display search results
            if ($result->num_rows > 0) {
                echo "<h2>Search Results for '$searchQuery' within $radius km</h2>";
                echo "<ul>";
                $markers = [];
                while ($row = $result->fetch_assoc()) {
                    
                    $markers[] = $row;
                }
                echo "</ul>";
                
                // Display Leaflet map
                echo '<div id="map"></div>';
                
                // Prepare JavaScript data
                $markersJson = json_encode($markers);
                
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var map = L.map('map').setView([$latitude, $longitude], 15); // Initial map center and zoom
                        
                        // Add tile layer to map (example using OpenStreetMap)
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
                        }).addTo(map);
                        
                        // Add markers to the map
                        var markers = $markersJson;
                        markers.forEach(function(marker) {
                            var popupContent = '<table><tr><th colspan=\"2\">NmPkk: ' + marker.NmPkk + '</th></tr>' +
                                               '<tr><td>Latitude:</td><td>' + marker.latitud + '</td></tr>' +
                                               '<tr><td>Longitude:</td><td>' + marker.longitud + '</td></tr>' +
                                               '<tr><td>Site:</td><td>' + marker.site + '</td></tr></table>';
                            var markerInstance = L.marker([marker.latitud, marker.longitud]).addTo(map);
                            markerInstance.bindPopup(popupContent);
                            markerInstance.bindTooltip(marker.NmPkk, {permanent: true, direction: 'right'});
                        });
                    });
                </script>";
            } else {
                echo "<p>No results found for '$searchQuery' within $radius km</p>";
            }
            
            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
        ?>
        
        <!-- Leaflet Map Script -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    </div>
</body>
</html>
