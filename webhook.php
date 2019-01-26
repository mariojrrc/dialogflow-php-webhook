<?php
declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

/**
* Envia uma requisição para a API do OpenWeatherMap e recupera
* a informação do tempo para uma cidade
*
* @param string $city
* @return string
*/
function getWeatherInformation(string $city): string
{
   $apiKey = \getenv("OPEN_WEATHER_MAP_API_KEY");
   $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&appid=$apiKey&lang=pt";
   $weather = \file_get_contents($weatherUrl);

   $weatherDetails = \json_decode($weather, true);

   $temperature = \round($weatherDetails["main"]["temp"] ?? 0, 1);
   $weatherDescription = $weatherDetails["weather"][0]["description"] ?? '';

   return sendFulfillmentResponse($temperature, $weatherDescription);
}

/**
* Envia a informação do tempo para o Dialogflow
*
* @param float $temperature
* @param string  $weatherDescription
*
* @return string
*/
function sendFulfillmentResponse(float $temperature, string $weatherDescription): string
{
   $response = "Faz $temperature graus com $weatherDescription";

   $fulfillment = [
       "fulfillmentText" => $response
   ];

   return \json_encode($fulfillment);
}

// listen to the POST request from Dialogflow
$request = \file_get_contents("php://input");
$requestJson = \json_decode($request, true);

$city = $requestJson['queryResult']['parameters']['geo-city'] ?? '';

if (isset($city) && !empty($city)) {
   echo getWeatherInformation($city);
}
