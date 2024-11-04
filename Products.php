<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

    // Check if the user is an admin (notAdmin flag should be false)
    if (!isset($_SESSION['notAdmin']) || $_SESSION['notAdmin'] === true) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized access: not an admin']);
        exit();
    }

    // Ensure the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require 'db_conn.php'; // Include database connection

        // Decode the JSON request body
        $data = json_decode(file_get_contents('php://input'), true);

        // Check the action type (add or delete) using the 'action' parameter in the request
        if (isset($data['action'])) {
            $action = $data['action'];

            // Handle adding a new product
            if ($action === 'add') {
                // Extract the name and price
                $name = $data['name'];
                $price = $data['price'];

                // Validate input
                if (empty($name) || !is_numeric($price) || $price <= 0) {
                    echo json_encode(['success' => false, 'error' => 'Invalid input']);
                    exit();
                }

                // SQL query to insert a product
                $sql = "INSERT INTO products (Name, Price) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sd", $name, $price);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to add product.']);
                }

                $stmt->close();

            // Handle deleting a product
            } elseif ($action === 'delete') {
                if (isset($data['id'])) {
                    $productId = $data['id'];

                    // SQL query to delete product
                    $sql = "DELETE FROM products WHERE ProductID = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $productId);

                    if ($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to delete product.']);
                    }

                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'error' => 'Product ID is missing.']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid action.']);
            }

        } else {
            echo json_encode(['success' => false, 'error' => 'Action not specified.']);
        }

        $conn->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} else {
    // Redirect to login if session is invalid
    header("Location: index.php");
    exit();
}
?>
