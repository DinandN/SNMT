<?php
include "../include/function.php";
checkSession();


//Page is made for creating and updating businesses

if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {
    $table = "businesses";
    $pdo = connectToDB('users'); // Establish the database connection
    if (!$pdo) {
        die("Connection failed"); // Check if the connection is established
    }
    $vlans = array();
    $query = "SELECT vlan FROM $table";// Make query to retrieve VLAN information
    $statement = $pdo->query($query);
    if ($statement){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $row){
            array_push($vlans, $row['vlan']);// Push all vlans in an array
        }
    }
    $error = ""; // Initialize the error message

    if (isset($_POST['Submit'])){
        //Get the values from the form
        $location = $_POST['location'];
        $unit = $_POST['unit'];
        $phoneNumber = $_POST['phoneNumber'];
        //check if the values are filled in 
        if (empty($location)) {
            $location = null;
        }
        if (empty($unit)) {
            $unit = null;
        }
        if (empty($phoneNumber)) {
            $phoneNumber = null;
        }

        // Check if the required fields are filled in
        if (empty($_POST['companyName']) || empty($_POST['vlan'])) {
            $error = "Vul Bedrijf naam in";

        } else{
            if ($_GET['id'] == null) {// Check if the id is null
                $query = "INSERT INTO $table (id, name, location, unit, phoneNumber, vlan) VALUES (NULL, :companyName, :location, :unit, :phoneNumber, :vlan)";
                // Prepare the SQL statement.
                $statement = $pdo->prepare($query);
            
                // Bind the values from $_POST to the placeholders in the prepared statement.
                $statement->bindParam(':companyName', $_POST['companyName']);
                $statement->bindParam(':location', $location); 
                $statement->bindParam(':unit', $unit);
                $statement->bindParam(':phoneNumber', $phoneNumber); 
                $statement->bindParam(':vlan', $_POST['vlan']);
                
                if ($statement->execute()) { // Check if the query is executed properly
                    header("Location: ./businesses.php");
                    exit; 
                }
            }else if($_GET['id'] != null){// Check if the id is not null
                if ($_POST['vlan'] == $_GET['vlan'] || !in_array($_POST['vlan'], $vlans)){
                    // Use prepared statement to prevent SQL injection
                    $query = "UPDATE $table SET name = :companyName, location = :location, unit = :unit, phoneNumber = :phoneNumber, vlan = :vlan WHERE id = :id;";
                    $statement = $pdo->prepare($query);
            
                    // Bind parameters
                    $statement->bindParam(':companyName', $_POST['companyName']);
                    $statement->bindParam(':location', $location);
                    $statement->bindParam(':unit', $unit);
                    $statement->bindParam(':phoneNumber', $phoneNumber);
                    $statement->bindParam(':vlan', $_POST['vlan']);
                    $statement->bindParam(':id', $id);
            
                    // Execute the prepared statement
                    if ($statement->execute()) { 
                        header("Location: ./businesses.php");
                        exit; 
                    }
                }else{
                    $error = "Vlan bestaat al";
                }
            }
        }
        
        
    }else{
        $query = "SELECT * FROM $table WHERE id = '$_GET[id]';";
        $statement = $pdo->query($query);
        if ($statement){
            $business = $statement->fetch(PDO::FETCH_ASSOC);
        }
    }
}else{
    header("Location: ../index.php"); // Redirect to login page if there is no session
}
?>

<?php echo htmlHeader(); ?>
    <?php echo generateImageDiv(); ?>
    <form method='POST' class='createForms'>
        <label class='labelPost'><?php echo $error; ?></label> <!-- Error message -->
        <label class='labelPost'>Bedrijf naam</label>
        <input class='inputPost2' type="text" name="companyName" value="<?php echo $business['name']; ?>">
        <br>

        <label class='labelPost'>Locatie</label>
        <input class='inputPost2' type="text" name="location" value="<?php echo $business['location']; ?>">
        <br>

        <label class='labelPost'>Unit</label>
        <input class='inputPost2' type="text" name="unit" value="<?php echo $business['unit']; ?>">
        <br>

        <label class='labelPost'>Telefoon Nummer</label>
        <input class='inputPost2' type="number" name="phoneNumber" value="<?php echo $business['phoneNumber']; ?>">
        <br>

        <label class='labelPost'>Vlan</label>
        <select class='dropdownMenu2' name="vlan">
        <?php
        //dropdown menu for selecting the avaible (open) vlans 
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
            foreach ($rows as $row) {// Generate dropdown options
                $vlanStart = $row['vlanStart'];
                $vlanEnd = $row['vlanEnd'];
                $selectedVlanId = isset($_GET['vlan']) ? $_GET['vlan'] : '';// Get the selected vlan from the url if its set
                if ($selectedVlanId != '') {
                    echo "<option value='$selectedVlanId' selected>$selectedVlanId</option>";// Set the selected vlan
                }

                for ($i = $vlanStart; $i <= $vlanEnd; $i++) {// Loop through all the vlans
                    $vlanExists = false;
                    $username = '';

                    // Check if the VLAN exists in radreply table
                    foreach ($rowsBusinesses as $rowsBusiness) {
                        if ($rowsBusiness['vlan'] == $i) {
                            $vlanExists = true;
                            $username = $rowsBusiness['name'];
                            break;
                        }
                    }

                    // Generate dropdown options only if VLAN does not exist
                    if (!$vlanExists) {
                        echo "<option value='$i'>$i</option>";
                    }
                }
            }
        }
        ?>
        </select>
        <br>
        <?php if (empty($_GET['id'])) {
    echo ("<input class='buttonOutsideTable' name='Submit' type='submit' value='Toevoegen'>");
} else {
    echo ("<input class='buttonOutsideTable' name='Submit' type='submit' value='Update'>");
}
        
        ?>
        
        <button class='buttonOutsideTable'><a href="./businesses.php">Terug</a></button>
        </form> 

