<?php
include "../include/function.php";
checkSession();

//check if user is admin
if ($_SESSION['level'] == 3) {
    $table = 'blockedPorts';
    //connect to db
    $pdo = connectToDB('users');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }
    //select query
    $query = "SELECT defaultVlan FROM $table WHERE id = 1";
    $statement = $pdo->query($query);
    if ($statement){
        $row = $statement->fetchAll(PDO::FETCH_ASSOC);
        //echo html
        echo (htmlHeader().
            generateImageDiv() . " 
            
            <form method='post'>
                <label class='labelPost2'>Default Vlan</label>
                <div class='displayFlex'>
                
                    <input class='inputTextField' type='number' name='defaultVlan' value='". intval($row[0]['defaultVlan']) ."'/> 
                    <input class='buttonOutsideTable' type='submit' name='submit' value='Submit' />
                </div>
            </form>
        ");

        //check if submit button is pressed
        if(isset($_POST['submit'])){//update query for blocking ports
            $queryUpdate = "UPDATE $table SET defaultVlan = :defaultVlan WHERE id = 1";
            $statement = $pdo->prepare($queryUpdate);
            $statement->bindParam(':defaultVlan', $_POST['defaultVlan']);
            if ($statement->execute()) {
                header("Location: ./redirect.php?page=blockedPorts");//header to refresh the page
                exit; 
            }else{
                echo "Error updating record: " . $pdo->errorInfo()[2];//check if the query is executed
            }
        }
    }
    //select query for blocked ports to display in a table
    $query = "SELECT switchName, portName, id FROM $table WHERE id != 1";
    $statement = $pdo->query($query);
    if ($statement){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        //display table
        echo ("
        <div class='tableContainer'>
                    <table class='table'>
                    <tr>
                        <th colspan='3' class='tTitle'>Geblokkeerde Ports</th>
                    </tr>
                    <tr class='tableHeader'>
                        <th>Switch Naam</th>
                        <th>Port Naam</th>
                        <th>Delete</th>
                    </tr>
        ");
        foreach ($rows as $row) {
            echo ("
            <tr>
                <td>" . $row['switchName'] . "</td>
                <td>" . $row['portName'] . "</td>
                <td>
                    <form method='post' class='formDeleteButtons'>
                        <input type='hidden' name='id' value='" . $row['id'] . "'>
                        <button type='submit' name='deleteButtonUser'>Verwijder</button>
                    </form>
                </td>
            </tr>
            ");
        }
        
        echo ("</table></div>");
        
        // Check if the "Delete" button is clicked
        if (isset($_POST['deleteButtonUser'])) {
            $deleteId = $_POST['id'];
            $queryDelete = "DELETE FROM $table WHERE id = :deleteId";///delete query
            $stmt = $pdo->prepare($queryDelete);
            $stmt->bindParam(':deleteId', $deleteId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: ./redirect.php?page=blockedPorts");
            } 
        }
    }
    echo ("
    <div class='displayFlex'>
        
        <button class='buttonOutsideTable'><a href='./createBlockedCable.php'>Toevoegen</a></button>
        <button class='buttonOutsideTable'><a href='./settings.php'>Terug</a></button>
    </div>
    ");

    
}
?>
