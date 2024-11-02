<?php
session_start();

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {
    if (!isset($_SESSION['notAdmin']) || $_SESSION['notAdmin'] === false) {
        header("Location: index.php");
        exit();
    }
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #1690A7;
        }
        #container {
            position: relative;
            background: #fff;
            border-radius: 5px;
            width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .tab-content {
            position: relative;
            border-radius: 0px 5px 5px 5px;
            border: 1px solid #ccc;
            padding: 20px;
        }
        .form-controls {
            margin-top: 10px;
        }
        .form-controls label {
            display: inline-block;
            width: 150px;
        }
        .form-controls select, .form-controls input {
            padding: 5px;
            width: 300px;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            background-color: #fff;
            border: 1px solid #767676;
            border-radius: 2px;
            background-color: #fff;
            border-radius: 2px;
        }
        input {
            background-color: #fff;
            border: 1px solid #767676;
            border-radius: 2px;
        }
        button {
            padding: 10px 20px;
            margin-top: 15px;
            border: 1px solid #767676;
            border-radius: 2px;
            background-color: #efefef;
        }
        button:hover{
            background-color:#A9A9A9;
        }
        .input-group {
            margin-bottom: 10px;
        }
        .input-group label {
            display: inline-block;
            width: 80px;
        }
        .input-group input {
            padding: 5px;
            width: 200px;
        }
        table {
            width:100%;
            border-collapse: seperate;
            border-spacing:0;
            margin-top: 20px;
            box-shadow: 0px -1px black , -1px 0px black , -1px -1px black;
        }
        th, td {
            border: 1px solid black;
            border-left:0;
            border-top:0;
            background-color:#fff;
            padding: 10px;
            text-align: left;
        }
        .fixTableHead {
            overflow-y: scroll;
            max-height: 200px;
            margin-top:20px;
            1px solid black;
            box-shadow: 0px -1px black , -1px 0px black , -1px -1px black;
            border-bottom: 1px solid black;
        }
        .fixTableHead thead {
            position: sticky;
            top:0px;
            z-index:1;
        }
        .fixTableHead table {
            margin-top:0px;
            box-shadow:none;
        }

        #bill-summary {
            display: none;
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
        }
        #bill-summary table , h4{
            margin:5px;
        }
        #deleteProductID,#deleteCustomerID {
            padding: 10px;
        }
        .notification {
            position: absolute;
            top: 10px;
            right: 20px;
            padding: 10px 20px;
            background-color: #efefef;
            color: #333;
            border: 1px solid #767676;
            border-radius: 2px;
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
            z-index: 1000;
            opacity: 0.95;
            transition: opacity 0.5s ease-out;
        }
        .notification.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .notification.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        #refresh {
            float: right;
        }
        .bill-info{
            display:none;
        }
        .logout{
            position: absolute;
            top: 10px;
            right: 20px;
        }
        .bill_header{
            text-align: center;
        }
    </style>
</head>
<body>

<div id="container">
    <!-- Billing Information Tab -->
    <div class="tab-content" id="billing-content">
        <button class="logout" onclick="window.location.href='logout.php'" type="button">Logout</button>
        <h3>Billing Information</h3>

        <div class="form-controls">
            <label for="selectCustomer">Select Customer:</label>
            <select id="selectCustomer">
            </select>
        </div>

        <button id="new-order">Create new Order for Customer</button>
        <button id="discard-order" disabled>Discard Order</button>
        <button id="generate-bill" disabled>Generate Bill</button>

        <div class="form-controls">
            <label for="selectProduct">Select Product:</label>
            <select id="selectProduct" disabled>
            </select>
        </div>

        <div class="form-controls">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" value="1" step="1" disabled>
        </div>

        <button id="add-bill" disabled>Add to Bill</button>
        <button id="remove-last" disabled>Remove Last</button>
        <button id="refresh">Refresh</button>
        <div class="fixTableHead">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
        <!-- Bill Summary -->
        <div id="bill-summary">
            <h3 class="bill_header">Supreme Supermarket</h3>
            <h4 class="bill_header" ><u>Bill Info</u></h4>
            <p id='customer-name' class='bill-info'></p>
            <p id="order-id" class='bill-info'></p>
            <p class='bill-info'>Date: <?php echo date('d-m-Y'); ?></p>
            <table>
                <thead>
                    <tr>
                        <th>ProdID</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Rs.</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <p id='GrandTotal'>Grand Total: 0</p>
        </div>
    </div>
