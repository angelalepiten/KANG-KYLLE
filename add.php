<?php
require_once("connection.php"); // Include your connection file

// Create a new connection instance
$newconnection = new Connection();
$connection = $newconnection->openConnection();

// Handle adding a record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
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
        exit();
    } catch (PDOException $e) {
        header("Location: index.php?message=error");
        exit();
    }
}

// Fetch records from the database
$stmt = $connection->prepare("SELECT * FROM inquire_tb");
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_OBJ); // Fetch results

// Check for messages
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Form for adding records -->
<div class="container mt-4">
    <form method="POST" action="">
        <div class="row mb-3">
            <label for="name" class="col-sm-2 col-form-label">Name</label>
            <div class="col-sm-8">
                <input type="text" name="name" class="form-control" id="name" placeholder="Enter Name" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="address" class="col-sm-2 col-form-label">Address</label>
            <div class="col-sm-8">
                <input type="text" name="address" class="form-control" id="address" placeholder="Enter Address" required>
            </div>
        </div>
        <div class="row mb-3">
            <label for="email" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-8">
                <input type="email" name="email" class="form-control" id="email" placeholder="Enter Email" required>
            </div>
        </div>
        <div class="container d-flex justify-content-end">
            <button class="btn btn-primary mt-5" name="add" type="submit">Add Record</button>
        </div>
    </form>

    <!-- Display success/error message -->
    <?php if ($message == 'success'): ?>
        <div class="alert alert-success">Record added successfully!</div>
    <?php elseif ($message == 'error'): ?>
        <div class="alert alert-danger">Error adding record.</div>
    <?php elseif ($message == 'invalid'): ?>
        <div class="alert alert-warning">Please fill in all fields correctly.</div>
    <?php endif; ?>

    <table class="table table-dark mt-3">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Address</th>
                <th scope="col">Email</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result): ?>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row->ID); ?></td>
                        <td><?php echo htmlspecialchars($row->Name); ?></td>
                        <td><?php echo htmlspecialchars($row->Address); ?></td>
                        <td><?php echo htmlspecialchars($row->Email); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row->ID; ?>" class="btn btn-danger me-3">Edit</a>
                            <a href="delete.php?id=<?php echo $row->ID; ?>" class="btn btn-warning" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No records found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
