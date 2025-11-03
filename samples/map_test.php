<div class="map-box">

    <img class="weather-map" src="https://tile.openweathermap.org/map/<?php
    echo $map_type . '/' . $tile_url . '.png?appid=' . API_KEY;
    ?>" alt="Weather Map">

    <img class="regular-map" src="https://b.tile.openstreetmap.org/<?php
    echo $tile_url;
    ?>.png" alt="Regular Map">

</div>