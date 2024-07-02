<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaflet Map with Markers</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <style>
        #map { height: 600px; } /* Adjust map height as needed */
    </style>
</head>
<body>
    <div id="map"></div>

    <script>
        var map = L.map('map').setView([1.5601943360704045, 103.63579413015414], 15); // Initial map center and zoom

        // Add tile layer to map (example using OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // PHP script output for markers (replace with actual PHP-generated JavaScript)
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "csv_db 10";

        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $database);

        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        // Query the database for latitude and longitude data
        $sql = "SELECT latitud, longitud FROM trees";
        $result = mysqli_query($conn, $sql);

        // Loop through the query results and create a marker for each location
        while ($row = mysqli_fetch_assoc($result)) {
            echo "L.marker([" . $row['latitud'] . ", " . $row['longitud'] . "]).addTo(map);\n";
        }

        // Close the database connection
        mysqli_close($conn);
        ?>
    </script>
</body>
</html>
