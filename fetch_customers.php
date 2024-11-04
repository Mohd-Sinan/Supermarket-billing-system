<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {
    require "db_conn.php"; // Ensure you have this connection established

    // Define your SQL query
    $sql = "SELECT CustomerID, Name, Address FROM customers"; // or your desired query

    // Execute the query
    $result = $conn->query($sql);

    // Check if the query was successful
    if ($result) {
        $data = [];

        // Fetch the results into an array
        while ($row = $result->fetch_assoc()) {
            $data[] = $row; // Add each row to the data array
        }

        // Convert the array to a JSON object
        echo json_encode($data);
    } else {
        // If the query failed, return an error message
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }

    // Close the connection (optional, but good practice)
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>
