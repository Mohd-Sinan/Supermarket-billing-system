<?php
session_start();

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

    if (!isset($_SESSION['notAdmin']) || $_SESSION['notAdmin'] === true) {
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
            margin:0;
            padding:10px;
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
        .tabs {
            display: flex;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f1f1f1;
            border-radius: 5px 5px 0px 0px;
            border: 1px solid #ccc;
            border-bottom: none;
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }
        .tab.active {
            background-color: #fff;
            border-bottom: 1px solid #fff;
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
        #refresh , #refreshProduct , #refreshCustomer {
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
    <div class="tabs">
        <div class="tab active" id="billing-tab">Billing</div>
        <div class="tab" id="products-tab">Products</div>
        <div class="tab" id="customer-tab">Customer</div>
    </div>

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

    <!-- Products Information Tab -->
    <div class="tab-content" id="products-content" style="display: none;">
        <button class="logout" onclick="window.location.href='logout.php'" type="button">Logout</button>
        <h3>Product Information</h3>
        <div class="input-group">
            <label for="productName">Name:</label>
            <input type="text" id="productName">
        </div>
        <div class="input-group">
            <label for="productPrice">Price:</label>
            <input type="number" id="productPrice">
        </div>
        <button id="addProduct">Add Product</button>
        <button id="refreshProduct">Refresh</button>
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
        <div class="input-group">
            <input type="number" id="deleteProductID" min="1">
            <button id="deleteProduct">Delete</button>
        </div>
    </div>
    <!-- Customer Information Tab  -->
    <div class="tab-content" id="customer-content" style="display: none;">
        <button class="logout" onclick="window.location.href='logout.php'" type="button">Logout</button>
        <h3>Customer Management</h3>
        <div class="input-group">
            <label for="customerName">Name:</label>
            <input type="text" id="customerName">
        </div>
        <div class="input-group">
            <label for="customerAddress">Address:</label>
            <input type="text" id="customerAddress">
        </div>
        <button id="addCustomer">Add Customer</button>
        <button id="refreshCustomer">Refresh</button>
        <div class="fixTableHead">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="input-group">
            <input type="number" id="deleteCustomerID" min="1">
            <button id="deleteCustomer">Delete</button>
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
    document.getElementById('refreshProduct').addEventListener('click', loadProducts);
    document.getElementById('refreshCustomer').addEventListener('click', loadCustomers);

    function loadProducts() {
        fetch('fetch_products.php')
            .then(response => response.json()) // Expect a JSON response
            .then(data => {
                if (data.error) {
                    console.error('Error loading products:', data.error);
                    return;
                }

                // Get references to the product table body and product dropdown
                const productTableBody = document.querySelector('#products-content tbody');
                const billingTableBody = document.querySelector('#billing-content tbody');

                // Clear any existing rows in the product table and dropdown
                productTableBody.innerHTML = '';
                selectProduct.innerHTML = '';

                if (data.length === 0) {
                    productTableBody.innerHTML = '<td colspan="3" style="text-align: center;">No Products Found</td>';
                } else {

                    // Loop through each product returned from the server
                    data.forEach(product => {

                        // Add a row to the product table
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${product.ProductID}</td>
                            <td>${product.Name}</td>
                            <td>${product.Price}</td>
                        `;
                        productTableBody.appendChild(row);


                        // Add an option to the product dropdown
                        const option = document.createElement('option');
                        option.value = product.ProductID;
                        option.textContent = `${product.ProductID} - ${product.Name}`;
                        selectProduct.appendChild(option);
                    });
                }

                //Copy producttable to billingtable
                billingTableBody.innerHTML = productTableBody.innerHTML;

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

                // Get references to the product table body and product dropdown
                const customerTableBody = document.querySelector('#customer-content tbody');

                // Clear any existing rows in the product table and dropdown
                customerTableBody.innerHTML = '';

                //Default Customer
                selectCustomer.innerHTML = '<option value="-1">Unknown</option>';

                if (data.length === 0) {
                    customerTableBody.innerHTML = '<td colspan="3" style="text-align: center;">No Customers Found</td>';
                } else {
                    // Loop through each product returned from the server
                    data.forEach(customer => {

                        // Add a row to the product table
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${customer.CustomerID}</td>
                            <td>${customer.Name}</td>
                            <td>${customer.Address}</td>
                        `;
                        customerTableBody.appendChild(row);

                        // Add an option to the product dropdown
                        const option = document.createElement('option');
                        option.value = customer.CustomerID;
                        option.textContent = `${customer.CustomerID} - ${customer.Name}`;
                        selectCustomer.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error loading customers:', error));
    }

    document.getElementById('products-tab').addEventListener('click', function() {
        document.getElementById('products-content').style.display = 'block';
        document.getElementById('billing-content').style.display = 'none';
        document.getElementById('customer-content').style.display = 'none';
        this.classList.add('active');
        document.getElementById('billing-tab').classList.remove('active');
        document.getElementById('customer-tab').classList.remove('active');
    });

    document.getElementById('billing-tab').addEventListener('click', function() {
        document.getElementById('products-content').style.display = 'none';
        document.getElementById('billing-content').style.display = 'block';
        document.getElementById('customer-content').style.display = 'none';
        this.classList.add('active');
        document.getElementById('products-tab').classList.remove('active');
        document.getElementById('customer-tab').classList.remove('active');
    });

    document.getElementById('customer-tab').addEventListener('click', function() {
        document.getElementById('customer-content').style.display = 'block';
        document.getElementById('billing-content').style.display = 'none';
        document.getElementById('products-content').style.display = 'none';
        this.classList.add('active');
        document.getElementById('billing-tab').classList.remove('active');
        document.getElementById('products-tab').classList.remove('active');
    });

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


    //Add product
    document.getElementById('addProduct').addEventListener('click', function() {
        // Get the product name and price from the input fields
        const productName = document.getElementById('productName').value;
        const productPrice = document.getElementById('productPrice').value;

        // Check if both name and price are provided
        if (!productName || !productPrice) {
            alert('Please fill out both fields.');
            return;
        }

        // Create the data object to send in the POST request
        const productData = {
            action: "add",
            name: productName,
            price: productPrice
        };

        // Send the product data using fetch with POST method
        fetch('Products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(productData) // Convert data to JSON string for POST
        })
        .then(response => response.json()) // Get response as JSON
        .then(data => {
            if (data.success) {
                showMessage('Product added successfully!', 'success');
                document.getElementById('productName').value = '';  // Clear the input fields
                document.getElementById('productPrice').value = '';
                loadProducts(); // Refresh the product list
            } else {
                alert('Failed to add product.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });

    });

    //Delete Product
    document.getElementById('deleteProduct').addEventListener('click', function() {
        const productId = document.getElementById('deleteProductID').value;

        if (!productId) {
            showMessage('Please provide a valid product ID.', 'error');
            return;
        }

        if (confirm('Are you sure you want to delete this product?')) {
            fetch('Products.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: "delete", id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Product deleted successfully!', 'success');
                    document.getElementById('deleteProductID').value = '';
                    loadProducts(); // Refresh the product list
                } else {
                    showMessage('Error: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred.', 'error');
            });
        }
    });

    // Add Customer
    document.getElementById('addCustomer').addEventListener('click', function() {
        const customerName = document.getElementById('customerName').value;
        const customerAddress = document.getElementById('customerAddress').value;

        if (!customerName || !customerAddress) {
            alert('Please fill out both fields.');
            return;
        }

        const customerData = {
	    action: 'add',
            name: customerName,
            address: customerAddress
        };

        fetch('Customers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(customerData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Customer added successfully!' , 'success');
                document.getElementById('customerName').value = '';
                document.getElementById('customerAddress').value = '';
                loadCustomers();
            } else {
                showMessage('Failed to add customer.' , 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred.', 'error');
        });

    });


    // Delete Customer
    document.getElementById('deleteCustomer').addEventListener('click', function() {
        const customerId = document.getElementById('deleteCustomerID').value;

        if (!customerId) {
            alert('Please provide a valid customer ID.');
            return;
        }

        if (confirm('Are you sure you want to delete this customer?')) {
            fetch('Customers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: "delete" , id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Customer deleted successfully!' , 'success');
                    document.getElementById('deleteCustomerID').value = '';
                    loadCustomers(); // Refresh the customer list
                } else {
                    showMessage('Error: ' + data.error , 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred.', 'error');
            });

        }
    });

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
