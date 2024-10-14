<?php
session_start();
if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {
    // Ensure that the request method is POST and the user is authorized (e.g., admin)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require 'db_conn.php'; // Include database connection

        // Decode the JSON request body
        $data = json_decode(file_get_contents('php://input'), true);

        // Check if product ID is provided
        if (isset($data['id'])) {
            $productId = $data['id'];

            // SQL query to delete product
            $sql = "DELETE FROM products WHERE ProductID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productId);

            if ($stmt->execute()) {
                // Return success response
                echo json_encode(['success' => true]);
            } else {
                // Return error if deletion fails
                echo json_encode(['success' => false, 'error' => 'Failed to delete product.']);
            }

            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'error' => 'Product ID is missing.']);
        }

        $conn->close();
    }
} else {
    // Redirect to login if session is invalid
    header("Location: index.php");
    exit();
}
?>
