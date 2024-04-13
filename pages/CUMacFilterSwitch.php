<?php
include "../include/function.php";
checkSession();

if ($_SESSION['level'] == 3) {
    $pdo = connectToDB('users');

    if (!$pdo) {
        die("Connection failed");
    }

    $table = "macFilterSwitch";
    $error = ''; // Initialize an error message variable

    // Check if an ID is provided via GET (for update)
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Initialize variables for form input fields
    $ip = '';
    $name = '';
    $username = '';
    $password = '';
    $router = '';
    $macFilter = '';

    if ($id > 0) {
        // Retrieve existing record if updating
        $query = "SELECT * FROM $table WHERE id = $id";
        $statement = $pdo->query($query);

        if ($statement) {
            $row = $statement->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $ip = $row['ip'];
                $name = $row['name'];
                $username = $row['username'];
                $password = $row['password'];
                $router = $row['router'];
                $macFilter = $row['switch'];
            }
        } else {
            die("Query execution failed: " . $pdo->errorInfo()[2]);
        }
    }

    if (isset($_POST['Submit'])) {
        // Handle form submission (both create and update)
        try {
            $ip = str_replace([' ', ','], '', $_POST['ipAdress']);
            $name = str_replace([' ', ','], '', $_POST['name']);
            $username = str_replace([' ', ','], '', $_POST['username']);
            $password = str_replace([' ', ','], '', $_POST['password']);
            $router = isset($_POST['router']) ? 1 : 0;
            $macFilter = isset($_POST['switch']) ? 1 : 0;

            if ($id > 0) {
                // Update an existing record
                $queryUpdate = "UPDATE $table SET ip = :ip, name = :name, username = :username, password = :password, router = :router, switch = :macFilter WHERE id = :id";
                $update = $pdo->prepare($queryUpdate);
                $update->bindParam(':ip', $ip);
                $update->bindParam(':name', $name);
                $update->bindParam(':username', $username);
                $update->bindParam(':password', $password);
                $update->bindParam(':router', $router);
                $update->bindParam(':macFilter', $macFilter);
                $update->bindParam(':id', $id);
                if ($update->execute()) {
                    // Query executed successfully.
                    header("Location: ./macFilterSwitch.php");
                    exit;
                } else {
                    $error = "Error updating record: " . $pdo->errorInfo()[2];
                }
            } else {
                // Insert a new record
                $queryInsert = "INSERT INTO $table (id, ip, name, username, password, router, switch) VALUES (NULL, :ip, :name, :username, :password, :router, :macFilter)";
                $insert = $pdo->prepare($queryInsert);
                $insert->bindParam(':ip', $ip);
                $insert->bindParam(':name', $name);
                $insert->bindParam(':username', $username);
                $insert->bindParam(':password', $password);
                $insert->bindParam(':router', $router);
                $insert->bindParam(':macFilter', $macFilter);
                if ($insert->execute()) {
                    // Query executed successfully.
                    header("Location: ./macFilterSwitch.php");
                    exit;
                } else {
                    $error = "Error inserting record: " . $pdo->errorInfo()[2];
                }
            }
        } catch (PDOException $e) {
            $error = "SQL Error: " . $e->getMessage();
        }
    }

    echo generateImageDiv();
} else {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php echo htmlHeader(); ?>
<body>
    <form method='post' class='createForms'>
        <label class='labelPost'><?php echo $error; ?></label> <!-- Error message -->
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <!-- Existing record ID (hidden) for updates -->
        
        <label class='labelPost'>IP adress</label>
        <input class='inputPost2' type="text" name="ipAdress" value="<?php echo $ip; ?>">
        <br><br>

        <label class='labelPost'>Naam</label>
        <input class='inputPost2' type="text" name="name" value="<?php echo $name; ?>">
        <br><br>

        <label class='labelPost'>Gebruikersnaam</label>
        <input class='inputPost2' type="text" name="username" value="<?php echo $username; ?>">
        <br><br>

        <label class='labelPost'>Wachtwoord</label>
        <input class='inputPost2' type="text" name="password" value="<?php echo $password; ?>">
        <br><br>

        <label class='labelPost'>Router</label>
        <input class='inputPost2' type="checkbox" name="router" <?php echo $router ? 'checked' : ''; ?>>
        <br><br>

        <label class='labelPost'>macFilter</label>
        <input class='inputPost2' type="checkbox" name="switch" <?php echo $macFilter ? 'checked' : ''; ?>>
        <br><br>

        <input class='buttonOutsideTable' type="submit" name="Submit" value="Versturen">
        <button class='buttonOutsideTable
        '><a href="./macFilterSwitch.php">Terug</a></button>
    </form>
</body>
</html>
