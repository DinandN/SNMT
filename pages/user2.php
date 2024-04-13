<?php
//start session
include "../include/function.php";
    checkSession();
if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {

    //includes
    $getId = $_GET['id'];
    $getVlan = $_GET['vlan'];

    $table  = "businesses";
    $table2 = 'radreply';
    $table3 = 'radcheck';
    $table4 = 'radacct';
    $table5 = "macFilterSwitch";
    $table6 = "blockedPorts";
    $tableAdmin = "admins";
    $tableColleague = "colleague";
    $tableBlocked = "blocked";

    $arrayVal = 0;
    $onlineUser = 0;
    $boolOnline = false;
    $onlineSince = "";
    $lastUser = "";

    $arrayAdmins = array();

    $rows2 = [];
    $allRecordsSwitch = [];
    $allInterfaceStatus = [];

    $pdo = connectToDB('users');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }
    $query = "SELECT * FROM $table  WHERE id = :id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':id', $getId, PDO::PARAM_INT);
    $statement->execute();

    echo( htmlHeader() );
    if ($statement){
        $row = $statement->fetch(PDO::FETCH_ASSOC);
            echo (
            generateImageDiv() ."
            <div class='tableContainer'>
                    <table class='table'>
                    <tr>
                        <th colspan='5' class='tTitle'>Bedrijfs Gegevens</th>
                    </tr>
                    <tr class='tableHeader'>
                        <th>Bedrijf's Naam</th>
                        <th>Locatie</th>
                        <th>Unit</th>
                        <th> Telefoon Nummer</th>
                        <th>Vlan</th>
                    </tr>
                    <tr>
                        <td> ". $row['name'] ."</td>
                        <td> ". $row['location'] ."</td>
                        <td> ". $row['unit'] ."</td>
                        <td><a Href=tel:$row[phoneNumber]> ". $row['phoneNumber'] ."</a></td>
                        <td> ". $row['vlan'] ."</td>
                    </tr>
                </table>
            </div>
            ");

            $pdo = connectToDB('radius');
            if (!$pdo) {
                die("Connection failed");//Check if the connection is made
            }
            $query = "SELECT username FROM $table2 WHERE `id` % 3 = 0 AND `value` = $getVlan ORDER BY username ASC"; 
            $statement = $pdo->prepare($query);
            $statement->bindParam(':vlan', $getVlan, PDO::PARAM_INT);
            $statement->execute();
        
            $usernames = [];
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $usernames[] = $row['username'];
            }
        
            if (!empty($usernames)) {
                $usernameList = "'" . implode("','", $usernames) . "'";
                $query2 = "SELECT * FROM $table3 WHERE username IN ($usernameList) ORDER BY username ASC";
                $statement2 = $pdo->query($query2);
            }
        
            $query3 = "SELECT t1.username, t1.acctstoptime
            FROM $table4 AS t1
            INNER JOIN (
                SELECT username, MAX(acctstoptime) AS max_time
                FROM $table4
                GROUP BY username
            ) AS t2 ON t1.username = t2.username AND t1.acctstoptime = t2.max_time
            ORDER BY t1.username ASC";
            $statement3 = $pdo->query($query3);
        
            $query4 = "SELECT acctstarttime,acctstoptime, username FROM $table4 WHERE acctstoptime IS NULL ORDER BY username ASC";
            $statement4 = $pdo->query($query4);

            echo ("
                        <div class='tableContainer'>
                            <table class='table'>
                            <tr>
                                <th colspan='5' class='tTitle'>Radius</th>
                            </tr>
                            <tr class='tableHeader'>
                                <th>Gebruikersnaam</th>
                                <th>Wachtwoord</th>
                                <th>Laatst Online</th>
                                <th>Verwijder Gebruiker</th>
                                <th>Update Gegevens</th>
                            </tr>
                        ");
            if($statement2 && $statement3 && $statement4){
                $rows2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
                $rows3 = $statement3->fetchAll(PDO::FETCH_ASSOC);
                $rows4 = $statement4->fetchAll(PDO::FETCH_ASSOC);
                
                $pdo = connectToDB('users');
                $query = "SELECT * FROM $tableAdmin";
                $statement = $pdo->query($query);
                if ($statement){
                    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        $arrayAdmins[] = $row['username'];
                    }
                }


                $onlineUsers = array();
                foreach ($rows4 as $onlineUser) {
                        $onlineUsers[$onlineUser['username']] = $onlineUser['acctstarttime'];
                    }

                    // Create table
                    
            
                    // Output the column information
                    $idUser = 0;
                    foreach ($rows2 as $row) {
                        
                        
                        echo "<tr";
                        $idUser = $row['id'];
                        if (isset($onlineUsers[$row['username']])) {
                            echo " class='onlineUser'";
                            $onlineSince = $onlineUsers[$row['username']];
                            $boolOnline = true;
                        } else {
                            $boolOnline = false;
                        }
            
                        echo ">";
                        
                        echo ("<td><a href='./userData.php?name=" . $row['username'] . "'>" . $row['username']. "</a></td>");
                        if ($_SESSION['level'] == 2 && in_array($row['username'], $arrayAdmins)){
                            echo("<td>********</td>");
                        }else{
                            echo("<td>" . $row['value'] . "</td>");
                        }
                        $lastOnline = NULL;
                        foreach ($rows3 as $row3) {
                            if ($row3['username'] == $row['username']) {
                                $lastOnline = $row3['acctstoptime'];
                                break;
                            }
                        }
            
                        echo "<td>";
                        if ($boolOnline) {
                            echo "Online: $onlineSince";
                        } else {
                            echo ($lastOnline !== NULL) ? $lastOnline : "Geen data";
                        }
                        echo "</td>";
                        
                        echo ("
                        <td>");
                        if ($_SESSION['level'] == 3 || !in_array($row['username'], $arrayAdmins)){
                            echo("
                            <form method='post' class='formDeleteButtons'>
                                <input type='hidden' name='userId' value='" . $row['id'] . "'>
                                <input type='hidden' name='usernamePost' value='" . $row['username'] . "'>
                                <button type='submit' name='deleteButtonUser'>Verwijder</button>
                            ");
                        }
                            //button to delete user from userTable
                            if (isset($_POST['deleteButtonUser']) && $_POST['userId'] == $row['id'] && $_POST['usernamePost'] == $row['username']) {
                                $id = $row['id'];
                                $username = $row['username'];

                                $pdo = connectToDB('radius');
                            
                                // Using prepared statements to prevent SQL injection
                                $queryDelete = "DELETE FROM $table WHERE id = ?";
                                $delete = $pdo->prepare($queryDelete);
                                $delete->execute([$id]);
                            
                                $idTable2 = $id * 3;
                                $queryDelete2 = "DELETE FROM $table2 WHERE id = ?";
                                $delete2 = $pdo->prepare($queryDelete2);
                                $delete2->execute([$idTable2]);
                            
                                $queryDelete3 = "DELETE FROM $table2 WHERE id = ?";
                                $delete3 = $pdo->prepare($queryDelete3);
                                $delete3->execute([$idTable2 - 1]);
                            
                                $queryDelete4 = "DELETE FROM $table2 WHERE id = ?";
                                $delete4 = $pdo->prepare($queryDelete4);
                                $delete4->execute([$idTable2 - 2]);
                            
                                $queryDelete5 = "DELETE FROM $table3 WHERE username = ?";
                                $delete5 = $pdo->prepare($queryDelete5);
                                $delete5->execute([$username]);

                                $pdo = connectToDB('users');

                                $queryDelete6 = "DELETE FROM $tableAdmin WHERE username = ?";
                                $stmt6 = $pdo->prepare($queryDelete6);
                                $stmt6->execute([$username]);

                                // DELETE from $tableUsers2
                                $queryDelete7 = "DELETE FROM $tableColleague WHERE username = ?";
                                $stmt7 = $pdo->prepare($queryDelete7);
                                $stmt7->execute([$username]);

                                // DELETE from $tableUsers3
                                $queryDelete8 = "DELETE FROM $tableBlocked WHERE username = ?";
                                $stmt8 = $pdo->prepare($queryDelete8);
                                $stmt8->execute([$username]);
                                
                                echo("<script type='text/javascript'>
                                    window.location.href = './redirect.php?page=user2&id=$_GET[id]&vlan=$getVlan';
                                    </script>");
                               
                            }
                            
                        
                        echo ("
                        </form>
                        </td>
                        <td>");
                        if ($_SESSION['level'] == 3 || !in_array($row['username'], $arrayAdmins)){
                            echo("
                            <button><a href='./CUUser.php?id=$_GET[id]&idUser=" . $row['id'] . "&vlan=$getVlan'>Update</a></button>
                            ");
                        }
                        echo("
                        </td>
                        </tr>
                        ");
            
                        echo "</tr>";
                        
                        $arrayVal++;
                    }
                }
                    echo ("
                    <tr><th colspan='5'> <button class='user2AddButton'><a href='./CUUser.php?id=$_GET[id]&vlan=$getVlan'>Toevoegen</a></button></th></tr>
                        </table>
                        </div>
                    ");

                    
                    //
                    $pdo = connectToDB('users');
                    if (!$pdo) {
                        die("Connection failed");
                    }

                    $query = "SELECT ip, username, password, name FROM $table5 WHERE router = 0";
                    $statement = $pdo->query($query);

                    if ($statement && isset($_POST['showSSH']) || $_SESSION['showSSH']) {
                        $_SESSION['showSSH'] = true;
                        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                        
                    
                        $allRecordsSwitch = [];
                        $allInterfaceStatus = [];

                            foreach ($rows as $row) {
                                $ip = $row['ip'];
                                $username = $row['username'];
                                $password = $row['password'];
                                $switchName = $row['name'];
                        
                                $connection = connectToSSH($ip, $username, $password);
                                if (!$connection) {
                                    continue;
                                }
                                
                                    $cmd = '/interface ethernet switch rule export terse';
                                    $stream = ssh2_exec($connection, $cmd, NULL, NULL, 90, 25, SSH2_TERM_UNIT_CHARS);
                                    stream_set_blocking($stream, true);
                                    $resultStream = stream_get_contents($stream);
                                    $array = explode("\n", $resultStream);
                            
                                    $recordsSwitch = [];
                            
                                    foreach ($array as $key) {
                                        $matches = [];
                                        preg_match('/comment=(.*?)(?=\snew-vlan-id)/', $key, $matches);
                                        $comment = isset($matches[0]) ? $matches[0] : '';
                                        $comment = str_replace('_', ' ', $comment);
                                        $comment = str_replace('comment=', '', $comment);
                            
                                        preg_match('/new-vlan-id=(\d*)/', $key, $matches);
                                        $vlanId = isset($matches[1]) ? $matches[1] : '';
                            
                                        preg_match('/src-mac-address=(..:..:..:..:..:..)/', $key, $matches);
                                        $macAddress = isset($matches[1]) ? $matches[1] : '';
                            
                                        if (!empty($comment) && !empty($vlanId) && !empty($macAddress) && $vlanId == $getVlan) {
                                            $recordsSwitch[] = [
                                                'comment' => $comment,
                                                'vlanId' => $vlanId,
                                                'macAddress' => $macAddress,
                                                'switchName' => $switchName
                                            ];
                                        }
                                    }

                                
                                    $recordsEther = [];
                                    $recordsOnline = [];
                                    $interfaceStatus = [];

                                    $cmd = '/interface bridge port print brief where pvid=' . $getVlan . '';
                                    $stream = ssh2_exec($connection, $cmd, NULL, NULL, 90, 25, SSH2_TERM_UNIT_CHARS);
                                    stream_set_blocking($stream, true);
                                    $resultStream = stream_get_contents($stream);
                                    $array = explode("\n", $resultStream);
                                    foreach ($array as $key) {
                                        $matches = [];
                                        if (preg_match('/\bether\w*\b/', $key, $matches)) {
                                            $etherNumber = isset($matches[0]) ? $matches[0] : '';
                                            if (!empty($etherNumber)) {
                                                $recordsEther[] = [
                                                    'etherNumber' => $etherNumber
                                                ];
                                            }
                                        }
                                    }
                            
                                    $cmd = '/interface print where running';
                                    $stream = ssh2_exec($connection, $cmd, NULL, NULL, 90, 25, SSH2_TERM_UNIT_CHARS);
                                    stream_set_blocking($stream, true);
                                    $resultStream = stream_get_contents($stream);
                                    $array = explode("\n", $resultStream);
                                    foreach ($array as $key) {
                                        $matches = [];
                                        if (preg_match('/\bether\w*\b/', $key, $matches)) {
                                            $etherOnline = isset($matches[0]) ? $matches[0] : '';
                                            if (!empty($etherOnline)) {
                                                $recordsOnline[] = [
                                                    'etherOnline' => $etherOnline
                                                ];
                                            }
                                        }
                                    }
                            
                                    foreach ($recordsEther as $etherRecord) {
                                        $etherNumber = $etherRecord['etherNumber'];
                                        $status = 'offline';
                            
                                        foreach ($recordsOnline as $onlineRecord) {
                                            $etherOnline = $onlineRecord['etherOnline'];
                            
                                            if ($etherNumber === $etherOnline) {
                                                $status = 'online';
                                                break;
                                            }
                                        }
                            
                                        $interfaceStatus[] = [
                                            'etherNumber' => $etherNumber,
                                            'status' => $status
                                            
                                        ];
                                    }
                            
                                    // Store the records in separate arrays indexed by the switch name
                                    $allRecordsSwitch[$switchName] = $recordsSwitch;
                                    $allInterfaceStatus[$switchName] = $interfaceStatus;
                                }
                            

                        $connection = null;
                    
                        // Display the MAC addresses table
                        echo("
                        <div class='tableContainer'>
                            <table class='table'>
                                <tr>
                                    <th colspan='5' class='tTitle'>MAC Filter</th>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <th>Mac</th>
                                    <th>Switch name</th>
                                    <th>Verwijder</th>
                                    <th>Update</th>
                                </tr>
                        ");
                    
                        foreach ($allRecordsSwitch as $switchName => $recordsSwitch) {
                            foreach ($recordsSwitch as $record) {
                                echo ("
                                <tr>
                                    <td>" . $record['comment'] . "</td>
                                    <td>" . $record['macAddress'] . "</td>
                                    <td>" . $switchName . "</td>
                                    <td>
                                        <form method='post' class='formDeleteButtons'>
                                            <input type='hidden' name='macAddressPost' value='". $record['macAddress'] ."'>
                                            <input type='hidden' name='switchName' value='". $switchName ."'>
                                            <button type='submit' name='deleteMacAddres'>Verwijder</button>
                                        </form>
                                    </td>
                                    <td><button><a href='./CUMacssid.php?vlan=" . $_GET['vlan'] . "&id=".$_GET['id']."&bool=".true."&macAddress=".$record['macAddress']."&hostName=".$record['comment']."'>Update</a></button></td>
                                </tr>
                                ");
                            }
                        }

                        if(isset($_POST['deleteMacAddres'])) {
                            $macAddressPost = $_POST['macAddressPost'];
                            $switchName = $_POST['switchName'];

                            $query = "SELECT ip, username, password FROM $table5 WHERE name = '$switchName'";
                            $statement = $pdo->query($query);
                            if ($statement){
                                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($rows as $row) {
                                    $ip = $row['ip'];
                                    $username = $row['username'];
                                    $password = $row['password'];
                                }
                                $connection = connectToSSH($ip, $username, $password);
                                $cmd = '/interface ethernet switch rule remove numbers=[find where src-mac-address="'.$macAddressPost.'/FF:FF:FF:FF:FF:FF"]';
                                $stream = ssh2_exec($connection, $cmd);
                                stream_set_blocking($stream, true);
                            }
                            echo("<script type='text/javascript'>
                                    window.location.href = './redirect.php?page=user2&id=$_GET[id]&vlan=$getVlan';
                                    </script>");
                        }
                
                        
                        echo ("
                        <tr>
                            <th colspan='5'> <button class='user2AddButton'><a href='./CUMacssid.php?id=$_GET[id]&vlan=$getVlan'>Toevoegen</a></button></th>
                        </table>
                        </tr>
                        </div>
                        </table>");

                        echo("
                            <div class='tableContainer'>
                            <table class='table'>
                            <tr>
                            <td colspan='5' class='tTitle'> Switch poort(en): </td>
                            </tr>
                            <tr>
                                <th colspan='2'>Ethernet Name</th>
                                <th colspan='2'>Status</th>
                                <th>Delete</th>
                            </tr>
                        ");

                        // Now, iterate through $allRecordsSwitch and $allInterfaceStatus
                        foreach ($allRecordsSwitch as $switchName => $recordsSwitch) {
                            foreach ($allInterfaceStatus[$switchName] as $record) {
                                
                                echo("<tr><th colspan='5' class='tTitle'>$switchName</th></tr>");
                                break;
                            }
                            

                            foreach ($allInterfaceStatus[$switchName] as $record) {
                                if ($record['status'] == 'online') {
                                    echo("<tr class='onlineUser'>");
                                } else {
                                    echo("<tr>");
                                }
                                
                                echo("
                                    <td colspan='2'>" . $record['etherNumber'] . "</td>
                                    <td colspan='2'>" . $record['status'] . "</td>
                                    <td>
                                        <form method='post' class='formDeleteButtons'>
                                            <input type='hidden' name='etherNumberPost' value='". $record['etherNumber'] ."'>
                                            <input type='hidden' name='switchName' value='". $switchName ."'>
                                            <button type='submit' name='deleteEther'>Verwijder</button>
                                        </form>
                                    </td>
                                </tr>
                                ");
                            }
                        }

                        if (isset($_POST['deleteEther'])){
                            $query = "SELECT ip, username, password FROM $table5 WHERE name = '$_POST[switchName]'";
                            $statement = $pdo->query($query);
                            if ($statement){
                                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($rows as $row) {
                                    $ip = $row['ip'];
                                    $username = $row['username'];
                                    $password = $row['password'];
                                }
                                $query = "SELECT defaultVlan FROM $table6 WHERE id = 1";
                                $statement = $pdo->query($query);
                                if ($statement){
                                    $row = $statement->fetchAll(PDO::FETCH_ASSOC);
                                    $defaultVlan = $row[0]['defaultVlan'];
                                    $connection = connectToSSH($ip, $username, $password);
                                    if (!$connection) {
                                        echo "Connection failed";
                                    }
                                    $cmd = '/interface bridge port set pvid=' . $defaultVlan. ' [find where interface="' . $_POST['etherNumberPost'] . '"]';
                                    $stream = ssh2_exec($connection, $cmd);
                                    stream_set_blocking($stream, true);
                                    

                                }
                            }
                            echo("<meta http-equiv='refresh' content='0'>");
                        }
                        echo("
                        <tr><th colspan='5'> <button class='user2AddButton'><a href='./createBlockedCable.php?id=$_GET[id]&vlan=$getVlan'>Toevoegen</a></button></th>
                        </tr>
                        </table></div>");
                        } 
                    
                
        }
        echo("
        <form method='post' class='createForms3' style='margin-bottom: 0px;'>
            <input class='buttonOutsideTable4' name='deleteCompany' type='submit' value='Verwijderen' onclick='return confirm(\"Weet je zeker dat je dit bedrijf wilt verwijderen?\");'>
            <input class='buttonOutsideTable4' name='showSSH' type='submit' value='Switch Poorten' >
            <button class='buttonOutsideTable4'><a href='./macDropdown.php?vlan=$_GET[vlan]&id=$_GET[id]'>DHCP</a></button>
        
        ");
        
        if(isset($_POST['deleteCompany'])){
            $pdo = connectToDB('radius');
            foreach ($rows2 as $row) {
                $id = $row['id'];
                $username = $row['username'];
                // Using prepared statements to prevent SQL injection
                $queryDelete = "DELETE FROM $table WHERE id = ?";
                $delete = $pdo->prepare($queryDelete);
                $delete->execute([$id]);
        
                $idTable2 = $id * 3;
                $queryDelete2 = "DELETE FROM $table2 WHERE id = ?";
                $delete2 = $pdo->prepare($queryDelete2);
                $delete2->execute([$idTable2]);
        
                $queryDelete3 = "DELETE FROM $table2 WHERE id = ?";
                $delete3 = $pdo->prepare($queryDelete3);
                $delete3->execute([$idTable2 - 1]);
        
                $queryDelete4 = "DELETE FROM $table2 WHERE id = ?";
                $delete4 = $pdo->prepare($queryDelete4);
                $delete4->execute([$idTable2 - 2]);
        
                $queryDelete5 = "DELETE FROM $table3 WHERE username = ?";
                $delete5 = $pdo->prepare($queryDelete5);
                $delete5->execute([$username]);

                $pdo = connectToDB('users');

                // DELETE from $tableUsers
                $queryDelete6 = "DELETE FROM $tableAdmin WHERE username = ?";
                $stmt6 = $pdo->prepare($queryDelete6);
                $stmt6->execute([$username]);

                // DELETE from $tableUsers2
                $queryDelete7 = "DELETE FROM $tableColleague WHERE username = ?";
                $stmt7 = $pdo->prepare($queryDelete7);
                $stmt7->execute([$username]);

                // DELETE from $tableUsers3
                $queryDelete8 = "DELETE FROM $tableBlocked WHERE username = ?";
                $stmt8 = $pdo->prepare($queryDelete8);
                $stmt8->execute([$username]);

            }
            $pdo = connectToDB('users');
            foreach ($allRecordsSwitch as $switchName => $recordsSwitch) {
                $sshConnectionSuccessful = false; 
                foreach ($recordsSwitch as $record) {
                    $query = "SELECT ip, username, password FROM $table5 WHERE name = '$switchName'";
                    $statement = $pdo->query($query);
                    
                    if ($statement) {
                        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($rows as $row) {
                            $ip = $row['ip'];
                            $username = $row['username'];
                            $password = $row['password'];
                        }
                        
                        $connection = connectToSSH($ip, $username, $password);
                        
                        if (!$connection) {
                            $sshConnectionSuccessful = false;
                            break; 
                        } else {
                            $sshConnectionSuccessful = true; 
                            $macAddressPost = $record['macAddress'];
                            $cmd = '/interface ethernet switch rule remove numbers=[find where src-mac-address="'.$macAddressPost.'/FF:FF:FF:FF:FF:FF"]';
                            $stream = ssh2_exec($connection, $cmd);
                            stream_set_blocking($stream, true);
                        }
                    }
                }
                if (!$sshConnectionSuccessful) {
                    continue; 
                }
            }   
            $pdo = connectToDB('users');
            foreach ($allRecordsSwitch as $switchName => $recordsSwitch) {
                $sshConnectionSuccessful = false;
                foreach ($allInterfaceStatus[$switchName] as $record) {
                    $query = "SELECT ip, username, password FROM $table5 WHERE name = '$switchName'";
                    $statement = $pdo->query($query);
                    if (!$statement) {
                        echo "SQL Error: " . $pdo->errorInfo()[2] . PHP_EOL;
                        continue; 
                    }
            
                    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            
                    // Assuming there's only one row for each switch, no need for a loop here
                    foreach ($rows as $row) {
                        $ip = $row['ip'];
                        $username = $row['username'];
                        $password = $row['password'];
                        break;
                    }
            
                    $connection = connectToSSH($ip, $username, $password);
                    if (!$connection) {
                        // Debug: Output a message if SSH connection failed
                        $sshConnectionSuccessful = false;
                        break; // Exit the inner loop
                    } else {
                        $query = "SELECT defaultVlan FROM $table6 WHERE id = 1";
                        $statement = $pdo->query($query);
                        if (!$statement) {
                            // Debug: Output the SQL error message
                            echo "SQL Error: " . $pdo->errorInfo()[2] . PHP_EOL;
                            continue; // Skip to the next switch
                        }
            
                        $sshConnectionSuccessful = true;
                        $row = $statement->fetchAll(PDO::FETCH_ASSOC);
                        $defaultVlan = $row[0]['defaultVlan'];
            
                        $cmd = '/interface bridge port set pvid=' . $defaultVlan . ' [find where interface="' . $record['etherNumber'] . '"]';
                        $stream = ssh2_exec($connection, $cmd);
                        stream_set_blocking($stream, true);
                    }
                }
            
                if (!$sshConnectionSuccessful) {
                    continue; 
                }
            }
            $query = "DELETE FROM $table WHERE id = :id";
            $statement = $pdo->prepare($query);
            $statement->bindParam(':id', $getId, PDO::PARAM_INT);
            $statement->execute();
            echo("<script type='text/javascript'>
            window.location.href = './businesses.php';
            </script>");  
        }
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<body>
    
    <button class='buttonOutsideTable4'><a href="./businesses.php">Terug</a></button>
    </form>
</body>
</html>