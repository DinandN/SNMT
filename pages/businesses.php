<?php
include "../include/function.php";
checkSession();
$_SESSION['showSSH'] = false;// Set to false to hide the SSH output so pages load faster
if ($_SESSION['level'] == 2 || $_SESSION['level'] == 3) {
    // Establish the database connection
    $pdo = connectToDB('users');
    $table = "businesses";
    if (!$pdo) {
        die("Connection failed");
    }
    
    // Make query to retrieve businesses information
    $query = "SELECT name, id , vlan FROM $table ORDER BY name ASC";
    $statement = $pdo->query($query);
    if (!$statement) {
        die("Query execution failed");
    }

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
    echo htmlHeader();
    ?>
        <!-- script to filter on businesss name -->
        <script>
            function myFunction() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("searchInput");
                filter = input.value.toUpperCase();
                table = document.getElementById("companyTable");
                tr = table.getElementsByTagName("tr");
                for (i = 0; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[0];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        </script>
    
    
    <?php
    // Build table
    if (count($rows) > 0) {
        echo generateImageDiv() . "
        <div class='displayFlex2'>
            <input class='inputTextField' type='text' id='searchInput' placeholder='Search by company name' onkeyup='myFunction()'>
            
        </div>
        <div class='tableContainer'>
            <table class='table' id='companyTable'>
                <tr class='tableHeader'>
                    <th>Bedrijf's Naam</th>
                    <th>Update</th>
                </tr>";

        foreach ($rows as $row) {
            $id = $row['id'];
            $vlan = $row['vlan'];
            echo "
                <tr>
                    <td><a href='user2.php?id=$id&vlan=$vlan'>" . $row['name'] . "</a></td>
                    <td><button><a href='./CUBusinesses.php?id=" . $row['id'] . "&vlan=".$row['vlan']."'>Update</a></button></td>
                </tr>";
        }

        echo "
            </table>
        </div>";
    } else {
        echo "No results found.";
    }
}else{
    header("Location: ../index.php");
}
    ?>

    <br>
    <div class='displayFlex'>
        
        <button class='buttonOutsideTable'><a href="./CUBusinesses.php">Toevoegen</a></button>
        <button class='buttonOutsideTable'><a href="./buttonScreen.php">Terug</a></button>
    </div>
</body>
</html>
