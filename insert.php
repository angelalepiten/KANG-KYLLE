<?php
require_once("connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newconnection = new Connection();
    $connection = $newconnection->openConnection();

    $name = $_POST['name'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    // Basic validation
    if (empty($name) || empty($address) || empty($email)) {
        header("Location: index.php?message=invalid");
        exit();
    }

    try {
        $stmt = $connection->prepare("INSERT INTO inquire_tb (Name, Address, Email) VALUES (:name, :address, :email)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        header("Location: index.php?message=success");
    } catch (PDOException $e) {
        header("Location: index.php?message=error");
    }
}
?>
