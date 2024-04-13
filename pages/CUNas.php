<?php
include "../include/function.php";
checkSession();

if ($_SESSION['level'] == 3) {
    $id = $_GET['id'];//get the id from the url
    $table = "nas"; 
    $pdo = connectToDB('radius'); // Establish the database connection
        if (!$pdo) {
            die("Connection failed");//Check if the connection is made
        }
    if(!empty($_POST) && $_GET['id'] == null){
        $boolean = true;
        if(empty($_POST['nasname']) || $_POST['ports'] < 0){// Check if the required fields are filled in
            $boolean = false;
        }

        if ($boolean){


            // Prepare the query
            $query = "INSERT INTO $table (nasname, shortname, type, ports, secret, server, community, description) VALUES (:nasname, :shortname, :type, :ports, :secret, :server, :community, :description)";

            // Prepare the SQL statement.
            $statement = $pdo->prepare($query);

            // Bind the values from $_POST to the placeholders in the prepared statement.
            $statement->bindParam(':nasname', $_POST['nasname']);
            $statement->bindParam(':shortname', $_POST['shortname']);
            $statement->bindParam(':type', $_POST['type']);
            $statement->bindParam(':ports', $_POST['ports']);
            $statement->bindParam(':secret', $_POST['secret']);
            $statement->bindParam(':server', $_POST['server']);
            $statement->bindParam(':community', $_POST['community']);
            $statement->bindParam(':description', $_POST['description']);

            // Execute the prepared statement.
            if ($statement->execute()) {
                // Query executed successfully.
                // You can add your success handling code here.
                header("Location: ./nas.php");
                exit; 
            }
            else {
                $error =  "Vul Nasname en Ports in.";// Error message
            }
        }
        else{
            $error =  "Vul Nasname en Ports in.";// Error message
        }
    }else if ($_GET['id'] != null){
        //make query
        $query = "SELECT * FROM $table WHERE id = $id";
        // Execute the query
        $statement = $pdo->query($query);
        if ($statement){//check if the query is executed
            $row = $statement->fetchAll(PDO::FETCH_ASSOC);
            //get the values from the row
            $nasname = $row[0]['nasname'];
            $shortname = $row[0]['shortname'];
            $type = $row[0]['type'];
            $ports = $row[0]['ports'];
            $secret = $row[0]['secret'];
            $server = $row[0]['server'];
            $community = $row[0]['community'];
            $description = $row[0]['description'];
        } 
        else {
            $error = "velden a.u.b. invullen";
            exit;
        }

        if (isset($_POST['Submit'])) {//check if the submit button is pressed
            try {
                // make a query to update the row
                $queryUpdate = "UPDATE $table SET id=:id, nasname=:nasname, shortname=:shortname, type=:type, ports=:ports, secret=:secret, server=:server, community=:community, description=:description WHERE id=:id";
                // Prepare the SQL statement.
                $update = $pdo->prepare($queryUpdate);

                // Bind the values from $_POST and other variables to the placeholders in the prepared statement.
                $update->bindParam(':id', $id, PDO::PARAM_INT); // Assuming $id is an integer.
                $update->bindParam(':nasname', $_POST['nasname']);
                $update->bindParam(':shortname', $_POST['shortname']);
                $update->bindParam(':type', $_POST['type']);
                $update->bindParam(':ports', $_POST['ports']);
                $update->bindParam(':secret', $_POST['secret']);
                $update->bindParam(':server', $_POST['server']);
                $update->bindParam(':community', $_POST['community']);
                $update->bindParam(':description', $_POST['description']);

                // Execute the prepared statement.
                if ($update->execute()) {
                    // Query executed successfully.
                    header("Location: ./nas.php");
                    exit; 
                }
            } catch (PDOException $e) {
                echo "SQL Error: " . $e->getMessage();//check if the query is executed
            }
        }
    }
}
else{
    header("Location: ../index.php");//Check if the user is logged in
}
?>
<!-- html input form -->
<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
<?php echo generateImageDiv(); ?>
<form  method="POST" class='createForms'>
    <label class='labelPost'><?php echo $error; ?></label> <!-- Error message -->
    <label class='labelPost'>nasname</label>
    <input class='inputPost2' type="text" name="nasname"  value=<?php echo $nasname ?>>
    <br>

    <label class='labelPost'>shortname</label>
    <input class='inputPost2' type="text" name="shortname" value=<?php echo $shortname ?>>
    <br>

    <label class='labelPost'>type</label>
    <input class='inputPost2' type="text" name="type" value=<?php echo $type ?>>
    <br>

    <label class='labelPost'>ports</label>
    <input class='inputPost2' type="number" name="ports" value=<?php echo $ports ?>>
    <br>

    <label class='labelPost'>secret</label>
    <input class='inputPost2' type="text" name="secret" value=<?php echo $secret ?>>
    <br>

    <label class='labelPost'>server</label>
    <input class='inputPost2' type="text" name="server" value=<?php echo $server ?>>
    <br>

    <label class='labelPost'>community</label>
    <input class='inputPost2' type="text" name="community" value=<?php echo $community ?>>
    <br>

    <label class='labelPost'>description</label>
    <input class='inputPost2' type="text" name="description" value=<?php echo $description ?>>
    <br>

    <input class='buttonOutsideTable' type="submit" name="Submit" value="Versturen">
    <button class='buttonOutsideTable'><a href="./nas.php">Terug</a></button>
</form>
</body>
</html>
