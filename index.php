<?php

require('myFunctions.php');
require('getWeather.php');

$city_raw = $_GET['city'] ?? 'Riga';

$weatherData = get_weatherData($city_raw);
$current = json_decode($weatherData->weather_json);
$forecast = json_decode($weatherData->forecast_json);

$city_error;
if ($current->cod == 404) {
    $city_error = "City $city_raw was not found.";

    $city_raw = 'Riga';

    $weatherData = get_weatherData($city_raw);
    $current = json_decode($weatherData->weather_json);
    $forecast = json_decode($weatherData->forecast_json);
}
else if ($current->cod != 200) {
    $city_error = 'Unknown error ' . $weatherData->weather_json;
}

$city = $current->name;

$current_weather = $current->weather[0];
$current_main = $current->main;
$current_wind = $current->wind;

$current_coord = $current->coord;

//Time
$timezone = $current->timezone;
$current_dt = convert_date($current->dt + $timezone);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather B</title>
    <link rel="stylesheet" href="styles/index.css">
</head>

<body class="roboto">
    <div class="general-block" style="display: flex; justify-content: space-between; align-items: center;">
        <h1>
            Weather Forecast
        </h1>
        <span style="font-size: xx-large;">
            <?php echo "$city " . get_county_flag($current->sys->country) ?>
        </span>
    </div>

    <? if ($city_error): ?>

    <div class="general-block"
        style="display: flex; justify-content: space-between; align-items: center; background: #C21;">
        <span style="font-size: large;">
            Error
        </span>
        <span>
            <? echo htmlspecialchars($city_error) ?>
        </span>
    </div>

    <? endif ?>

    <form id="search-panel">
        <input type="text" placeholder="Search..." name="city" value="<?php echo $city_raw; ?>" id="search-bar">
        <button type="submit" id="search-button">Submit</button>
    </form>

    <div class="main-weather">
        <div class="main-weather-block">
            <p>
                <?php echo $current_dt['week'] . ', ' . $current_dt['date'] ?>
                <span class="shadow-text">
                    <?php echo $current_dt['time'] ?>
                </span>
            </p>

            <p class="shadow-text">
                <?php echo $current_coord->lon ?>&deg;N,
                <?php echo $current_coord->lat ?>&deg;E
            </p>

            <img src="<?php echo WEATHER_ICON_URL . $current_weather->icon; ?>.png" alt="Weather icon">

            <p>
                <?php echo $current_weather->main ?><br>
                <i class="shadow-text">
                    <?php echo $current_weather->description ?>
                </i>
            </p>
            <p>
                <?php echo temp_format($current_main->temp) ?><br>
                <i class="shadow-text"> feels like
                    <?php echo temp_format($current_main->feels_like) ?>
                </i>
            </p>
        </div>

        <div class="main-weather-block" style="display:grid">

            <p>
                <i class="shadow-text">Pressure</i><br>
                <?php echo $current_main->pressure ?> mb
            </p>
            <p>
                <i class="shadow-text">Humidity</i><br>
                <?php echo $current_main->humidity ?>%
            </p>
            <p>
                <i class="shadow-text">Visibility</i><br>
                <?php echo round($current->visibility / 1000) ?> km
            </p>

            <div>
                <p style="display: inline-block;">
                    <i class="shadow-text">Wind</i><br>
                    <?php echo $current_wind->speed . ' km/h' ?>
                </p>

                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-mouse-pointer2-icon lucide-mouse-pointer-2"
                    style="transform: rotate(<?php echo $current_wind->deg - 180 + 45 ?>deg)">
                    <path
                        d="M4.037 4.688a.495.495 0 0 1 .651-.651l16 6.5a.5.5 0 0 1-.063.947l-6.124 1.58a2 2 0 0 0-1.438 1.435l-1.579 6.126a.5.5 0 0 1-.947.063z" />
                </svg>
            </div>

        </div>
    </div>

    <div class="forecast-scroller">

        <?php foreach ($forecast->list as $w): ?>

        <div class="forecast-block">
            <p>
                <?
                    $dt = convert_date($w->dt + $timezone);
                    echo $dt['week'] . '<br>' . $dt['date'];
                ?>
            </p>

            <img src="<?php echo WEATHER_ICON_URL . $w->weather[0]->icon ?>.png" alt="Weather icon">

            <p>
                <? echo $dt['time']; ?><br>
            </p>
            <p>
                <? echo round($w->main->temp) . '&deg;C'; ?>
            </p>
        </div>

        <?php endforeach ?>

    </div>

</body>

</html>