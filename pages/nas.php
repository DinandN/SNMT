<?php
//start session
include "../include/function.php";
    checkSession();
if ($_SESSION['level'] == 3) {
    ob_start();
    //vars
    $table = "nas";

    // Establish the database connection
    $pdo = connectToDB('radius');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }

    $query = "SELECT * FROM $table";
    $statement = $pdo->prepare($query);
    if ($statement->execute()) {//Check if the query is executed
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        //build table
        echo htmlHeader(); 
        echo generateImageDiv()."
        <div class='tableContainer'>
        <table class='table'>
        <thead>
            <tr class='tableHeader'>
                <th>Nasname</th>
                <th>Shortname</th>
                <th>Type</th>
                <th>Ports</th>
                <th>Secret</th>
                <th>Server</th>
                <th>Community</th>
                <th>Description</th>
                <th>Verwijderen</th>
                <th>Update</th>
            </tr>
        </thead>";
        foreach ($rows as $row) {
            echo ("
            <tbody>
            <tr>
            <td>" . $row['nasname'] . "</td>
            <td>" . $row['shortname'] . "</td>
            <td>" . $row['type'] . "</td>
            <td>" . $row['ports'] . "</td>
            <td>" . $row['secret'] . "</td>
            <td>" . $row['server'] . "</td>
            <td>" . $row['community'] . "</td>
            <td>" . $row['description'] . "</td>
            <td>
            <form method='post'>
                    <input type='hidden' name='nasId' value='" . $row['id'] . "'>
                    <button type='submit' name='deleteButtonNas' class='formDeleteButtons'>Verwijder</button>
            </form>
            ");
            //button to delete user from nasTable
            if (isset($_POST['deleteButtonNas']) && $_POST['nasId'] == $row['id']) {
                $id = $row['id'];
                $queryDelete = "DELETE FROM $table WHERE id = $id";
                $delete = $pdo->query($queryDelete);
                header("Location: ./redirect.php?page=nas");
            }
            echo ("
            </td>
            <td><button><a href='./CUNas.php?id=" . $row['id'] . "'>Update</a></button></td>
            </tr>
            </tbody>
            ");
        
        }
        echo ("
        </table>
            <div class='displayFlex'>
                
                <button class='buttonOutsideTable'><a href='./CUNas.php'>Toevoegen</a></button>
                <button class='buttonOutsideTable'><a href='./buttonScreen.php'>Terug</a></button>
            </div>
        </div>
        ");
    } else {
        die("Query execution failed: " . $statement->errorInfo()[2]);
    }
}else{
    header("Location: ./redirect.php?page=home");
}

?>