<?php
// Include necessary files and check session
include "../include/function.php";
checkSession();


//this page is made for creating and updating macssid


$arraySwitchName = [];
$bool = false;
$boolCheck = true;
if (isset($_GET['bool'])) {
    $bool = true;
}

if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {
    $hostName =  isset($_GET['hostName']) ? str_replace(' ', '_', $_GET['hostName']) : '';//Check if a hostname is provided via GET (for update)
    $macAddress = $_GET['macAddress'];
    $name = $_GET['name'];
    $pdo = connectToDB('users');// Establish the database connection

    if (!$pdo) {
        die("Connection failed");
    }

    $table = "macFilterSwitch";
    $querySwitch = "SELECT name FROM $table WHERE `switch` = 1";
    $statementSwitch = $pdo->query($querySwitch);
    if($statementSwitch){
        $rowsSwitch = $statementSwitch->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rowsSwitch as $rowSwitch) {
            $arraySwitchName[] = $rowSwitch['name'];//get all the switch names
        }
    }

    if (!empty($_POST)) {
        $name = $_POST['dropdownMenu'];
        $query = "SELECT * FROM $table WHERE `name` ='$name'";// Make query to retrieve switch information
        $statement = $pdo->query($query);
        if($statement){
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {// Get the values from the row
                $ip = $row['ip'];
                $username = $row['username'];
                $password = $row['password'];
                $id = $row['id'];
            }
        }
        $connect = connectToSSH($ip, $username, $password);// Establish the SSH connection
        //get the values from the form
        $name = $_POST['dropdownMenu'];
        $comment = $_POST['comment'];
        $vlan = $_POST['vlan'];
        $mac = $_POST['mac'];
        $comment = preg_replace('/\s+/', '_', $comment);
        
        if ($comment == '') {// Check if the required fields are filled in
            $error = "Vul een comment in.";
            $boolCheck = false;
        }
        if (!isValidMacAddress($mac)) {// Check if the mac address is valid and if it is filled in
            $error = "Vul een correct Macaddress in.";
            $boolCheck = false;
        }

        if ($boolCheck){// Check if the required fields are filled in
            if ($bool) {
                //Because of certain reasons with not seeing an mac address after an update command we first delete the old record and then add the new one
                $cmdDelete = '/interface ethernet switch rule remove numbers=[find where src-mac-address="' . $_GET['macAddress'] . '/FF:FF:FF:FF:FF:FF"]';
                $streamDelete = ssh2_exec($connect, $cmdDelete);
                stream_set_blocking($streamDelete, true);
                // Add the updated record
                $cmdAdd = '/interface ethernet switch rule add comment=' . $comment . ' new-vlan-id=' . $vlan . ' ports=ether2,ether4 src-mac-address=' . $mac . '/FF:FF:FF:FF:FF:FF switch=switch1';
                $streamAdd = ssh2_exec($connect, $cmdAdd);
                stream_set_blocking($streamAdd, true);
            } else {
                // It's a create operation, directly add the new record
                $cmd = '/interface ethernet switch rule add comment=' . $comment . ' new-vlan-id=' . $vlan . ' ports=ether2,ether4 src-mac-address=' . $mac . '/FF:FF:FF:FF:FF:FF switch=switch1';
                $stream = ssh2_exec($connect, $cmd);
                stream_set_blocking($stream, true);
            }
            //Based on the url we redirect to the right page
            if (isset($_GET['vlan'])){
                header ("Location: ./user2.php?id=".$id."&vlan=".$_GET['vlan']);
            }else{
                header ("Location: ./macssid.php?name=".$_GET['name']);
            }
        }else{
            $error = "Vul alle velden in.";
            header ("Location: ./CUMacssid.php?name=".$_GET['name']."&hostName=".$comment."&macAddress=".$mac."&bool=".$bool."&error=".$error);
        }
    }
} else {
    header("Location: ../index.php");
}
?>
<!-- html form input -->
<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
    <?php echo generateImageDiv(); ?>
    <div>
        <form method='post' class='createForms'>
            <label class='labelPost'><?php echo $_GET['error']; ?></label> <!-- Error message -->
            <label class='labelPost'>Comment</label>
            <input class='inputPost2' type="text"  name="comment" value=<?php echo $hostName ?>>
            <br>

            <label class='labelPost'>Mac</label>
            <input class='inputPost2' type="text" name="mac" value=<?php echo $macAddress ?>>
            <br>

            <label class='labelPost'>Vlan</label>
            <select class='dropdownMenu2' name="vlan">
            <?php
            //Based if its for a spefic businesses or to add a new record to the router we generate the dropdown menu with the right vlans
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
                foreach ($rows as $row) {
                    $vlanStart = $row['vlanStart'];
                    $vlanEnd = $row['vlanEnd'];
                    $selectedVlanId = isset($_GET['vlan']) ? $_GET['vlan'] : '';
                    if ($selectedVlanId != '') {
                        echo "<option value='$selectedVlanId' selected>$selectedVlanId</option>";
                        break;
                    }else{
                        for ($i = $vlanStart; $i <= $vlanEnd; $i++) {
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
                            if ($vlanExists) {
                                echo "<option value='$i'>$username ($i)</option>";
                            }
                        }
                    }
                }
            }
            ?>
            </select>
            <br>

            <label class='labelPost'>Switch</label>
            <select class='dropdownMenu2' name='dropdownMenu'>
            <?php 
            foreach($arraySwitchName as $switchName){
                echo ("
                <option>". $switchName ."</option>
                ");
    
            } 
            ?>
            </select>
            <br>
            <input class='buttonOutsideTable' type="submit" value="Versturen">
            <?php 
            //Based on the url we redirect to the right page
            if (isset($_GET['vlan'])) {
                echo("<button class='buttonOutsideTable'><a href='./user2.php?id=".$_GET['id']."&vlan=".$_GET['vlan']."'>Terug</button></a>");
                
            } else {
                echo("<button class='buttonOutsideTable'><a href='./macssid.php?name=".$_GET['name']."'>Terug</button></a>");
            }

            ?>
        </form>
    </div>
</body>
</html>
