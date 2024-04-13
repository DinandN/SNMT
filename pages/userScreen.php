<?php

include "../include/function.php";
    checkSession();

if ($_SESSION['level'] == 1 || $_SESSION['level'] == 2 || $_SESSION['level'] == 3) {

    $table = "radcheck"; //user table
    $table2 = "radreply"; // vlan table
    $table3 = "radacct"; //online user table
    $arrayVal = 0;
    $onlineUser = 0;
    $boolOnline = false;

    // Establish the database connection
    $pdo = connectToDB('radius');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }

    $query = "SELECT * FROM $table WHERE username = '$_SESSION[username]' ORDER BY username ASC";
    $statement = $pdo->query($query);

    $query2 = "SELECT `value` FROM $table2 WHERE `id` % 3 = 0 AND username = '$_SESSION[username]'";
    $statement2 = $pdo->query($query2);

    // Query3 for the last used acctstoptime of each username
    $query3 = "SELECT t1.username, t1.acctstoptime
    FROM $table3 AS t1
    INNER JOIN (
        SELECT username, MAX(acctstoptime) AS max_time
        FROM $table3
        GROUP BY username
    ) AS t2 ON t1.username = t2.username AND t1.acctstoptime = t2.max_time
    ORDER BY t1.username ASC";
    $statement3 = $pdo->query($query3);

    $query4 = "SELECT acctstoptime, username FROM $table3 WHERE acctstoptime IS NULL ORDER BY username ASC";
    $statement4 = $pdo->query($query4);
    if ($statement && $statement2 && $statement3 && $statement4) {
        // Fetch all rows from the result set
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $rows2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
        $rows3 = $statement3->fetchAll(PDO::FETCH_ASSOC);
        $rows4 = $statement4->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            //Create table
            echo (
            generateImageDiv() ."
            <div class='tableContainer'>
                <table  class='table'>
                <tr class='tableHeader'>
                    <th>Gebruikersnaam</th>
                    <th>Wachtwoord</th>
                    <th>Laatst Online</th>

                    <th>Update Gegevens</th>
                    
                </tr>
            ");
            foreach ($rows as $row) {
                //check which user is online and give it a different color
                if ($rows4[$onlineUser]['username'] == $row['username']){
                    echo "<tr class='onlineUser'>";
                    $boolOnline = true;
                    $onlineUser++;
                }else{
                    echo "<tr>";
                    $boolOnline = false;
                }
                echo ("
                <td><a href='./userData.php?name=" . $row['username'] . "'>" . $row['username']. "</a></td>
                <td>" . $row['value'] . "</td>
                ");
                $lastOnline = NULL;
                foreach ($rows3 as $row3) {
                    if ($row3['username'] == $row['username']) {
                        $lastOnline = $row3['acctstoptime'];
                        break;
                    }
                }
                if ($lastOnline == NULL) {
                    echo "<td> Geen Data </td>";
                } else if($boolOnline){
                    echo "<td> Online </td>";
                }else {
                    echo "<td>" . $lastOnline . "</td>";
                }

                echo ("<td><button><a href='./CUUser.php?idUser=" . $row['id'] . "&vlan=".$rows2[$arrayVal]['value']."'>Update</a></button></td>
                </tr>
                ");
                $arrayVal++;
            }
            echo ("
            </table>
                <div class='displayFlex'>
                    <button class='buttonOutsideTable2'><a href='./buttonScreen.php'>Terug</a></button>
                </div>
            </div>
            ");
        }
    }else {
        die("Query execution failed: " . $pdo->errorInfo()[2]);//Check if the query is executed
    }
}else{
    header("Location: ../index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
<br>
  
</body>
</html>