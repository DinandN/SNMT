<?php
include "../include/function.php";
checkSession();
if ($_SESSION['level'] == 3) {
    $_SESSION['activated'] = false;
    echo (htmlHeader() ."
    <body>
    ". generateImageDiv() ."
        <div class='centerDiv'>
            <label class='labelWelkom'>Welkom Beheerder " .$_SESSION['username'] . " </label>
        </div>
        <div class='divNavButton'>
            <button class='navButton'><a href='./specialUser.php?table=admins'>Beheerder</a></button><br>
            <button class='navButton'><a href='./specialUser.php?table=colleague'>Medewerkers</a></button><br>
            <button class='navButton'><a href='./specialUser.php?table=blocked'>Gasten</a></button><br>
            <button class='navButton'><a href='./sessionTime.php'>Sessie Tijd</a></button><br>
            <button class='navButton'><a href='./vlanManager.php'>Vlan</a></button><br>
            <button class='navButton'><a href='./blockedPorts.php'>Blocked Ports</a></button><br>
            <button class='navButton'><a href='./macFilterSwitch.php'>Switches/Routers</a></button><br>
            <button class='navButton'><a href='./buttonScreen.php'>Menu Scherm</a></button><br>
            
        </div>
    </body>
    ");
    }else{
        header("Location: ../index.php");
    }

?>