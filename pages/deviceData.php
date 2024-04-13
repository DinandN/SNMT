<?php
include "../include/function.php";
checkSession();
if ($_SESSION['level'] == 3 || $_SESSION['level'] == 2 || $_SESSION['level'] == 1) {

    $pdo = connectToDB('radius');

    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }

    $username = $_GET['name'];
    $macAddress = $_GET['macAddress'];

    $table = "radacct";

    $query = "SELECT * FROM $table WHERE username = '$username' AND callingstationid ='$macAddress' ";
    $statement = $pdo->query($query);
    if($statement){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_reverse($rows);
        echo (
        generateImageDiv() ."
        <div class='displayFlex'>
            <label class='labelPost'>Apparaatgeschiedenis van " . $username . "</label>
            <button class='buttonOutsideTable' onclick='history.back()'>Terug</button>
        </div>
        <div class='tableContainer'>
        <table>
        <thead>
            <tr class='tableheader'>
            <th>Start Tijd</th>
            <th>Stop Tijd</th>
            <th>Sessie Tijd</th>
            <th>Input Octets</th>
            <th>Output Octets</th>
            <th>Access point</th>
            </tr>
        </thead>
        <tbody>");
        foreach($rows as $row){
            $result = convertTime($row['acctsessiontime']);
            echo ("
            <tr>
            <td>" . $row['acctstarttime'] . "</td>
            <td>" . $row['acctstoptime'] . "</td>
            <td>" . $result . "</td>
            <td>" . $row['acctinputoctets'] . "</td>
            <td>" . $row['acctoutputoctets'] . "</td>
            <td>" . $row['nasipaddress'] . "</td>
            </tr>
            ");
            
        }
        echo ("
        </tbody>
        </table>
        </div>
        ");
    }else{
        die("Query execution failed: " . $pdo->errorInfo()[2]);
    }
}else{
    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
    
</body>
</html>