<?php

include "../include/function.php";
    checkSession();

if ($_SESSION['level'] == 3) {
    $table = $_GET['table'];

    // Establish the database connection
    $pdo = connectToDB('users');
    if (!$pdo) {
        die("Connection failed");//Check if the connection is made
    }

    $query = "SELECT * FROM $table ORDER BY username ASC";
    $statement = $pdo->query($query);

    if ($statement){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        echo ( htmlHeader().
            generateImageDiv() ."
            <div class='tableContainer'>
                <table  class='table' width='100%'>
                <tr class='tableHeader'>
                    <th>Gebruikersnaam</th>
                    <th>Verwijder</th>
                </tr>
                ");
                foreach ($rows as $row) {
                    echo ("
                        <tr>
                            <td>" . $row['username'] . "</td>
                            <td>
                                <form method='post' class='formDeleteButtons'>
                                    <input type='hidden' name='usernamePost' value='" . $row['username'] . "'>
                                    <button type='submit' name='deleteButtonUser' >Verwijder</button>");
                                    if(isset($_POST['deleteButtonUser'])  && $_POST['usernamePost'] === $row['username']){
                                        $queryDelete = "DELETE FROM $table WHERE username = '$row[username]'";
                                        $delete = $pdo->query($queryDelete);
                                        header("Location: ./redirect.php?page=specialUser&table=$table");
                                    }
                                    echo("
                                </form>
                            </td>
                        </tr>
                    ");
                }
            echo ("
            </table>
                <div class='displayFlex'>
                    
                    <button name='addUser' class='buttonOutsideTable'> <a href='./createSpecialUser.php?table=$table'>Toevoegen</a></button>
                    <button class='buttonOutsideTable'><a href='./settings.php'>Terug</a></button>
                </div>
            </div>
            ");
    }else {
        die("Query execution failed: " . $pdo->errorInfo()[2]);//Check if the query is executed
    }
}else{
    header("Location: ../index.php");// Redirect to login page if there is no session
}
?>