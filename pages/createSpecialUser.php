<?php
include "../include/function.php";
checkSession();


//Page for making a special user (admin, colleague, blocked)
//blocked being guests (so they can't login to the system)


if ($_SESSION['level'] == 3) {

    // Connect to the database
    $pdoUsers = connectToDB('users');
    $pdoRadius = connectToDB('radius');

    if (!$pdoUsers || !$pdoRadius) {
        die("Connection failed");
    }

    $tableUsers = $_GET['table'];
    $tableRadius = "radcheck";

    $query = "SELECT username FROM $tableRadius ORDER BY username ASC";
    $statement = $pdoRadius->query($query);

    //select querys for checking if the username already exists in the other tables
    $tableCheck = "SELECT * FROM admins";
    $statementCheck = $pdoUsers->query($tableCheck);
    $tableCheck2 = "SELECT * FROM blocked";
    $statementCheck2 = $pdoUsers->query($tableCheck2);
    $tableCheck3 = "SELECT * FROM colleague";
    $statementCheck3 = $pdoUsers->query($tableCheck3);

    if ($statement && $statementCheck && $statementCheck2 && $statementCheck3) {
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $existingUsernames = array();
        while ($checkRow = $statementCheck->fetch(PDO::FETCH_ASSOC)) {
            $existingUsernames[] = $checkRow['username'];
        }
        while ($checkRow = $statementCheck2->fetch(PDO::FETCH_ASSOC)) {
            $existingUsernames[] = $checkRow['username'];
        }
        while ($checkRow = $statementCheck3->fetch(PDO::FETCH_ASSOC)) {
            $existingUsernames[] = $checkRow['username'];
        }

        $filteredRows = array_filter($rows, function ($row) use ($existingUsernames) {
            return !in_array($row['username'], $existingUsernames);//filter the rows so the usernames that already exist in the other tables are not shown
        });

        echo htmlHeader(). generateImageDiv() . "<form method='post'>
            <select class='dropdownMenu' name='dropdownMenu'>";
        foreach ($filteredRows as $row) {
            echo "<option>" . $row['username'] . "</option><br><br>";
        }
        echo "<br><br>
        
        </select>
        <div class='displayFlex'>
            <input class='buttonOutsideTable' type='submit' name='Submit' value='Versturen'>
            <button class='buttonOutsideTable'><a href='./specialUser.php?table=$tableUsers'>Terug</a></button>
            </div>
        </form>";

        if (isset($_POST['Submit'])) {//insert the selected username into the table
            $selectedUsername = $_POST['dropdownMenu'];

            $query2 = "INSERT INTO $tableUsers VALUES (:username)";
            $statement2 = $pdoUsers->prepare($query2);
            $statement2->bindParam(':username', $selectedUsername);
            $statement2->execute();

            header("Location: ./specialUser.php?table=$tableUsers");
            exit;
        }
    } else {
        die("Query execution failed pdoRadius: " . $pdoRadius->errorInfo() . "\n Query execution failed pdoUsers: "  . $pdoUsers->errorInfo());
    }
}else{
    header("Location: ../index.php");
    exit;
}
?>

