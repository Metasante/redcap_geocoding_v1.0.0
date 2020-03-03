// Initialize map
function initMap() {
  // Create a GeoAdmin Map
  var map = new ga.Map({

    // Define the div where the map is placed
    target: 'map',

    // Create a view
    view: new ol.View({

      // Define the default resolution
      // 10 means that one pixel is 10m width and height
      // List of resolution of the WMTS layers:
      // 650, 500, 250, 100, 50, 20, 10, 5, 2.5, 2, 1, 0.5, 0.25, 0.1
      resolution: 650,

      // Define a coordinate CH1903+ (EPSG:2056) for the center of the view
      center: [2660000, 1190000]
    })
  });

  // Create a background layer
  var lyr1 = ga.layer.create('ch.swisstopo.pixelkarte-farbe');

  // Add the background layer in the map
  map.addLayer(lyr1);

  // Create an overlay layer
  var lyr2 = ga.layer.create('ch.swisstopo.fixpunkte-agnes');

  // Add the overlay layer in the map
  map.addLayer(lyr2);
};

    function obtain_adresse_inc() { // RECUPERE L'ADRESSE TAPEE PAR L'UTILISATEUR
      input = (document.getElementById("nb").value) + " " + (document.getElementById("st").value) + " " + (document.getElementById("np").value) + " " + (document.getElementById("cm").value);
    };




// Send data to DB
    function sendData() {

      location.href = "https://metasante.ch/index.php/questionnaires/";

    };
