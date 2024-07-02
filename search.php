<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <style>
        /* CSS for styling results */
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: 0 auto; }
        h1, h2 { text-align: center; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 10px; }
        #map { height: 600px; } /* Adjust map height as needed */
    </style>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
</head>
<body>
    <div class="container">
        <h1>Search trees</h1>
        
        <!-- Search form -->
        <form action="search.php" method="GET">
            <input type="text" name="query" placeholder="Enter your search query">
            <button type="submit">Search</button>
        </form>
        
        <hr>
        
        <?php
        // Check if the query parameter is set
        if (isset($_GET['query'])) {
            // Get the search query from the URL parameter
            $searchQuery = $_GET['query'];
            
            // Database connection parameters
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "csv_db 10"; 
            
            // Create connection
            $conn = mysqli_connect($servername, $username, $password, $database);
            
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            // Prepare SQL query to fetch latitude, longitude, NmPkk, and site for the given TID
            $sql = "SELECT latitud, longitud, NmPkk, site FROM trees WHERE tid = ?";
            
            // Bind parameters
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $searchQuery);
            
            // Execute query
            $stmt->execute();
            
            // Get result set
            $result = $stmt->get_result();
            
            // Display search results
            if ($result->num_rows > 0) {
                echo "<h2>Search Results for '$searchQuery'</h2>";
                echo "<ul>";
                $markers = [];
                while ($row = $result->fetch_assoc()) {
                    echo "<li>Latitude: {$row['latitud']}, Longitude: {$row['longitud']}, NmPkk: {$row['NmPkk']}, Site: {$row['site']}</li>";
                    $markers[] = $row;
                }
                echo "</ul>";
                
                // Display Leaflet map
                echo '<div id="map"></div>';
                
                // Prepare JavaScript data
                $markersJson = json_encode($markers);
                
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var map = L.map('map').setView([1.4624, 103.6447], 15); // Initial map center and zoom
                        
                        // Add tile layer to map (example using OpenStreetMap)
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors'
                        }).addTo(map);
                        
                        // Add markers to the map
                        var markers = $markersJson;
                        markers.forEach(function(marker) {
                            var popupContent = '<table><tr><th colspan=\"2\">Tree ID: $searchQuery</th></tr>' +
                                               '<tr><td>NmPkk:</td><td>' + marker.NmPkk + '</td></tr>' +
                                               '<tr><td>Site:</td><td>' + marker.site + '</td></tr></table>';
                            var markerInstance = L.marker([marker.latitud, marker.longitud]).addTo(map);
                            markerInstance.bindPopup(popupContent);
                            markerInstance.bindTooltip(marker.NmPkk, {permanent: true, direction: 'right'});
                        });
                    });
                </script>";
            } else {
                echo "<p>No results found for '$searchQuery'</p>";
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
