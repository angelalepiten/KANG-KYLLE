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

// Handle updating a record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    // Basic validation
    if (empty($name) || empty($address) || empty($email)) {
        header("Location: index.php?message=invalid");
        exit();
    }

    try {
        $updateStmt = $connection->prepare("UPDATE inquire_tb SET Name = :name, Address = :address, Email = :email WHERE ID = :id");
        $updateStmt->bindParam(':name', $name);
        $updateStmt->bindParam(':address', $address);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->execute();

        header("Location: index.php?message=updated");
        exit();
    } catch (PDOException $e) {
        header("Location: index.php?message=error");
        exit();
    }
}

// Initialize search variable
$searchTerm = isset($_POST['search']) ? trim($_POST['search']) : '';

// Set the number of records to display per page
$recordsPerPage = 5;

// Get the current page from the query string, defaulting to 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Count total records for pagination
$totalQuery = $searchTerm 
    ? "SELECT COUNT(*) FROM inquire_tb WHERE Name LIKE :search OR Address LIKE :search OR Email LIKE :search" 
    : "SELECT COUNT(*) FROM inquire_tb";

$totalStmt = $connection->prepare($totalQuery);

if ($searchTerm) {
    $searchTermWithWildcards = '%' . $searchTerm . '%';
    $totalStmt->bindParam(':search', $searchTermWithWildcards, PDO::PARAM_STR);
}

$totalStmt->execute();
$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage); // Calculate total pages

// Prepare the SQL statement for fetching the records
if ($searchTerm) {
    $stmt = $connection->prepare("SELECT * FROM inquire_tb WHERE Name LIKE :search OR Address LIKE :search OR Email LIKE :search LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':search', $searchTermWithWildcards, PDO::PARAM_STR);
} else {
    $stmt = $connection->prepare("SELECT * FROM inquire_tb LIMIT :limit OFFSET :offset");
}

// Bind the limit and offset parameters
$stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

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
    <link rel="stylesheet" href="style.css">
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
            <button class="btn btn-primary mt-5" name="add" type="submit">Add</button>
        </div>
    </form>

    <!-- Display success/error message -->
    <?php if ($message == 'success'): ?>
        <div class="alert alert-success" id="alert">Record added successfully!</div>
    <?php elseif ($message == 'error'): ?>
        <div class="alert alert-danger" id="alert">Error adding record.</div>
    <?php elseif ($message == 'invalid'): ?>
        <div class="alert alert-warning" id="alert">Please fill in all fields correctly.</div>
    <?php elseif ($message == 'updated'): ?>
        <div class="alert alert-success" id="alert">Record updated successfully!</div>
    <?php endif; ?>

    <script>
        // Function to fade out alert message
        function fadeOutAlert() {
            const alert = document.getElementById('alert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        alert.style.display = 'none'; // Completely hide the alert after fade
                    }, 500); // Match this with the transition duration
                }, 2000); // Delay before starting the fade
            }
        }

        fadeOutAlert(); // Call the function to fade out the alert
    </script>

   <!-- Search Form -->
    <form id="searchForm" method="POST" class="mb-3">
        <div class="input-group">
            <input type="text" id="searchInput" name="search" class="form-control" placeholder="Search by Name, Address, or Email" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>

    <!-- Display Records in a Table -->
    <table class="table table-dark mt-3">
        <thead>
            <tr>
                <th scope="col">id</th>
                <th scope="col">name</th>
                <th scope="col">address</th>
                <th scope="col">email</th>
                <th scope="col">action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result): ?>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row->id); ?></td>
                        <td><?php echo htmlspecialchars($row->name); ?></td>
                        <td><?php echo htmlspecialchars($row->address); ?></td>
                        <td><?php echo htmlspecialchars($row->email); ?></td>
                        <td>
                            <button class="btn btn-danger me-3" data-bs-toggle="modal" data-bs-target="#editModal" onclick="populateModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                            <a href="delete.php?id=<?php echo $row->ID; ?>" class="btn btn-warning" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No records found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="id" id="modalId">
                    <div class="mb-3">
                        <label for="modalName" class="col-form-label">Name:</label>
                        <input type="text" name="name" class="form-control" id="modalName" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalAddress" class="col-form-label">Address:</label>
                        <input type="text" name="address" class="form-control" id="modalAddress" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalEmail" class="col-form-label">Email:</label>
                        <input type="email" name="email" class="form-control" id="modalEmail" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Pagination Links -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                <li class="page-item <?php echo $page == $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page; ?>&search=<?php echo htmlspecialchars($searchTerm); ?>">
                        <?php echo $page; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function populateModal(record) {
        document.getElementById('modalId').value = record.id;
        document.getElementById('modalName').value = record.name;
        document.getElementById('modalAddress').value = record.address;
        document.getElementById('modalEmail').value = record.email;
    }
</script>
</body>
</html>
