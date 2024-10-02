<?php
class Connection {
    private $server = "mysql:host=localhost;port=3306;dbname=client_db;charset=utf8mb4"; // Include port if changed
    private $user = "root"; // Default XAMPP MySQL user
    private $pass = ""; // Default XAMPP MySQL password (empty)
    private $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Set error mode to exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ // Set fetch mode
    );
    protected $pdo; // PDO object

    public function openConnection() {
        try {
            $this->pdo = new PDO($this->server, $this->user, $this->pass, $this->options); // Create PDO instance
            return $this->pdo; // Return the PDO instance
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage(); // Show error message if connection fails
            exit(); // Exit the script
        }
    }
}

// Instantiate the Connection class
$newconnection = new Connection();
?>
