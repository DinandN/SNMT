<?php
include "../include/function.php";
checkSession();
if ( $_SESSION['level'] == 3) {
    $table = "vlan";
    $tableDHCP = "prefixDHCP";
    // Establish the database connection
    $pdo = connectToDB('users');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }

    $query = "SELECT * FROM $table ORDER BY vlanStart ASC";
    $statement = $pdo->query($query);

    $query2 = "SELECT * FROM $tableDHCP WHERE id = 1";
    $statement2 = $pdo->query($query2);

    if ($statement && $statement2){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $row2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
        
        echo (
            generateImageDiv() ."
            <form method='post'>
                <label class='labelPost2'>Temp DHCP</label>
                <div class='displayFlex'>
                    <input class='inputTextField' type='text' name='tempDHCP' value='". $row2[0]['prefix']."'/> 
                    <input class='buttonOutsideTable' type='submit' name='submitDHCP' value='Submit' />
                </div>
            </form>
            <div class='tableContainer'>
                <table  class='table' width='100%'>
                <tr class='tableHeader'>
                    <th>Vlan Start</th>
                    <th>Vlan Einde</th>
                    <th>Verwijderen</th>
                </tr>
                ");
                foreach ($rows as $row) {
                    echo ("
                        <tr>
                            <td> Vlan Start: ". $row['vlanStart'] . "</td>
                            <td> Vlan Einde: ". $row['vlanEnd'] . "</td>
                            <td>
                            <form method='post' class='formDeleteButtons'>
                                <input type='hidden' name='vlanStart' value='" . $row['vlanStart'] . "'>
                                <input type='hidden' name='vlanEnd' value='" . $row['vlanEnd'] . "'>
                                <button type='submit' name='deleteButtonUser'>Verwijder</button>");
                                if(isset($_POST['deleteButtonUser'])  && $_POST['vlanStart'] === $row['vlanStart'] && $_POST['vlanEnd'] === $row['vlanEnd']){
                                    $queryDelete = "DELETE FROM $table WHERE vlanStart = '$row[vlanStart]' AND vlanEnd = '$row[vlanEnd]'";
                                    $delete = $pdo->query($queryDelete);
                                    header("Location: ./redirect.php?page=vlanManager");
                                }
                                echo("
                                </form>
                            </td>
                        </tr>
                    ");
                }

            echo ("
            </table>
                <div class='displayFlex'>
                    <button name='addVlan' class='buttonOutsideTable'><a href='./createVlan.php' >Toevoegen</a></button>
                    <button class='buttonOutsideTable'><a href='./settings.php'>Terug</a></button>
                </div>
            </div>
            
            ");

            if(isset($_POST['submitDHCP'])){
                $queryUpdate = "UPDATE $table2 SET prefix = '$_POST[tempDHCP]' WHERE id = 1";
                $update = $pdo->query($queryUpdate);
                if ($update) {
                    header("Location: ./redirect.php?page=vlanManager");
                    exit; 
                }else{
                    echo "Error updating record: " . $pdo->errorInfo()[2];//check if the query is executed
                }
            }
    }else {
        die("Query execution failed: " . $pdo->errorInfo()[2]);//Check if the query is executed
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