<?php
include "../include/function.php";
checkSession();

if ($_SESSION['level'] == 3) {
    $pdo = connectToDB('users'); // Establish the database connection
    if (!$pdo) {
        die("Connection failed");// Check if the connection is established
    }

    $table = "sessionTime";
    $query = "SELECT * FROM $table";
    $statement = $pdo->query($query);
    if ($statement){
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        echo( htmlHeader() . generateImageDiv() ."
        
        <form method='post' class='createForms'>
            <label class='labelPost'>Tijd in Seconden:</label><br/>
            <input type='number'  class='inputPost2' name='time' value='".$rows[0]['time']."'>
            
        
        <div class=''>
            <input class='buttonOutsideTable' type='submit' name='submit' value='Submit' />
            <button class='buttonOutsideTable'><a href='./settings.php'>Terug</a></button>
        </div>
        </form>
        ");

        if(isset($_POST['submit']) && $_POST['time'] != ''){
            $queryUpdate = "UPDATE $table SET `time` = :time";
            $update = $pdo->prepare($queryUpdate);
            $update->bindParam(':time', $_POST['time']); // Assuming $_POST['time'] contains the time value.
            if ($update->execute()) {
                header("Location: ./redirect.php?page=sessionTime");
                exit;
            }else{
                echo "Error updating record: " . $pdo->errorInfo()[2];//check if the query is executed
            }
        }
    }

} else {
    header("Location: ./logout.php");;
    exit(); // Make sure to exit after setting a header to prevent further execution.
}
?>
