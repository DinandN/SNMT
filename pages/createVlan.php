<?php
include "../include/function.php";
checkSession();


if ($_SESSION['level'] == 3) {
    if(!empty($_POST)){// Check if the required fields are filled in
        if ($_POST['vlanStart'] < $_POST['vlanEnd']) {// Check if the vlan start is smaller than the vlan end
            $pdo = connectToDB('users'); // Establish the database connection

            if (!$pdo) {
                die("Connection failed");// Check if the connection is established
            }
            $table = "vlan";
            $vlanCheck = false;
            // Prepare the query

            $pdo = connectToDB('users');
            $querySelect = "SELECT * FROM $table";
            $statement = $pdo->query($querySelect);
            if ($statement){
                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    if ($_POST['vlanStart'] >= $row['vlanStart'] && $_POST['vlanStart'] <= $row['vlanEnd'] || $_POST['vlanEnd'] >= $row['vlanStart'] && $_POST['vlanEnd'] <= $row['vlanEnd']) {
                        // Check if the vlan is between another vlan in the database 
                        //cannot be row1 start 10 end 20 and row2 start 15 end 25
                        $error = "De gekozen VLAN zit tussen een andere VLAN";
                        $vlanCheck = false;
                    }else{
                        $vlanCheck = true;
                    }
                    echo $vlanCheck;
                }if($vlanCheck){// Insert the vlan in the database
                    $query = "INSERT INTO $table (vlanStart, vlanEnd) VALUES (:vlanStart, :vlanEnd)";
                    $statement = $pdo->prepare($query);
                    $statement->bindParam(':vlanStart', $_POST['vlanStart']);
                    $statement->bindParam(':vlanEnd', $_POST['vlanEnd']);
                    $statement->execute();
                    if ($statement){
                        header("Location: ./vlanManager.php");// Redirect to the nas page
                    } 
                    else {
                        die("Query execution failed: " . $pdo->errorInfo()[2]);// Check if the query is executed
                    }
                }else{
                    echo "Error inserting row 2." . $pdo->errorInfo()[2];// Error message
                }
            }
        }else{
            $error = "Vlan Start moet groter zijn dan Vlan Einde";
        }
    }
}else{
    header("Location: ../index.php");// Redirect to login page if there is no session
}

?>
<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
<?php echo generateImageDiv(); ?>
        <form method='post' class='createForms'>
            <label class='labelPost'><?php echo $error ?></label> <!-- Error message -->
            <label class='labelPost'>Vlan Start: </label>
            <input class='inputPost2' type="number"  name="vlanStart">
            <br><br>

            <label class='labelPost'>Vlan Einde: </label>
            <input class='inputPost2' type="number" name="vlanEnd">
            <br><br>
        
            <input class='buttonOutsideTable' type="submit" value="Versturen">
            <button class='buttonOutsideTable'><a href="./vlanManager.php">Terug</a></button>
        </form>
</body>
</html>
