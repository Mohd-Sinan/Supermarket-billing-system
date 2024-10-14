<?php
session_start();
include "db_conn.php"; // Include your database connection

// Define the SQL query to fetch products
$sql = "SELECT ProductID, Name, Price FROM products";

// Execute the query
$result = $conn->query($sql);

// Initialize an array to hold the product data
$products = [];

// Check if the query was successful
if ($result) {
    // Fetch the results into the products array
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Set the response header to JSON and encode the products
    echo json_encode($products);
} else {
    // If the query failed, send an error response
    echo json_encode(['error' => 'Product query failed: ' . $conn->error]);
}

// Close the database connection
$conn->close();
?>
