<?php
include "../include/function.php";
checkSession();

//This page is made to set blocked ports on the switches so they cannot be used
//Or the give business access to certain ports



if ($_SESSION['level'] == 3 || $_GET['vlan'] != null) {
    echo (htmlHeader());

    //set some vars
    $table = "macFilterSwitch";
    $table2 = 'blockedPorts';
    $pdo = connectToDB('users');
    $recordsEther = [];
    $switches = []; // An array to store switch names
    $updateVlan = 0;

    //get connection info from switches
    $query = "SELECT ip, username, password, name FROM $table WHERE router = 0";
    $statement = $pdo->query($query);

    //select all the blocked ports
    $query2 = "SELECT switchName, portName FROM $table2 WHERE id != 1";
    $statement2 = $pdo->query($query2);
    if ($statement && $statement2) {
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $table2Data = $statement2->fetchAll(PDO::FETCH_ASSOC);

        //connection info switches
        foreach ($rows as $row) {
            $ip = $row['ip'];
            $username = $row['username'];
            $password = $row['password'];
            $name = $row['name'];

            $connection = connectToSSH($ip, $username, $password);
            if ($_GET['vlan'] == null) {//check if the vlan is set
                $cmd = '/interface bridge port print brief';//get all the ports that are on the switches
            }else{//if the vlan is set
                $query = "SELECT defaultVlan FROM $table2 WHERE id = 1";
                $statement = $pdo->query($query);
                if ($statement){
                    $row = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $updateVlan = intval($row[0]['defaultVlan']);
                    $cmd = '/interface bridge port print brief where pvid=' . $updateVlan  . '';//get all the blocked ports from the switches
                }
                
            }
            $stream = ssh2_exec($connection, $cmd, NULL, NULL, 90, 25, SSH2_TERM_UNIT_CHARS);//execute the command
            stream_set_blocking($stream, true);
            $resultStream = stream_get_contents($stream);//get the result
            $array = explode("\n", $resultStream);//create an array trough explode
            foreach ($array as $key) {//loop trough the array
                $matches = [];
                if (preg_match('/\bether\w*\b/', $key, $matches)) {//check where the word 'ether' is and get everything beside it untill the next ' '
                    $etherNumber = isset($matches[0]) ? $matches[0] : '';
                    if (!empty($etherNumber)) {//check if the etherNumber is not empty
                        $recordsEther[] = [//add the data to the array
                            'nameSwitch' => $name,
                            'etherNumber' => $etherNumber
                        ];
                    }
                }
            }

            // Collect unique switch names
            if (!in_array($name, $switches)) {
                $switches[] = $name;
            }

            if (!empty($table2Data)) {//check if the table is not empty
                $filteredRecordsEther = [];
                foreach ($recordsEther as $record) {//loop trough the records
                    $match = false;
                    foreach ($table2Data as $table2Row) {//loop trough the table2Data
                        if ($table2Row['switchName'] === $record['nameSwitch'] && $table2Row['portName'] === $record['etherNumber']) {//check if the switchname and portname are the same
                            $match = true;
                            break;
                        }
                    }
                    if (!$match) {
                        $filteredRecordsEther[] = $record;
                    }
                }

                $recordsEther = $filteredRecordsEther;
            }
        }
        sort($switches);//sort the switches
        echo (generateImageDiv() . "
        <div class='displayFlex'>
        <form method='post' style='width: 100%;'>
        <select class='formDropdown2' id='switchDropdown' name='selectedSwitch'>
        ");
        foreach ($switches as $switch) {//loop trough the switches in a dropdown
            $selected = ($_POST['selectedSwitch'] === $switch) ? 'selected' : '';
            echo "<option value='$switch' $selected>$switch</option>";
        }
        echo ("
            </select>
            <input class='buttonOutsideTable' type='submit' value='Show Cables'><br/>
            </form>
            </div>
        ");


        // Check if a switch is selected
        if (isset($_POST['selectedSwitch'])) {//check if the switch is selected
            $selectedSwitch = $_POST['selectedSwitch'];
            echo ("
                
            <div class='tableContainer'>
                <form method='post'> 
                <table class='table'>
                <tr><th colspan='2' class='tTitle'>Selected Switch: $selectedSwitch</th></tr>
                    <tr>
                        <th>Ethernet Cables</th>
                        <th>Select</th>
                    </tr>");
            foreach ($recordsEther as $record) {//get all ether ports from the user-selected switch
                if ($record['nameSwitch'] === $selectedSwitch) {
                    echo ("
                        <tr>
                            <td>{$record['etherNumber']}</td>
                            <td>
                                <input type='hidden' name='selectedSwitch' value='$selectedSwitch'>
                                <input class='inputPost' type='checkbox' name='selectedEthernetPorts[]' value='{$record['etherNumber']}'>
                            </td>
                        </tr>
                        ");
                }
            }
            echo ("</table>
            
            <div class='displayFlex'>
                <input class='buttonOutsideTable' type='submit' name='submit' value='Submit'>");
                if ($_GET['vlan'] == null) {
                    echo("<button class='buttonOutsideTable'><a href='./blockedPorts.php'>Terug</button>");
                }else{
                    echo("<button class='buttonOutsideTable'><a href='./user2.php?id=".$_GET['id']."&vlan=".$_GET['vlan']."'>Terug</button>");
                }
                
            echo("
            </div>
            </form>
            </div>
            ");
        }else{echo ("
            <div class='displayFlex'>");
            // Check if the "Back" button should redirect to the "user2" page or the "blockedPorts" page
            if ($_GET['vlan'] == null) {
                echo (" <button class='buttonOutsideTable'><a href='./blockedPorts.php'>Terug</button>");
            }else{
                echo("<button class='buttonOutsideTable'><a href='./user2.php?id=".$_GET['id']."&vlan=".$_GET['vlan']."'>Terug</button>");
            }
            echo("</div");
        }
        // Check if the "Submit" button is clicked
        if (isset($_POST['submit'])) {
            if ($_POST['submit'] === 'Submit') {
                if (isset($_POST['selectedEthernetPorts'])) {
                    //creation of blocked ports
                    if ($_GET['vlan'] == null) {
                        $selectedSwitchName = $_POST['selectedSwitch'];
                        $selectedEthernetPorts = $_POST['selectedEthernetPorts'];
            
                        // Loop through the selected Ethernet ports and insert them into the "blockedPorts" table
                        foreach ($selectedEthernetPorts as $port) {
                            $query = "INSERT INTO $table2 (switchName, portName, defaultVlan) VALUES (:switchName, :portName, :defaultVlan)";
                            $statement = $pdo->prepare($query);
                            $statement->bindParam(':switchName', $selectedSwitchName);
                            $statement->bindParam(':portName', $port);
                            $defaultVlan = null;
                            $statement->bindParam(':defaultVlan', $defaultVlan, PDO::PARAM_NULL);

                            $result = $statement->execute();
            
                            // You can handle errors or success here as needed
                            if (!$result) {
                                echo "Error inserting record: " . $pdo->errorInfo()[2];
                            }
                        }
                        header("Location: ./redirect.php?page=blockedPorts");//redirect to the blockedPorts page
                        exit();
                    }else {//giving ports to businesses
                        $selectedSwitchName = $_POST['selectedSwitch'];
                        $selectedEthernetPorts = $_POST['selectedEthernetPorts'];
                        $defaultVlan = $_GET['vlan'];
            
                        // Loop through the selected Ethernet ports and insert them into the "blockedPorts" table
                        $query = "SELECT ip, username, password FROM $table WHERE name = '$selectedSwitchName'";
                        $statement = $pdo->query($query);
                        if ($statement){
                            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($rows as $row) {
                                $ip = $row['ip'];
                                $username = $row['username'];
                                $password = $row['password'];
                            }
                            $connection = connectToSSH($ip, $username, $password);
                            if (!$connection) {
                                die("Connection failed SSH");
                            }
                            foreach($selectedEthernetPorts as $port){
                                //change the vlan of the ports that are selected
                                $cmd = '/interface bridge port set pvid=' . $defaultVlan . ' [find where interface="' . $port . '"]';
                                $stream = ssh2_exec($connection, $cmd);
                                stream_set_blocking($stream, true);
                                
                            }//redirect to the user2 page
                            echo("<script type='text/javascript'>
                            window.location.href = './redirect.php?page=user2&id=$_GET[id]&vlan=$_GET[vlan]';
                            </script>");
                            
                        }
                        
                    }
                }
            }
        }
        
    }
}

?>