</div>
<script>

document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    loadCustomers();

    const selectCustomer = document.getElementById('selectCustomer');
    const selectProduct = document.getElementById('selectProduct');
    const discardOrder = document.getElementById('discard-order');
    const quantityInput = document.getElementById('quantity');
    const addBillButton = document.getElementById('add-bill');
    const removeLastButton = document.getElementById('remove-last');
    const generateBillButton = document.getElementById('generate-bill');
    const billSummary = document.getElementById('bill-summary');
    const createOrder = document.getElementById('new-order');

    generateBillButton.addEventListener('click', generateBill);
    addBillButton.addEventListener('click', addBill);
    removeLastButton.addEventListener('click', removeLast);
    createOrder.addEventListener('click', initBill);
    discardOrder.addEventListener('click', discardBill);

    document.getElementById('refresh').addEventListener('click', loadProducts);

    function loadProducts() {
        fetch('fetch_products.php')
            .then(response => response.json()) // Expect a JSON response
            .then(data => {
                if (data.error) {
                    console.error('Error loading products:', data.error);
                    return;
                }

                // Get references to the product table body and product dropdown
                const billingTableBody = document.querySelector('#billing-content tbody');

                // Clear any existing rows in the product table and dropdown
                billingTableBody.innerHTML = '';
                selectProduct.innerHTML = '';

                if (data.length === 0) {
                    billingTableBody.innerHTML = '<td colspan="3" style="text-align: center;">No Products Found</td>';
                } else{

                    // Loop through each product returned from the server
                    data.forEach(product => {

                        // Add a row to the product table
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${product.ProductID}</td>
                            <td>${product.Name}</td>
                            <td>${product.Price}</td>
                        `;
                        billingTableBody.appendChild(row);


                        // Add an option to the product dropdown
                        const option = document.createElement('option');
                        option.value = product.ProductID;
                        option.textContent = `${product.ProductID} - ${product.Name}`;
                        selectProduct.appendChild(option);

                    });
                }
            })
            .catch(error => console.error('Error loading products:', error));
    }

    function loadCustomers() {
        fetch('fetch_customers.php')
            .then(response => response.json()) // Expect a JSON response
            .then(data => {
                if (data.error) {
                    alert('Error loading customers:', data.error);
                    return;
                }

                //Default Customer
                selectCustomer.innerHTML = '<option value="-1">Unknown</option>';

                // Loop through each product returned from the server
                data.forEach(customer => {

                    // Add an option to the product dropdown
                    const option = document.createElement('option');
                    option.value = customer.CustomerID;
                    option.textContent = `${customer.CustomerID} - ${customer.Name}`;
                    selectCustomer.appendChild(option);

                });
            })
            .catch(error => console.error('Error loading customers:', error));
    }

    //notification
    const showMessage = (message, type) => {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        const notificationElements = document.querySelectorAll('.notification');

        // Loop and remove any previous notification from the DOM
        notificationElements.forEach(element => { element.remove(); });

        document.getElementById('container').appendChild(notification);
        setTimeout(() => notification.style.opacity = '0', 2500); // Start fade out after 2.5 seconds
        setTimeout(() => notification.remove(), 3000); // Remove after 3 seconds
    };

    function initBill(){

        const CustID = selectCustomer.value;
        const custName = getCustName();

        fetch('BillManager.php' , {
             method: 'POST', // Use GET method to fetch UUID
             headers: {
                        'Content-Type': 'application/json',
                    },
             body: JSON.stringify({ action: "initBill", CustomerID : CustID , CustomerName : custName })
        })
        .then(response => response.json()) // This will still try to parse the response
        .then(data => {
            if (data.success) {

                selectCustomer.disabled = true;
                discardOrder.disabled = false;
                selectProduct.disabled = false;
                quantityInput.disabled = false;
                addBillButton.disabled = false;
                removeLastButton.disabled = false;
                generateBillButton.disabled = false;

                fetchBillSummary();
                showBillInfo(false);
                billSummary.style.display = 'block';
                this.disabled = true;

            } else {
                showMessage( error, 'error');
            }
        })
        .catch(error => {
            showMessage( error, 'error');
        });
    }

    function addBill() {

        const ProductID = selectProduct.value;
        const Quantity = parseInt(document.getElementById('quantity').value);
        const CustID = selectCustomer.value;

        if (!Quantity ||  Quantity < 1 ){
            showMessage('Please Enter A Quantity >= 1', 'error');
            return;
        }

        // Create the data object to send
        const data = {
            action: 'addBill',
            productID: ProductID,
            Quantity: Quantity,
            customerID: CustID
        };

        // Send the POST request using fetch
        fetch('BillManager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(responseData => {
            if (responseData.success) {
                showMessage("Product successfully added to Bill" , 'success' );
                fetchBillSummary();
            } else {
                showMessage("Failed to add product to Bill" , 'error' );
            }
        })
        .catch(error => {
            showMessage("An error occurred" , 'error' );
        });
    }

    function fetchBillSummary() {
        // Fetch the JSON response from the PHP script
        fetch('BillSummary.php')
        .then(response => response.json()) // Parse the response as JSON
        .then(data => {
            // Get the tbody element from the table with ID BillSummary
            const Billtbody = document.querySelector('#bill-summary tbody');

            // Clear the existing tbody content
            Billtbody.innerHTML = '';

            // Insert the fetched data (which contains the <td> elements) into the tbody
            Billtbody.innerHTML = data.data;
            document.getElementById('customer-name').innerText = `Customer Name: ${data.customerName}`;
            document.getElementById('order-id').innerText = `Order ID: ${data.uuid}`;
            document.getElementById('GrandTotal').innerText = `Grand Total: ${data.GrandTotal}`;

        })
        .catch(error => {
            showMessage('Error fetching Bill Summary:', 'error');
        });
    }

    function getCustName(){
        // Split the text by ' - ' and return the second part (name)
        return selectCustomer.options[selectCustomer.selectedIndex].innerText.split(' - ').at(-1).trim();
    }
    function showBillInfo(shouldShow) {
        // Select all elements with the class 'bill-info'
        const billInfoElements = document.querySelectorAll('.bill-info');

        // Set all elements to the  display
        billInfoElements.forEach(element => {
            element.style.display = shouldShow ? 'block' : 'none' ;
        });
    }
    function removeLast(){

        // Send the POST request using fetch
        fetch('BillManager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'removeLast' })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(responseData => {
            if (responseData.success) {
                showMessage("Product successfully removed from Bill" , 'success' );
                fetchBillSummary();
            } else {
                showMessage("Failed to remove product from Bill" , 'error' );
            }
        })
        .catch(error => {
            showMessage( error , 'error' );
        });

    }

    function discardBill(){

        const CustID = selectCustomer.value;

        // Send the POST request using fetch
        fetch('BillManager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'discardBill' ,  customerID: CustID })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(responseData => {
            if (responseData.success) {

                selectCustomer.disabled = false;
                createOrder.disabled = false;
                selectProduct.disabled = true;
                quantityInput.disabled = true;
                addBillButton.disabled = true;
                removeLastButton.disabled = true;
                generateBillButton.disabled = true;

                billSummary.style.display = 'none';
                this.disabled = true;

                showBillInfo(false);
                document.querySelector('#bill-summary tbody').innerHTML = '';
                document.getElementById('customer-name').innerText = '';
                document.getElementById('order-id').innerText = '';
                document.getElementById('GrandTotal').innerText = '';

                showMessage("Order Discarded Successfully" , 'success' );
            } else {
                showMessage("Failed To Discard Bill" , 'error' );
            }
        })
        .catch(error => {
            showMessage( error , 'error' );
        });
    }
    function generateBill(){

        const CustID = selectCustomer.value;

        // Send the POST request using fetch
        fetch('BillManager.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'generateBill' ,  customerID: CustID })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(responseData => {
            if (responseData.success) {

                selectCustomer.disabled = false;
                createOrder.disabled = false;
                selectProduct.disabled = true;
                quantityInput.disabled = true;
                addBillButton.disabled = true;
                removeLastButton.disabled = true;
                generateBillButton.disabled = true;
                discardOrder.disabled = true;

                this.disabled = true;

                showBillInfo(true);

                showMessage("Bill Generated Successfully" , 'success' );
            } else {
                showMessage( responseData.error , 'error' );
            }
        })
        .catch(error => {
            showMessage( error , 'error' );
        });
    }
});

</script>

</body>
</html>
<?php
}else{
     header("Location: index.php");
     exit();
}
 ?>
