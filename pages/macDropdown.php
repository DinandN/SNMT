<?php
include "../include/function.php";
checkSession();

//dropdown menu for selecting a router 

if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {
    $pdo = connectToDB('users'); // Establish the database connection
    if (!$pdo) {
        die("Connection failed");// Check if the connection is established
    }
    $table = "macFilterSwitch";

    $query = "SELECT name, router FROM $table where router = 1 ORDER BY name ASC";// Make query to retrieve switch information
    $statement = $pdo->query($query);

    if ($statement) {
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        echo(
        generateImageDiv()
        ."
        
        <form method='post'>
        <select class='dropdownMenu' name='dropdownMenu'>" ); 
        foreach($rows as $row){// Generate dropdown options
            echo ("
            <option value='" . $row['name'] . "'>". $row['name'] ."</option>
            <br><br>
            ");

        } 
        echo ("
        
        <br><br>
        
        </select>
        <div class='displayFlex'>
            <input class='buttonOutsideTable' type='submit' name='Submit' value='Versturen'> ");
            if(isset($_GET['vlan'])){
                echo ("<button class='buttonOutsideTable'><a href='./user2.php?vlan=$_GET[vlan]&id=$_GET[id]'>Terug</a></button>");
            }else{
               echo ("<button class='buttonOutsideTable'><a href='./buttonScreen.php'>Terug</a></button>");
            }
            
            echo ("
        </div>
        </form>
        
        ");
    } else {
        die("Query execution failed pdoRadius: " . $pdoRadius->errorInfo() . "\n Query execution failed pdoUsers: "  . $pdoUsers->errorInfo());
    }
    if(isset($_POST['Submit']) ){
        if (!isset($_GET['vlan'])){
            header("Location: ./macssid.php?name=$_POST[dropdownMenu]"); 
        }else {
            header("Location: ./macssid.php?name=$_POST[dropdownMenu]&vlan=$_GET[vlan]&id=$_GET[id]");
        }
        
    } 
}else{
    header("Location: ../index.php");// Redirect to login page if there is no session
}

?>
<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
</body>
</html>