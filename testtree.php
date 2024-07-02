<!DOCTYPE html>
<html>
<head>
    <title>GROUP 9 PROXIMITY</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
    <h1>Searching query based on proximity</h1>
    <div id="map" style="height: 600px;"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([1.4854, 103.7613], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 50
        }).addTo(map);
        L.control.zoom({
            position: 'topright'
        }).addTo(map);
        L.control.scale({
            position: 'bottomleft'
        }).addTo(map);

        <?php
            $servername = 'localhost'; 
            $username = 'root';
            $password = '';
            $database = 'project';

            // Create connection
            $conn = mysqli_connect($servername, $username, $password, $database);

            // Check connection
            if ($conn->connect_error) {
                die('Connection failed: ' . $conn->connect_error);
            }

            // Query the database for latitude and longitude data
            $sql = "SELECT * FROM treedata";
            $result = $conn->query($sql);

            // Fetch data and generate JavaScript for each marker
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "console.log('Adding marker at: [" . $row['Latitud'] . ", " . $row['Longitud'] . "]');\n";
                    echo "var marker = L.marker([" . $row['Latitud'] . ", " . $row['Longitud'] . "]).addTo(map);\n";
                }
            } else {
                echo "console.log('No data found');\n";
            }

            // Close the database connection
            $conn->close();
        ?>
		
//PROXIMITY HERE

    </script>
</body>
</html>
