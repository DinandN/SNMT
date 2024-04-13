<?php
include "../include/function.php";
checkSession();
if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {

    $pdo = connectToDB('radius');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }

    $table = "radacct";
    $totalUsers = 0;

    $query = "SELECT * FROM $table WHERE acctstoptime IS NULL";
    $statement = $pdo->query($query);
    if($statement){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $totalUsers = count($rows);
        echo (
            generateImageDiv() ."
        <div class='tableContainer'>
        <div class='displayFlex'>
            <label class='labelPost'>". "Gebruikers Online: " .  $totalUsers . "</label><br/>
        </div>
        <table class='table'>
        <tr>
        <th>Gebruikersnaam</th>
        <th>Locatie</th>
        <th>Online Sinds</th>
        </tr>
        ");
        foreach($rows as $row){
            echo ("
            <tr>
            <td>" . $row['username'] . "</td>
            ");
            if(substr($row['nasipaddress'], 0, 6) === "10.199"){
                echo "<td> MijnWerk 2 </td>";
            }
            else if(substr($row['nasipaddress'], 0, 5) === "10.99"){
                echo "<td> MijnWerk 1 </td>";
            }else{
                echo "<td> Onbekend </td>";
            }
            echo ("
            <td>" . $row['acctstarttime'] . "</td>
            </tr>"
            );
        }
        echo "</table></div>";
    }else{
        die("Query execution failed: " . $pdo->errorInfo()[2]);
    }
}else{
    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php echo htmlHeader(); ?>
<body>
    <div class='displayFlex'>
    <button class='buttonOutsideTable2'><a href='./buttonScreen.php'>Terug</a></button>
    </div>
</body>
</html>