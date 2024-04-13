<?php
include "../include/function.php";
checkSession();
if ($_SESSION['level'] == 3 || $_SESSION['level'] == 2 || $_SESSION['level'] == 1) {

    $pdo = connectToDB('radius');

    if (!$pdo) {
        die("Connection failed"); // Check if the connection is made
    }

    $username = $_GET['name'];

    $table = "radacct";

    $query = "SELECT * FROM $table WHERE username = '$username' AND acctstoptime IS NULL";
    $statement = $pdo->query($query);

    $query2 = "SELECT t1.callingstationid, t2.last_online
           FROM (
               SELECT callingstationid, MAX(acctstoptime) AS last_online
               FROM $table
               WHERE username = :username
               GROUP BY callingstationid
           ) t2
           JOIN $table t1 ON t1.callingstationid = t2.callingstationid AND t1.acctstoptime = t2.last_online";
    $statement2 = $pdo->prepare($query2);
    $statement2->bindParam(':username', $username, PDO::PARAM_STR);
    $statement2->execute();

    if ($statement && $statement2) {
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $rows2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
        $rows = array_reverse($rows);
        echo (
            generateImageDiv() . "
        <div class='displayFlex'>
            <label class='labelPost'>Gebruikersinformatie van " . $username . "</label>
            <button class='buttonOutsideTable' onclick='history.back()'>Terug</button>
        </div>
        <div class='tableContainer'>
        <table>
        <thead>
        <tr>
            <th colspan='4' class='tTitle'> Huidige Online</th>
        </tr>
        <tr class='tableheader'>
            <th>MAC Address</th>
            <th>Mac Vendor</th>
            <th>Start Tijd</th>
            <th>Access point</th>
        </tr>
        </thead>
        <tbody>"
        );
        foreach ($rows as $row) {
            echo ("
            <tr>
            <td>" . $row['callingstationid'] . "</td>
            <td class='macVendor' style='width: 40%;''>" . $row['callingstationid'] . "</td>
            <td>" . $row['acctstarttime'] . "</td>
            <td>" . $row['nasipaddress'] . "</td>
            </tr>
            ");
        }
        echo ("
        </tbody>
        </table>
        </div>
        <br/>
        <div class='tableContainer'>
        <table>
        <thead>
        <tr>
            <th colspan='4' class='tTitle'>History</th>
        </tr>
        <tr class='tableheader'>
            <th>MAC Address</th>
            <th>Mac Vendor</th>
            <th>Laats Online</th>
        </tr>
        </thead>"
        );
        foreach ($rows2 as $row) {
            echo ("
            <tr>
            <td><a href='./deviceData.php?name=$_GET[name]&macAddress=" . $row['callingstationid'] . "'>" . $row['callingstationid'] . "</a></td>
            <td class='macVendor' style='width: 40%;''>" . $row['callingstationid'] . "</td>
            <td>" . $row['last_online'] . "</td>
            </tr>
            ");
        }
        echo ("
        </tbody>
        </table>
        </div>
        <br/>");
    } else {
        die("Query execution failed: " . $pdo->errorInfo()[2]);
    }
} else {
    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo htmlHeader(); ?>
</head>
<body>
    <!-- Your existing HTML content here -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../include/script.js"></script>
    <script>
         fetchMacAddressInfo();
    </script>
</body>
</html>





