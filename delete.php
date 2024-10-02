<?php
require_once("connection.php"); // Include your connection file

// Create a new connection instance
$newconnection = new Connection();
$connection = $newconnection->openConnection();

..........................00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000
// Handle deleting a record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id']; // Get the record ID from the POST data

    // Debugging: Check if ID is set
    if (empty($id)) {
        error_log("ID is empty!");
        header("Location: index.php?message=invalid");
        exit();
    }

    // Validate the ID
    if (!is_numeric($id)) {
        header("Location: index.php?message=invalid");
        exit();
    }

    // Prepare the SQL statement to delete the record
    try {
        $stmt = $connection->prepare("DELETE FROM inquire_tb WHERE ID = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Check if the record was deleted
        if ($stmt->rowCount() > 0) {
            header("Location: index.php?message=deleted");
        } else {
            header("Location: index.php?message=notfound");
        }
        exit();
    } catch (PDOException $e) {
        error_log("Delete error: " . $e->getMessage());
        header("Location: index.php?message=error");
        exit();
    }
}

// Fetch records for displaying
$records = $connection->query("SELECT * FROM inquire_tb")->fetchAll(PDO::FETCH_OBJ);
?>

