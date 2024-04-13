<?php 


//function to convert int seconds to time as string
function convertTime($seconds) {
    $minutes = floor($seconds / 60);
    $seconds %= 60;
    $hours = floor($minutes / 60);
    $minutes %= 60;
    $days = floor($hours / 24);
    $hours %= 24;
    $months = floor($days / 30);
    $days %= 30;
    $result = '';
    if ($months > 0) {
        $result .= $months . ' mo ';
    }
    if ($days > 0) {
        $result .= $days . ' day ';
    }
    if ($hours > 0) {
        $result .= $hours . ' hr ';
    }
    if ($minutes > 0) {
        $result .= $minutes . ' min ';
    }
    $result .= $seconds . ' sec';
    return $result;
}

//html header
function htmlHeader(){
    $html = "
    <head>
        <link rel='icon' type='image/x-icon' href='../img/thumbnail_favicon.ico'>
        <link rel='stylesheet' href='../css/style.css'>
        <title>SNMT</title>
    </head>
    ";
    return $html;
}

//html (header) showcase the buisnesses logo
function generateImageDiv() {
    $html = "
        <div class='img bigImg svgBackground'>
            <image src='../img/mijnwerk.jpg' />
        </div>
    ";
    return $html;
}

//convert time from time to seconds
function convertTimeToSeconds($time)
{
    preg_match('/(\d+)m(\d+)s/', $time, $matches);
    $minutes = (int)$matches[1];
    $seconds = (int)$matches[2];

    return $minutes * 60 + $seconds;
}


//function to check if the user is logged in
function checkSession() {
    session_start();
    include "../include/database.php"; // Include your database connection code here.
    $pdo = connectToDB("users"); // Define or include your database connection code here.
    
    if (!$pdo) {
        die("Connection failed");
    }
    
    $query = "SELECT * FROM sessionTime";
    $statement = $pdo->query($query);
    //get session Max time from db
    if (!$statement) {
        die("Query execution failed");
    }
    
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    //check if the session is set and if the time is not expired
    if (!isset($_SESSION["boolSession"]) || !isset($_SESSION["time"]) || (time() - $_SESSION['time']) > $rows[0]['time']) {
        header("Location: ./logout.php");
        exit(); // Make sure to exit after setting a header to prevent further execution.
    }
}

function isValidMacAddress($macAddress) {
    // Regular expression pattern for a valid MAC address
    $pattern = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';

    // Use preg_match to check if the MAC address matches the pattern
    if (preg_match($pattern, $macAddress)) {
        return true; // Valid MAC address
    } else {
        return false; // Invalid MAC address
    }
}


