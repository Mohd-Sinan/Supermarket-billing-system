<?php
session_start(); // Start the session to access session variables

// Check if the user is not an admin (using notAdmin flag)
if (isset($_SESSION['notAdmin']) && $_SESSION['notAdmin'] === true) {
    // If the user is not an admin, respond with an error and exit
    echo json_encode(['success' => false, 'error' => 'Unauthorized access: not an admin']);
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'db_conn.php'; // Include database connection

    // Get the JSON data from the POST request
    $data = json_decode(file_get_contents('php://input'), true);

    // Extract the name and price from the data
    $name = $data['name'];
    $price = $data['price'];

    // Perform server-side validation (optional but recommended)
    if (empty($name) || !is_numeric($price) || $price <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit();
    }

    // Assume you have a database connection here (e.g., using PDO or MySQLi)
    // $conn = new mysqli('localhost', 'username', 'password', 'database');

    // Prepare and execute the SQL to insert the product
    $sql = "INSERT INTO products (name, price) VALUES ('$name', '$price')";
    if ($conn->query($sql) === TRUE) {
        // Respond with success
        echo json_encode(['success' => true]);
    } else {
        // Respond with error
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

    // Close the connection
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
