<?php


session_start(); // Start session
$error = "";
include("./include/function.php");
if (!empty($_POST)) { // Check if the form is filled in
    $sText = $_POST['sText'];
    $sPassword = $_POST['sPassword'];

    include("./include/database.php");
    
    $pdo = connectToDB('radius'); // Establish the database connection
    $pdoUsers = connectToDB('users'); // Establish the database connection
    if (!$pdo && !$pdoUsers) {
        die("Connection failed"); // Check if the connection is established
    }

    $table = "radcheck";
    $tableBlocked = "blocked";
    $tableColleague = "colleague";
    $tableAdmin = "admins";
    // Prepare the query with placeholders
    $query = "SELECT * FROM $table WHERE BINARY username = :username AND BINARY value = :password";
    $statement = $pdo->prepare($query);
    $statement->execute([':username' => $sText, ':password' => $sPassword]);

    $pdo = null; // Close the connection
    // Check if a matching row is found
    if ($statement->rowCount() > 0) {
        // Check if the user is blocked
        $queryBlocked = "SELECT * FROM $tableBlocked WHERE username = :username";
        $statementBlocked = $pdoUsers->prepare($queryBlocked);
        $statementBlocked->execute([':username' => $sText]);

        $queryColleague = "SELECT * FROM $tableColleague WHERE username = :username";
        $statementColleague = $pdoUsers->prepare($queryColleague);
        $statementColleague->execute([':username' => $sText]);

        $queryAdmin = "SELECT * FROM $tableAdmin WHERE username = :username";
        $statementAdmin = $pdoUsers->prepare($queryAdmin);
        $statementAdmin->execute([':username' => $sText]);

        $pdoUsers = null; // Close the connection

        if ($statementBlocked->rowCount() > 0) {
            $error = "Gasten mogen niet inloggen";
        } 
        else if ($statementColleague->rowCount() > 0) {$_SESSION["level"] = 2;}
        else if ($statementAdmin->rowCount() > 0) {$_SESSION["level"] = 3;} 
        else {$_SESSION["level"] = 1;}
        $_SESSION["boolSession"] = true;
        $_SESSION["username"] = $sText;
        $_SESSION['time'] = time();
        header("Location: ./pages/buttonScreen.php");
        exit;
        
    } else {
        $error = "Verkeerd wachtwoord of gebruikersnaam";
    }
}else if($_SESSION['boolSession']){
    header("Location: ./pages/buttonScreen.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <form class='formLogin' method="post">
        <?php echo generateImageDiv(); ?>
        <h3>Hier Inloggen</h3>

        <label class='labelPost'><?php echo $error; ?></label> <!-- Error message -->
        <label class='labelPost'>Gebruikersnaam</label>
        <input class='inputPost' type="text" placeholder="Username" name="sText">

        <label class='labelPost'>Wachtwoord</label>
        <input class='inputPost' type="password" placeholder="Password" name="sPassword">

        <button type='submit' class='buttonSubmitPost'>Sign in</button>
    </form>
</body>
</html>
