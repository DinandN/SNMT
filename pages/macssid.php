<?php
include ("../include/function.php");
checkSession();
if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) { 
    
    $pdo = connectToDB('users'); // Establish the database connection
    if (!$pdo) {
        die("Connection failed");// Check if the connection is established
    }

    $table = "macFilterSwitch";
    $tableDHCP = "prefixDHCP";
    $tableBlockedVlan = "blockedPorts";
    $name = $_GET['name'];

    $query = "SELECT * FROM $table WHERE name = :name";
    $query2 = "SELECT * FROM $tableDHCP WHERE id = :id";
    $query3 = "SELECT * FROM $tableBlockedVlan WHERE id = :id";

    // Prepare the SQL statements.
    $statement = $pdo->prepare($query);
    $statement2 = $pdo->prepare($query2);
    $statement3 = $pdo->prepare($query3);

    // Bind the values to the placeholders in the prepared statements.
    $statement->bindParam(':name', $name);
    $statement2->bindValue(':id', 1); // Assuming you always want to fetch by id=1.
    $statement3->bindValue(':id', 1); // Assuming you always want to fetch by id=1.

    // Execute the prepared statements.
    if ($statement->execute() && $statement2->execute() && $statement3->execute()) {
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $ip =  $row['ip'];
            $username = $row['username'];
            $password = $row['password'];
            $router = $row['router'];
        }
        $row2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
        $row3 = $statement3->fetchAll(PDO::FETCH_ASSOC);
        $array = [];
        // Establish the database connection
        $connection = connectToSSH($ip, $username , $password);
        if (!$connection) {
            die("Connection failed");//Check if the connection is made
        }
        $cmd ;
        if ($router == 1 && $_GET['vlan'] == '') {
            $cmd = '/ip dhcp-server lease print proplist=last-seen,address,mac-address,host-name where server="'.$row2[0]['prefix'].''.intval($row3[0]['defaultVlan']).'"';
        } else if($_GET['vlan'] != ''){
            $cmd = '/ip dhcp-server lease print proplist=last-seen,address,mac-address,host-name where server="'.$row2[0]['prefix'].''.$_GET['vlan'].'"';
        }else{
            $cmd = '/interface ethernet switch rule export terse';
        }
        $stream = ssh2_exec($connection, $cmd, NULL, NULL , 90 , 25, SSH2_TERM_UNIT_CHARS);
        stream_set_blocking($stream, true);
        $resultStream = stream_get_contents($stream);
        $array = explode("\n", $resultStream);
    //print_r($array);
        $records = [];
        if ($router == 1) {
            foreach ($array as $key) {
                $matches = [];
                preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $key, $matches);
                $address = isset($matches[0]) ? $matches[0] : '';

                preg_match('/\b(?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}\b/', $key, $matches);
                $macAddress = isset($matches[0]) ? $matches[0] : '';
                
                preg_match('/\b[0-9A-Fa-f]{2}(?:[:-][0-9A-Fa-f]{2}){5}\s+([^\s]+)/', $key, $matches);
                $name = isset($matches[1]) ? $matches[1] : '';
                

                preg_match('/\b\d+m\d+s\b/', $key, $matches);
                $time = isset($matches[0]) ? $matches[0] : '';

                if (!empty($address) && !empty($macAddress) && !empty($time)) {
                    $records[] = [
                        'address' => $address,
                        'macAddress' => $macAddress,
                        'hostName' => $name,
                        'time' => $time
                    ];
                }
            }
            usort($records, function ($a, $b) {
                return convertTimeToSeconds($b['time']) - convertTimeToSeconds($a['time']);
            });
        } else {
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
                
                if (!empty($comment) && !empty($vlanId) && !empty($macAddress)) {
                    $records[] = [
                        'comment' => $comment,
                        'vlanId' => $vlanId,
                        'macAddress' => $macAddress
                    ];
                }
            }
        }

        echo ("
            " . htmlHeader() . "
                " . generateImageDiv() . "
                <div class='tableContainer'>
                    <table class='table' width='100%'>
                        <tr class='tableHeader'>
        ");

        if ($router == 1) {
            echo ("
                            <th>Laatst Gezien</th>
                            <th>Address</th>
                            <th>Mac Address</th>
                            <th>Mac Vendor</th>
                            <th>Naam</th>
                ");
                            if(!isset($_GET['vlan'])){
                                echo("<th>Toevoegen</th>");
                            }else{
                                echo("<th>Verwijderen</th>");
                            }
                            echo("
                        </tr>
            ");
            usort($records, function($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });
            foreach ($records as $record) {
                echo ("
                        <tr>
                            <td>" . $record['time'] . "</td>
                            <td>" . $record['address'] . "</td>
                            <td>" . $record['macAddress'] . "</td>
                            <td class='macVendor' style='width: 25%;'>" . $record['macAddress'] . "</td>
                            <td>" . $record['hostName'] . "</td>");
                            
                            if(!isset($_GET['vlan'])){
                            echo("
                            <td>
                                <form method='post'>
                                    <input type='hidden' name='macAddressPost' value='". $record['macAddress'] ."'>
                                    <input type='hidden' name='hostNamePost' value='". $record['hostName'] ."'>
                                    <button type='submit' name='addButtonUser'>Toevoegen</button>");
                                    if (isset($_POST['addButtonUser'])) {
                                        $macAddress = $_POST['macAddressPost'];
                                        $hostName = $_POST['hostNamePost'];
                                        header("Location: ./CUMacssid.php?macAddress=$macAddress&hostName=$hostName&name=$_GET[name]");
                                    }
                                echo ("
                                </form>
                            </td>");
                            }else{
                                echo("
                                <td>
                                    <form method='post'>
                                        <input type='hidden' name='macAddressPost' value='". $record['macAddress'] ."'>
                                        <input type='hidden' name='hostNamePost' value='". $record['hostName'] ."'>
                                        <button type='submit' name='deleteButtonUser'>Verwijderen</button>");
                                        if(isset($_POST['deleteButtonUser'])) {
                                            $usernamePost = $_POST['usernamePost'];
                                            $cmd2 = '/interface ethernet switch rule remove numbers=[find where src-mac-address="'.$usernamePost.'/FF:FF:FF:FF:FF:FF"]';
                                            $stream2 = ssh2_exec($connection, $cmd2);
                                            stream_set_blocking($stream2, true);
                                            header("Location: ./redirect.php?page=macssid&name=$name");
                                        }
                                    echo ("
                                    </form>
                                </td>");
                            }
                            echo("
                        </tr>
                ");    
            }
            echo ("
                    </table>
                    <div class='displayFlex'>");
                    if(isset($_GET['vlan'])){
                        echo ("<button class='buttonOutsideTable2'><a href='./macDropdown.php?vlan=$_GET[vlan]&id=$_GET[id]'>Terug</a></button>");
                    }else{
                        echo ("<button class='buttonOutsideTable2'><a href='./macDropdown.php'>Terug</a></button>");
                    }
                    echo("
                    </div>
                </div>
                ");
        } 

        

        ssh2_disconnect($connection);
    }
}else{
    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
         fetchMacAddressInfo();
    </script>
</body>
</html>
