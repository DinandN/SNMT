<?php
include "../include/function.php";
checkSession();

$page = $_GET['page'];

switch ($page) {
    case 'user':
        header("Location: ./user.php");
        break;
    case 'nas':
        header("Location: ./nas.php");
        break;
    case 'specialUser':
        header("Location: ./specialUser.php?table=". $_GET['table']);
        break;
    case 'macFilterSwitch':
        header("Location: ./macFilterSwitch.php");
        break;
    case 'macssid':
        header("Location: ./macssid.php?name=".$_GET['name']);
        break;
    case 'businesses':
        header("Location: ./businesses.php");
        break;
    case 'user2':
        header("Location: ./user2.php?id=".$_GET['id']."&vlan=".$_GET['vlan']);
        break;
    case 'blockedPorts':
        header("Location: ./blockedPorts.php");
        break;
    case 'sessionTime':
        header("Location: ./settings.php");
        break;
    case 'vlanManager':
        header("Location: ./vlanManager.php");
        break;
    default:
    header("Location: ../buttonScreen.php");
        break;
}


?>