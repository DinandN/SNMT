<?php 
session_start();
if (!$_SESSION["boolSession"])  {
    header("Location: ../index.php");
}

session_destroy();
header("Location: ../index.php");