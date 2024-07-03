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
        var map = L.map('map').setView([1.4632, 103.6347], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map); 

//PROXIMITY
        // Function to find trees within a given radius (in kilometers) from a point
        function findTreesWithinRadius(trees, lat, lng, radiusKm) {
            var radiusMeters = radiusKm * 1000; // Convert km to meters
            var point = L.latLng(lat, lng);
            var nearbyTrees = [];

            trees.forEach(function (tree) {
                var treeLatLng = L.latLng(tree.latitude, tree.longitude);
                var distance = L.GeometryUtil.distance(map, point, treeLatLng);

                if (distance <= radiusMeters) {
                    nearbyTrees.push({
                        "name": tree.name,
                        "distance": distance,
                        "latlng": treeLatLng
                    });
                }
            });

            return nearbyTrees;
        }

        // Function to get the radius in kilometers based on the map's current bounds
        function getRadiusFromBounds(map) {
            var bounds = map.getBounds();
            var center = bounds.getCenter();
            var northEast = bounds.getNorthEast();
            var distance = map.distance(center, northEast);
            return distance / 1000; // Convert meters to kilometers
        }

        // Fetch tree data from the server
        function fetchAndDisplayTrees() {
            var center = map.getCenter();
            var radiusKm = getRadiusFromBounds(map);

            fetch(`fetch_trees_by_proximity.php?lat=${center.lat}&lng=${center.lng}&radius=${radiusKm}`)
                .then(response => response.json())
                .then(trees => {
                    console.log("Nearby trees:", trees);

                    // Clear existing markers
                    treeLayer.clearLayers();

                    // Highlight nearby trees on the map
                    trees.forEach(function (tree) {
                        var latlng = L.latLng(tree.latitude, tree.longitude);
                        L.marker(latlng).addTo(treeLayer)
                            .bindPopup(tree.name + "<br>Distance: " + Math.round(tree.distance * 1000) + " meters");
                    });

                    // Optionally add a marker for the search point
                    L.marker(center).addTo(treeLayer)
                        .bindPopup("Search Point<br>Lat: " + center.lat + "<br>Lng: " + center.lng);
                })
                .catch(error => console.error('Error fetching tree data:', error));
        }

        // Create a layer group to hold the tree markers
        var treeLayer = L.layerGroup().addTo(map);

        // Fetch and display trees initially
        fetchAndDisplayTrees();

        // Update tree markers on zoom or drag end
        map.on('zoomend', fetchAndDisplayTrees);
        map.on('dragend', fetchAndDisplayTrees);

    </script>
</body>
</html>
