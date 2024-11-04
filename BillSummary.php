<?php
// Start the session
session_start();

// Initialize the response array
$response = [
    'success' => false,
    'uuid' => $_SESSION['uuid'],
    'customerName' => '',
    'data' => '',
    'GrandTotal' => 0
];

// Check if the user is logged in
if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

    // Set the customer name in the response
    $response['customerName'] = htmlspecialchars($_SESSION['CustomerName']);

    // Retrieve the product list from the session and decode the JSON
    if (isset($_SESSION['Bill_list'])) {
        $productList = json_decode($_SESSION['Bill_list'], true); // Decode the JSON into an associative array

        // Make sure it's an array and not empty
        if (is_array($productList) && !empty($productList)) {
            // Loop through the product list and concatenate the <td> tags for each product
            $tdTags = '';
            foreach ($productList as $product) {
                $tdTags .= "<tr><td>" . htmlspecialchars($product['ProductID']) . "</td>";
                $tdTags .= "<td>" . htmlspecialchars($product['Name']) . "</td>";
                $tdTags .= "<td>" . htmlspecialchars($product['Quantity']) . "</td>";
                $tdTags .= "<td>" . htmlspecialchars($product['Amount']) . "</td></tr>";
            }
            $response['data'] = $tdTags;
            $response['success'] = true;
            $response['GrandTotal'] = $_SESSION['GrandTotal'];;
        } else {
            $response['data'] = "<tr><td colspan='4' style='text-align: center;'>Bill is Empty</td></tr>";
        }
    } else {
        $response['data'] = "<tr><td colspan='4' style='text-align: center;'>No Bill list found in session.</td></tr>";
    }
} else {
    header("Location: index.php");
    exit();
}

// Set the Content-Type to application/json and return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
