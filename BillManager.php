<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (isset($_SESSION['id']) && isset($_SESSION['user_name'])){

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Decode the JSON request body
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['action'])) {
            if ($data['action'] === 'initBill') {
                // Get the current time in 100-nanosecond intervals since 00:00:00 UTC on October 15, 1582
                $time = microtime(true) * 10000000 + 621355968000000000;

                // Get the MAC address of the machine
                $mac = strtoupper(substr(md5(uniqid(rand(), true)), 0, 12)); // Example MAC address
                $time_hi_and_version = ($time & 0x0FFF000000000000) >> 48 | (1 << 12); // Set version to 1

                // Generate the UUID
                $uuid = sprintf('%08x-%04x-%04x-%04x-%s',
                    ($time & 0xFFFFFFFF),
                    ($time >> 32) & 0xFFFF,
                    $time_hi_and_version & 0xFFFF,
                    random_int(0, 0x3FFF) | 0x8000, // Set bits 6 and 7 to indicate a random UUID
                    substr($mac, 0, 12)
                );

                // Store the UUID in Session
                $_SESSION['uuid'] = $uuid;

                $_SESSION['CustomerID'] = $data['CustomerID'];
                $_SESSION['CustomerName'] = $data['CustomerName'];

                // Validate input
                if (empty($data['CustomerID'])) {
                    echo json_encode(['success' => false, 'error' => 'CustomerID is Missing']);
                    exit();
                }

                $_SESSION['Bill_list'] = json_encode([]);
                $_SESSION['GrandTotal'] = 0;

                echo json_encode(['success' => true , 'uuid' => $uuid]);

            } elseif ($data['action'] === 'addBill') {
                require 'db_conn.php';

                // Check if uuid session is set and CustomerName matches
                if (!isset($_SESSION['uuid']) || $_SESSION['CustomerID'] !== $data['customerID']) {
                    echo json_encode(['success' => false,  'error' => 'Session invalid or CustomerID mismatch']);
                    exit();
                }

                // Query to get product name and price from the products table
                $query = "SELECT name, price FROM products WHERE productID = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $data['productID']);
                $stmt->execute();
                $result = $stmt->get_result();
                $product_fetch_result = $result->fetch_assoc();

                if (!$product_fetch_result) {
                    echo json_encode(['success' => false , 'error' => 'Product not found']);
                    exit();
                }

                // Compute amount
                $amount = $data['Quantity'] * $product_fetch_result['price'];
                $_SESSION['GrandTotal'] += $amount;

                $bill_list = json_decode($_SESSION['Bill_list'], true); // Decode JSON string to an array

                // Create the new product entry
                $newProduct = [
                    'ProductID' => $data['productID'],
                    'Name'      => $product_fetch_result['name'],
                    'Quantity'  => $data['Quantity'],
                    'Amount'    => $amount
                ];

                // Add the new product to the Bill_list
                $bill_list[] = $newProduct;

                // Encode the updated array back to JSON and save it in the session
                $_SESSION['Bill_list'] = json_encode($bill_list);

                // Respond with success
                echo json_encode(['success' => true]);

                // Close the statement and connection
                $stmt->close();
                $conn->close();

            } elseif ($data['action'] === 'removeLast') {

                $bill_list = json_decode($_SESSION['Bill_list'], true);

                if(!empty($bill_list)){
                    $last_item = array_pop($bill_list);
                    $_SESSION['GrandTotal'] -= $last_item['Amount'];
                    $_SESSION['Bill_list'] = json_encode($bill_list);

                    // Respond with success
                    echo json_encode(['success' => true]);

                } else {
                    echo json_encode(['success' => false, 'error' => 'Bill is already Empty']);
                }

            } elseif ($data['action'] === 'discardBill') {

                // Check if Bill_list session is set and CustomerName matches
                if (!isset($_SESSION['uuid']) || $_SESSION['CustomerID'] !== $data['customerID']) {
                    echo json_encode(['success' => false,  'error' => 'Session invalid or CustomerName mismatch']);
                    exit();
                }

                //unset Bill session variables
                unset($_SESSION['uuid']);
                unset($_SESSION['CustomerID']);
                unset($_SESSION['CustomerName']);
                unset($_SESSION['Bill_list']);
                unset($_SESSION['GrandTotal']);

                // Respond with success
                echo json_encode(['success' => true]);
            } elseif ($data['action'] === 'generateBill') {
                require 'db_conn.php';

                $bill_list = json_decode($_SESSION['Bill_list'], true);

                // Check if uuid session is set and CustomerID matches
                if (!isset($_SESSION['uuid']) || $_SESSION['CustomerID'] !== $data['customerID']) {
                    echo json_encode(['success' => false, 'error' => 'Session invalid or CustomerID mismatch']);
                    exit();
                }

                if(empty($bill_list)){
                    echo json_encode(['success' => false, 'error' => 'Bill Cannot be Empty']);
                    exit();
                }

                // Start a transaction
                $conn->begin_transaction();

                try {
                    // Use the UUID from the session as the OrderID
                    $uuid = $_SESSION['uuid'];

                    // Handle CustomerID: Use NULL if session CustomerID is -1
                    $customerID = ($_SESSION['CustomerID'] == -1) ? NULL : $_SESSION['CustomerID'];

                    // Insert the order into the orders table with the session UUID as OrderID
                    $stmt = $conn->prepare("INSERT INTO orders (OrderID, CustomerID, Date_Time, Completion_Status) VALUES (?, ?, NOW(), 'pending')");

                    // Use 'i' type if $customerID is an integer, and 's' type for OrderID (UUID)
                    // NULL needs to be passed using NULL for bind_param (to handle NULL, we use conditional binding)
                    if ($customerID === NULL) {
                        $stmt->bind_param('ss', $uuid, $customerID); // Pass NULL explicitly for CustomerID
                    } else {
                        $stmt->bind_param('si', $uuid, $customerID);
                    }
                    if (!$stmt->execute()) {
                        throw new Exception("Order insertion failed: " . $stmt->error);
                    }
                    // Insert each bill item into the sales table
                    $stmt = $conn->prepare("INSERT INTO sales (OrderID, ProductID, Quantity) VALUES (?, ?, ?)");

                    foreach ($bill_list as $item) {
                        $stmt->bind_param('sid', $uuid, $item['ProductID'], $item['Quantity']);
                        $stmt->execute();
                    }

                    // Update the order status to 'complete'
                    $stmt = $conn->prepare("UPDATE orders SET Completion_Status = 'complete' WHERE OrderID = ?");
                    $stmt->bind_param('s', $uuid);
                    $stmt->execute();

                    // Commit the transaction
                    $conn->commit();

                    // Clear session variables related to the bill
                    unset($_SESSION['uuid']);
                    unset($_SESSION['CustomerID']);
                    unset($_SESSION['CustomerName']);
                    unset($_SESSION['Bill_list']);
                    unset($_SESSION['GrandTotal']);

                    // Respond with success and the OrderID (UUID)
                    echo json_encode(['success' => true, 'OrderID' => $uuid]);

                } catch (Exception $e) {
                    // Rollback the transaction if something goes wrong
                    $conn->rollback();
                    echo json_encode(['success' => false, 'error' => 'Transaction failed: ' . $e->getMessage()]);
                }

                // Close the statement and connection
                $stmt->close();
                $conn->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid Action']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }
} else {
    // Redirect to login if session is invalid
    header("Location: index.php");
    exit();
}
?>
