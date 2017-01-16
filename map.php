<?php

require_once('config.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Map Explorer</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.2/dist/leaflet.css" />
  <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="css/style.css">
  <script src="js/jquery-3.1.1.min.js"></script>
  <script src="https://unpkg.com/leaflet@1.0.2/dist/leaflet.js"></script>

</head>
<body>
  <div id="page-container">
    <h1>Find local pharmacies</h1>
    <p>Put your postcode in the box below to find nearby pharmacies offering NHS Health Checks.</p>
    <input type="text" class="input-text" id="postcode" placeholder="Postcode">
    <div id="postcode-error">Invalid Postcode</div>
    <div id="mapid"></div>
  </div>

  <script type="text/javascript">
  var mymap = L.map('mapid').setView([50.69908305, -1.29838969], 13);

  L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=<?=MAPBOX_ACCESS_TOKEN?>', {
    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
    maxZoom: 18,
    id: '<?=MAPBOX_PROJECT_KEY?>',
    accessToken: '<?=MAPBOX_ACCESS_TOKEN?>'
  }).addTo(mymap);

  $('#postcode').on('input', function(evt) {
    $(this).val(function (_, val) {
      return val.toUpperCase();
    });
  });

  //setup before functions
  var typingTimer;                //timer identifier
  var doneTypingInterval = 1000;  //time in ms, 5 second for example
  var $input = $('#postcode');

  //on keyup, start the countdown
  $input.on('keyup', function () {
    $("#postcode").css('background', '').css('border-color', '');
    $("#postcode-error").css('height', '0px');
    clearTimeout(typingTimer);
    typingTimer = setTimeout(doneTyping, doneTypingInterval);
  });

  //on keydown, clear the countdown
  $input.on('keydown', function () {
    clearTimeout(typingTimer);
  });

  function setError()
  {
    $("#postcode").css('background', '#F8A6AC').css('border-color', '#7D171E');
    $("#postcode-error").css('height', '23px');
  }

  markers = [];

  //user is "finished typing," do something
  function doneTyping () {
    postcode = $("#postcode").val();
    if (postcode == '') { return }
    parts = postcode.match(/^([A-Z]{1,2}\d{1,2}[A-Z]?)\s*(\d[A-Z]{2})$/);
    if (parts === null)
    {
      setError();
      return;
    }
    parts.shift();
    postcode = parts.join(' ');
    regex = /^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW]) ?[0-9][ABD-HJLNP-UW-Z]{2})$/i;
    if(!regex.test(postcode))
    {
      setError();
    }
    else
    {
      $("#postcode").css('background', '#eee').css('border-color', '');
      $("#postcode-error").css('height', '0px');

      $.ajax({
        url: "servlet.php",
        data: {
          postcode: postcode
        }
      })
      .done(function( data ) {
        $("#postcode").css('background', '#96D28D');
        for(i = 0; i < markers.length; i++)
        {
          mymap.removeLayer(markers[i]);
        }
        markers = [];
        $.each(data, function(index, value) {
          var markerLocation = new L.LatLng(value.latitude, value.longitude);
          marker = L.marker([value.latitude, value.longitude]).addTo(mymap);
          marker.bindPopup("<b>"+value.display_name+"</b><br>"+value.address+"<br>Telephone: "+value.telephone);
          if(index == 0)
            marker.openPopup();
          markers.push(marker);
        });
      });
    }
  }

  </script>
</body>
</html>
