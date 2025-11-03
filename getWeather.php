<?php

define('API_KEY', getenv('OPENWEATHER_API_KEY'));
define('WEATHER_ICON_URL', 'https://openweathermap.org/img/wn/');
define('WEATHER_URL_BASE', 'https://api.openweathermap.org/data/2.5/');
define('API_METRIC', '&units=metric');

class WeatherData
{
    public $city;
    public $weather_json;
    public $forecast_json;

    public function __construct(
        string $city,
        string $weather_json,
        string $forecast_json
    ) {
        $this->city = $city;
        $this->weather_json = $weather_json;
        $this->forecast_json = $forecast_json;
    }
}

function my_api_call(string $url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function get_weatherData_api(string $city): WeatherData
{
    $city_url = urlencode("$city");

    $w_url = WEATHER_URL_BASE . "weather?q=$city_url&appid=" . API_KEY . API_METRIC;
    $w_res = my_api_call($w_url);

    $f_url = WEATHER_URL_BASE . "forecast?q=$city_url&appid=" . API_KEY . API_METRIC;
    $f_res = my_api_call($f_url);

    return new WeatherData($city, $w_res, $f_res);
}

function get_weatherData_cached(string $city)
{
    $conn = new mysqli('localhost', 'root', null, 'weather');

    if ($conn->connect_error) {
        return;
    }

    $cached_db = $conn->query("SELECT * FROM cache WHERE 
    city = '$city' AND timestamp > DATE_SUB(NOW(), INTERVAL '1' HOUR)");

    $conn->close();

    if ($cached_db->num_rows == 0) {
        return;
    }

    $cached = $cached_db->fetch_array();

    return new WeatherData(
        $cached['city'],
        $cached['weather'],
        $cached['forecast']
    );
}

function cache_weatherData(WeatherData $weatherData)
{
    $conn = new mysqli('localhost', 'root', null, 'weather');

    if ($conn->connect_error) {
        return;
    }

    extract(get_object_vars($weatherData));

    $conn->query("DELETE FROM cache WHERE city = '$city'");
    $conn->query("INSERT INTO cache VALUES 
    ('$city', NOW(), '$weather_json', '$forecast_json')");
}

function get_weatherData(string $city)
{
    $weatherData = get_weatherData_cached($city) ?? get_weatherData_api($city);

    cache_weatherData($weatherData);

    return $weatherData;
}

?>