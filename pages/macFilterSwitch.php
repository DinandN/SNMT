<?php

include "../include/function.php";
    checkSession();

if ($_SESSION['level'] == 3) {
    echo htmlHeader();
    $table = "macFilterSwitch";
    $statusArray = [];

    // Establish the database connection
    $pdo = connectToDB('users');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }

    $query = "SELECT * FROM $table";
    $statement = $pdo->query($query);
    if ($statement){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        echo (
            generateImageDiv() ."
            <div class='tableContainer'>
                <table  class='table' width='100%'>
                <tr class='tableHeader'>
                    <th>IP</th>
                    <th>Naam</th>
                    <th>Gebruikersnaam</th>
                    <th>Wachtwoord</th>
                    <th>Router</th>
                    <th>macFilter</th>
                    <th>Verwijderen</th>
                    <th>Update</th>
                </tr>
                ");
                foreach ($rows as $row) {
                        echo "<tr";

                        
                        if ($_SESSION['statusArray'][$row['ip']] == "Online" && $_SESSION['activated'] ) {
                            echo " class='onlineUser'";
                        }else if ($_SESSION['statusArray'][$row['ip']] == "Offline" && $_SESSION['activated']){
                            echo " class='offlineUser'";
                        }
                        echo ">";

                    echo ("
                        <td>" . $row['ip'] . "</td>
                        <td>" . $row['name'] . "</td>
                        <td>" . $row['username'] . "</td>
                        <td>" . $row['password'] . "</td>
                    <td>"); 
                    if($row['router'] == 1){echo("Ja");}else{echo("Nee");}
                    echo ("</td> <td>");
                    if($row['switch'] == 1){echo("Ja");}else{echo("Nee");}
                    echo ("</td>
                            <td>
                                <form method='post' class='formDeleteButtons'>
                                    <input type='hidden' name='id' value='" . $row['id'] . "'>
                                    <button type='submit' name='deleteButtonUser'>Verwijder</button>");
                    if(isset($_POST['deleteButtonUser'])  && $_POST['id'] === $row['id']){       
                        $queryDelete = "DELETE FROM $table WHERE id = :id";
                        $delete = $pdo->prepare($queryDelete);
                        $delete->bindParam(':id', $row['id']); // Assuming $row['id'] contains the ID you want to delete.
                        if ($delete->execute()) {
                            echo("<script type='text/javascript'>
                            window.location.href = './macFilterSwitch.php';
                            </script>");  
                        }
                    }
                    echo("
                            </form>
                            </td>
                            <td><button><a href='./CUMacFilterSwitch.php?id=" . $row['id'] . "'>Update</a></button></td>
                        </tr>
                    ");
                }
            echo ("
            </table>
               
                    
                    
                    <form method='post' class='formDeleteButtons'>
                        <div class='displayFlex'>
                            <button class='buttonOutsideTable3' name='addUser'><a href='./CUMacFilterSwitch.php'>Toevoegen</a></button>
                            <button class='buttonOutsideTable3' name='testSwitches' type='submit'>Test</button>
                            <button class='buttonOutsideTable3' ><a href='./settings.php'>Terug</a></button>
                        </div>
                    </form>
                    
                
            </div>
            ");

            
        if (isset($_POST['testSwitches'])) {
            $_SESSION['statusArray'] = [];
            foreach ($rows as $row) {
                $ip = $row['ip'];
                $username = $row['username'];
                $password = $row['password'];

                $connect = connectToSSH($ip, $username, $password);
                if ($connect) {
                    $statusArray[$ip] = "Online";
                } else {
                    $statusArray[$ip] = "Offline";
                }
            }
            $_SESSION['statusArray'] = $statusArray;
            $_SESSION['activated'] = true;
            echo "<meta http-equiv='refresh' content='0'>";
        }
    }else {
        die("Query execution failed: " . $pdo->errorInfo()[2]);//Check if the query is executed
    }
}else{
    header("Location: ../index.php");// Redirect to login page if there is no session
}

?>