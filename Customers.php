<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in and is an admin
if (isset($_SESSION['id']) && isset($_SESSION['user_name']) && (isset($_SESSION['notAdmin']) && $_SESSION['notAdmin'] === false)) {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require 'db_conn.php'; // Include database connection

        // Decode the JSON request body
        $data = json_decode(file_get_contents('php://input'), true);

        // Determine the action based on the incoming data
        if (isset($data['action'])) {
            if ($data['action'] === 'add') {
                // Add Customer
                $name = $data['name'];
                $address = $data['address'];

                // Validate input
                if (empty($name) || empty($address)) {
                    echo json_encode(['success' => false, 'error' => 'Invalid input']);
                    exit();
                }

                // Prepare and execute the SQL to insert the customer
                $sql = "INSERT INTO customers (Name, Address) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $name, $address);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to add customer.']);
                }

                $stmt->close();
            } elseif ($data['action'] === 'delete') {
                // Delete Customer
                if (isset($data['id'])) {
                    $customerId = $data['id'];

                    // Prepare and execute the SQL to delete the customer
                    $sql = "DELETE FROM customers WHERE CustomerID = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $customerId);

                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to delete customer.']);
                    }

                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Customer ID is missing.']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid action specified.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'No action specified.']);
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
}
?>
