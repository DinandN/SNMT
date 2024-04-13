<?php
include "../include/function.php";
checkSession();

if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {
    $pdo = connectToDB('users'); // Establish the database connection

    if (!$pdo && !$pdoRad) {
        die("Connection failed"); // Check if the connection is made
    }

    $table = "vlan";
    $tableBusinesses = "businesses";

    // Make query to retrieve VLAN information
    $queryColumns = "SELECT * FROM $table ORDER BY vlanStart ASC";
    $statementColumns = $pdo->query($queryColumns);

    // Make query to retrieve radreply information
    $query = "SELECT vlan, name FROM $tableBusinesses";
    $statement = $pdo->query($query);

    if ($statementColumns && $statement) { // Check if the query is executed
        $rows = $statementColumns->fetchAll(PDO::FETCH_ASSOC);
        $rowsBusinesses = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Build table
        echo generateImageDiv();
        echo "<table width='100%'>";
        echo "<tr class='tableHeader'>";
        echo "<th>Vlan</th>";
        echo "<th>Gebruikersnaam</th>";
        echo "</tr>";

        foreach ($rows as $row) {
            $vlanStart = $row['vlanStart'];
            $vlanEnd = $row['vlanEnd'];

            for ($i = $vlanStart; $i <= $vlanEnd; $i++) {
                $vlanExists = false;
                $username = '';

                // Check if the VLAN exists in radreply table
                foreach ($rowsBusinesses as $rowsBusiness) {
                    if ($rowsBusiness['vlan'] == $i) {
                        $vlanExists = true;
                        $username = $rowsBusiness['name'];
                        break;
                    }else{
                        $vlanExists = false;
                    }
                }

                // Display VLAN information
                if ($vlanExists) {
                    echo "<tr>";
                    echo "<td>$i</td>";
                    echo "<td>$username</td>";
                    echo "</tr>";
                }else {
                    echo "<tr class='vlanNoMatch'>";
                    echo "<td >$i</td>";
                    echo "<td>Niet in Gebruik</td>";
                    echo "</tr>";
                }
            }
        }

        echo "</table>";
    } else {
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
    <div class='displayFlex'>
    <button class='buttonOutsideTable2'><a href="./buttonScreen.php">Terug</a></button>
    </div>
</body>
</html>
