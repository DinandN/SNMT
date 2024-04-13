<?php

//connect to database
function connectToDB($database){
    $server = "localhost";
	$port = 3306;
	$login = "root";
	$password = "1234";
    $database = $database;

    $dsn = "mysql:host=$server;port=$port;dbname=$database";
    $pdo = new PDO($dsn, $login, $password);
    return $pdo;
}

//connect to ssh
function connectToSSH($host , $username , $password){
    $host = $host;
    $port = 22;
    $connection = ssh2_connect($host , $port);
    if($connection) {
        $auth = ssh2_auth_password($connection, $username, $password);
        return $connection;
    }
}
?>