<?php
defined('C5_EXECUTE') or die('Access Denied.');
$uniq = uniqid();
$lat  = strlen($values['latitude']) > 0 ? $values['latitude'] : "44.9603951";
$lng  = strlen($values['longitude']) > 0 ? $values['longitude'] : "-93.226847";
?>
<style type="text/css">
    #map<?= $uniq; ?> {
        width: 100%;
        height: 200px;
    }
    #address {
        width: 100%;
        z-index: 1000;
    }
</style>
<input type="hidden" 
       name="<?= $this->field('latitude');?>"
       value="<?= $lat;?>"
       id="latitude<?= $uniq; ?>">
<input type="hidden" 
       name="<?= $this->field('longitude');?>"
       value="<?= $lat;?>"
       id="longitude<?= $uniq; ?>">
<div>
    <input type="text" 
           name="<?= $this->field('address');?>"
           value="<?= $values['address'];?>"
           id="address">
</div>
<div id="map<?= $uniq; ?>"></div>
<script type="text/javascript">

    var map;
    ccm_addHeaderItem("https://maps.googleapis.com/maps/api/js?key=<?= Config::get('app.api_keys.google.maps');?>&libraries=places&callback=initMap", "JAVASCRIPT");
        function initMap() {
            mapOptions = {
                center: {lat: <?= $lat; ?>, lng: <?= $lng; ?>},
                zoom: 18,
                disableDefaultUI: true,
                zoomControl: true,
                mapTypeControl: true,
                scrollwheel: false
            };
            map = new google.maps.Map(document.getElementById("map<?= $uniq; ?>"), mapOptions);
    <?php if (!isset($values["address"])) { ?>
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    initialLocation = new google.maps.LatLng(
                            position.coords.latitude,
                            position.coords.longitude
                            );
                    map.setCenter(initialLocation);
                    map.setZoom(18);
                    marker = new google.maps.Marker({
                        map: map,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        position: initialLocation
                    });
                    google.maps.event.addListener(marker, "dragend", function () {
                        updatePosition(marker.getPosition());
                    });
                    updatePosition(marker.getPosition());
                });
            } else {
                // hopefully never fall back to this but have to put the marker somewhere
                marker = new google.maps.Marker({
                    map: map,
                    draggable: true,
                    animation: google.maps.Animation.DROP,
                    position: {lat: <?= $lat; ?>, lng: <?= $lng; ?>}
                });
                google.maps.event.addListener(marker, "dragend", function () {
                    updatePosition(marker.getPosition());
                });
                updatePosition(marker.getPosition());
            }
    <?php } else { ?>
            marker = new google.maps.Marker({
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
                position: {lat: <?= $lat; ?>, lng: <?= $lng; ?>}
            });
            google.maps.event.addListener(marker, "dragend", function () {
                updatePosition(marker.getPosition());
            });
            updatePosition(marker.getPosition());
    <?php } ?>
            var input = document.getElementById("address");
            var autocomplete = new google.maps.places.Autocomplete(input);

            google.maps.event.addListener(autocomplete, "place_changed", function () {
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    alert("The place you entered could not be found.");
                    return;
                } else {
                    updatePosition(place.geometry.location);
                }
            });

            function updatePosition(pos) {
                $("#latitude<?= $uniq; ?>").val(pos.lat());
                $("#longitude<?= $uniq; ?>").val(pos.lng());
                map.setCenter(pos);
                marker.setPosition(pos);
                geocoder = new google.maps.Geocoder();
                geocoder.geocode(
                        {
                            latLng: pos
                        },
                        function (results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                $("#address<?= $uniq; ?>").val(results[0].formatted_address);
                            } else {
                                console.log(status);
                            }
                        }
                );
            }
        }
</script>
