<?php
include "../include/function.php";
checkSession();

$vlan = $_GET['vlan'];
$id = $_GET['id'];

$table = "radcheck"; 
$table2 = "radreply";
$table3 = "radacct";
$tableUsers = "admins";
$tableUsers2 = "colleague";
$tableUsers3 = "blocked";

$pdo = connectToDB('radius'); // Establish the database connection
$pdoUsers = connectToDB('users'); // Establish the database connection
if (!$pdo || !$pdoUsers) {
    die("Connection failed");//Check if the connection is made
}


if(isset($_GET['idUser'])){
    $idUser = $_GET['idUser'];//get the id from the url
    $IDTable2 = $idUser * 3;//set ID for second table
    
    $query = "SELECT username, value FROM $table WHERE id = $idUser";
    $statement = $pdo->query($query);
    $query2 = "SELECT value FROM $table2 WHERE id = $IDTable2";
    $statement2 = $pdo->query($query2);
    if ($statement && $statement2){//check if the query is executed
        $row = $statement->fetchAll(PDO::FETCH_ASSOC);
        $username = $row[0]['username'];
        $value = $row[0]['value'];
        $row2 = $statement2->fetch(PDO::FETCH_ASSOC);
        $vlan = $row2['value'];
    } 
    else {
        die("Query execution failed: " . $pdo->errorInfo()[2]);//show error if the query is not executed
    }
    if (isset($_POST['Submit'])) {
        try {
            $queryUpdate = "UPDATE $table SET username=?, value=? WHERE id=?";
            $stmt = $pdo->prepare($queryUpdate);
            $stmt->execute([$_POST['username'], $_POST['password'], $idUser]);
    
            $queryUpdate2 = "UPDATE $table2 SET username=? ,  value=?  WHERE id=?";
            $stmt2 = $pdo->prepare($queryUpdate2);
            $stmt2->execute([$_POST['username'], $vlan , $IDTable2]);
    
            $queryUpdate3 = "UPDATE $table2 SET username=? WHERE id=?";
            $stmt3 = $pdo->prepare($queryUpdate3);
            $stmt3->execute([$_POST['username'], $IDTable2 - 1]);
    
            $queryUpdate4 = "UPDATE $table2 SET username=? WHERE id=?";
            $stmt4 = $pdo->prepare($queryUpdate4);
            $stmt4->execute([$_POST['username'], $IDTable2 - 2]);
    
            $queryUpdate5 = "UPDATE $table3 SET username=? WHERE username=?";
            $stmt5 = $pdo->prepare($queryUpdate5);
            $stmt5->execute([$_POST['username'], $username]);
    
            $queryUpdate6 = "UPDATE $tableUsers SET username=? WHERE username=?";
            $stmt6 = $pdoUsers->prepare($queryUpdate6);
            $stmt6->execute([$_POST['username'], $username]);
    
            $queryUpdate7 = "UPDATE $tableUsers2 SET username=? WHERE username=?";
            $stmt7 = $pdoUsers->prepare($queryUpdate7);
            $stmt7->execute([$_POST['username'], $username]);
    
            $queryUpdate8 = "UPDATE $tableUsers3 SET username=? WHERE username=?";
            $stmt8 = $pdoUsers->prepare($queryUpdate8);
            $stmt8->execute([$_POST['username'], $username]);
    
            if ($_SESSION['username'] == $username) {
                $_SESSION['username'] = $_POST['username'];
            }
            
            if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {
                header("Location: ./user2.php?id=$_GET[id]&vlan=$vlan");
            } else {
                header("Location: ./userScreen.php");
            }
            exit;
        } catch (PDOException $e) {
            echo "SQL Error: " . $e->getMessage();
        }
    }
}else{
 
                $query = "INSERT INTO $table VALUES (NULL, :username, 'Cleartext-Password', ':=', :password)";
                $query2 = "INSERT INTO $table2 VALUES (NULL, :username, 'Tunnel-Type', ':=', 'VLAN')";
                $query3 = "INSERT INTO $table2 VALUES (NULL, :username, 'Tunnel-Medium-Type', ':=', '6')";
                $query4 = "INSERT INTO $table2 VALUES (NULL, :username, 'Tunnel-Private-Group-ID', ':=', :vlan)";

                // Prepare the SQL statements.
                $statement = $pdo->prepare($query);
                $statement2 = $pdo->prepare($query2);
                $statement3 = $pdo->prepare($query3);
                $statement4 = $pdo->prepare($query4);

                // Bind the values from $_POST and other variables to the placeholders in the prepared statements.
                $statement->bindParam(':username', $_POST['username']);
                $statement->bindParam(':password', $_POST['password']);
                $statement2->bindParam(':username', $_POST['username']);
                $statement3->bindParam(':username', $_POST['username']);
                $statement4->bindParam(':username', $_POST['username']);
                $statement4->bindParam(':vlan', $vlan); // Assuming $vlan is a variable containing the VLAN value.

                // Execute the prepared statements.
                if ($statement->execute() && $statement2->execute() && $statement3->execute() && $statement4->execute()) {
                    // Queries executed successfully.
                    header("Location: ./user2.php?id=$id&vlan=$vlan");
                    exit;
                } else {
                    echo "Error inserting row." . $pdo->errorInfo()[2];// Error message
                }
            }
            else{
                $error =  "Vul alle velden in";// Error message
            }
        }
    }else{
        header("Location: ../index.php");// Redirect to login page if there is no session
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
    <?php echo generateImageDiv(); ?>
        <form method='post' class='createForms'>
            <label class='labelPost'><?php echo $error ?></label> <!-- Error message -->
            <label class='inputPost'>Gebruikersnaam</label>
            <input class='inputPost2' type="text" name="username" value=<?php echo $username ?>>
            <br><br>
        
            <label class='inputPost'>Wachtwoord</label>
            <input class='inputPost2' type="text" name="password"  value=<?php echo $value ?>>
            <br><br>

            <input class='buttonOutsideTable' type="submit" name='Submit' value="Versturen">
            <button class='buttonOutsideTable'><a href="./user2.php?id=<?php echo $id ?>&vlan=<?php echo $vlan ?>">Terug</a></button>
        </form>
    
</body>
</html>
