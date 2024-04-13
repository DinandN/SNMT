<?php
include "../include/function.php";
checkSession();// Check if the user is logged in

    // Generate the html based on user permition level 

    echo (htmlHeader().
    "
    <body>
    ". generateImageDiv() 
    );

    if ($_SESSION["level"] == 3){
        echo ("
        <div class='centerDiv'>
            <label class='labelWelkom'>Welkom Beheerder " .$_SESSION['username'] . " </label>
        </div>
        <div class='divNavButton'>
            <button class='navButton'><a href='./businesses.php'>Bedrijven Tabel</a></button><br>
            <button class='navButton'><a href='./userOnline.php'>Gebruikers Online</a></button><br>
            <button class='navButton'><a href='./radreply.php'>Vlan's in Gebruik</a></button><br>
            <button class='navButton'><a href='./macDropdown.php'>MAC Filter</a></button><br>
            <button class='navButton'><a href='./nas.php'>Acces Points</a></button><br>
            <button class='navButton'><a href='./settings.php'>Instellingen</a></button><br>
    ");
    }else if ($_SESSION["level"] == 2){
        echo ("
        <div class='centerDiv'>
            <label class='labelWelkom'>Welkom Medwerker " .$_SESSION['username'] . " </label>
        </div>
        <div class='divNavButton'>
            <button class='navButton'><a href='./businesses.php'>Bedrijven Tabel</a></button><br>
            <button class='navButton'><a href='./userOnline.php'>Gebruikers Online</a></button><br>
            <button class='navButton'><a href='./radreply.php'>Vlan's in Gebruik</a></button><br>
            <button class='navButton'><a href='./macDropdown.php'>MAC Filter</a></button><br>
        ");
    }
    else if ($_SESSION["level"] == 1){
        echo ("
        <div class='centerDiv'>
            <label class='labelWelkom'>Welkom Gebruiker " .$_SESSION['username'] . " </label>
        </div>
        <div class='divNavButton'>
            <button class='navButton'><a href='./userScreen.php'>Gebruikers Tabel</a></button><br>
    ");
    }

    echo("
    <form method='post'>
    <input class='navButton' name='loginScreen' type='submit' value='Login Scherm'>
    </form>
    <br>
    </div>
</body>");

if (isset($_POST['loginScreen'])) {
    header("Location: ./logout.php");
    exit;
}
?>