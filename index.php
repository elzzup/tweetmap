<?php

define('ENVIRONMENT_DEV', 1);
define('ENVIRONMENT_PRO', 2);
if (file_exists('./env.php')) {
    define('ENVIRONMENT', ENVIRONMENT_PRO);
} else {
    define('ENVIRONMENT', ENVIRONMENT_DEV);
}

require_once('./vendor/autoload.php');
require_once('./MyStatus.php');
require_once('./keys.php');
require_once('./GetTweets.php');
require_once('./GetUserTweet.php');
require_once('./FireworksModel.php');
require_once('./LoadTweets.php');
require_once('./Funcs.php');

$type = @$_GET['type'];
$user_tweets = get_second_tweets_db(1000, $type);

// 藤沢市
$lat = "35.3266269";
$long = "139.4829343";
//// 足立区
//$lat = "35.7780774";
//$long = "139.7972246";

//list($tweets, $tweets_4s) = get_tweets($lat, $long);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
    </style>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=<?= GOOGLE_API_KEY ?>&sensor=TRUE">
</script>
<script type="text/javascript">
function initialize() {
    var mapOptions = {
    center: new google.maps.LatLng(<?= $lat ?>, <?= $long ?>),
        zoom: 10,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
    var infowindow = new google.maps.InfoWindow();
    var geocoder = new google.maps.Geocoder();
<?php 
$locs = array();
foreach ($user_tweets as $user_id => $statuses) {
    $user_params = array();
    $user_params[] = $user_id;
    foreach ($statuses as $st) {
        $tweets_params = array();
        $tweets_params[] = $st->geo->coordinates[0];
        $tweets_params[] = $st->geo->coordinates[1];
        $tweets_params[] = $st->time;
        $tweets_params[] = '"' . escape_js_string($st->text) . '"';
        $user_params[] = '[' . implode(',', $tweets_params) . ']';
    }
    $locs[] = '[' . implode(',', $user_params) . ']';
}
$locations_text = '[' . implode(',', $locs) . ']';

//foreach ($tweets as $i => $st) { 
//    if (!isset($st->geo)) continue;
//    $locs[] = '["' . escape_js_string($st->text) . '", ' . $st->geo->coordinates[0] . ', ' . $st->geo->coordinates[1] . ']';
//}

?>
var user_locs = <?= $locations_text?>;
console.log(user_locs);
for (var k = 0; k < user_locs.length; k++) {
    var user_id = user_locs[k][0];
    var pre_x = -1, pre_y = -1;
    for (var j = 1; j < user_locs[k].length; j++) {
        var x = user_locs[k][j][0];
        var y = user_locs[k][j][1];
        var time = user_locs[k][j][2];
        var col = time_to_color(time);
        var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + col,
            new google.maps.Size(21, 34),
            new google.maps.Point(0,0),
            new google.maps.Point(10, 34));
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(x, y),
            icon: pinImage,
            map: map
        });
        if (pre_x != -1) {
            var points = [
                new google.maps.LatLng(pre_x, pre_y),
                new google.maps.LatLng(x, y)
            ];
            var flightPath = new google.maps.Polyline({
                path: points,
                geodesic: true,
                strokeColor: "#" + col,
                strokeOpacity: 1.0,
                strokeWeight: 5
            });
            flightPath.setMap(map);
        }
        pre_x = x;
        pre_y = y;
        google.maps.event.addListener(marker, 'click', (function(marker, user_lock, k, j, time) {
            return function() {
                console.log(k + ":" + j);
                infowindow.setContent(user_locs[k][j][3] + "[" + time + "]");
                infowindow.open(map, marker);
            }
        })(marker, user_locs, k, j, time));
    }
}
}
function time_to_color(time) {
    /*
    1800 ~ 1900 メイン
     */
    if (time < 1800) {
        var hex = ("0" + Math.floor(255 - (time / 1800) * 255).toString(16)).slice(-2);
        return "FF" + hex + "" + hex;
    } else if (time >= 1900) {
        var hex = ("0" + Math.floor(255 - ((1900 - time) / 400) * 255).toString(16)).slice(-2);
        return hex + "0000";
    }
    return "FF0000";
}

</script>
  </head>
  <body onload="initialize()">
    <div>
      <p>薄 → 18:00 赤 19:00 → 濃</p>
    </div>
    <div id="map_canvas" style="width:100%; height:100%"></div>
  </body>
</html>
