<?php
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin

$macAddress = $_GET['macAddress'];
$apiUrl = 'https://api.macvendors.com/' . $macAddress;

$response = file_get_contents($apiUrl); // Make the request to the external API
echo $response;
?>
