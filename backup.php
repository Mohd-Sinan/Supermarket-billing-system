<?php
session_start();

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
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
            border: 1px solid #ccc;
            border-bottom: none;
        }
        .tab.active {
            background-color: #fff;
            border-bottom: 1px solid #fff;
        }
        .tab-content {
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
            overflow: scroll;
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
        #customer-content table, #customer-content #bill-summary {
            margin-top: 20px;
        }
        #deleteProductID,#deleteCustomerID {
            padding: 10px;
        }
	.notification {
	    position: fixed;
	    top: 20px;
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
    </style>
</head>
<body>

<div class="container">
    <div class="tabs">
        <div class="tab active" id="billing-tab">Billing</div>
        <div class="tab" id="products-tab">Products</div>
        <div class="tab" id="admin-tab">Customer</div> <!-- New Admin Tab -->
    </div>

<!-- Customer Information Tab -->
    <div class="tab-content" id="billing-content">
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
            <input type="number" id="quantity" value="1" min="1" disabled>
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
            <h4>---------- Gurukul Supermarket ----------</h4>
            <p>Customer Name: Aashi Singh</p>
            <p id="order-id">Order ID: </p>
            <p>Date: <?php echo date('d-m-Y'); ?></p>
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
            <p>Grand Total: 0</p>
        </div>
    </div>

    <!-- Products Information Tab -->
    <div class="tab-content" id="products-content" style="display: none;">
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
<!-- Admin Information Tab (For Customer Management) -->
<div class="tab-content" id="admin-content" style="display: none;">
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

<script>

document.addEventListener('DOMContentLoaded', function() {
        loadProducts();
        loadCustomers();

        document.getElementById('new-order').addEventListener('click', CreateOrder);
        document.getElementById('refresh').addEventListener('click', loadProducts);
        document.getElementById('refreshProduct').addEventListener('click', loadProducts);
        document.getElementById('refreshCustomer').addEventListener('click', loadCustomers);

/*
        // Load products function
        function loadProducts() {
            fetch('fetch_products.php')
                .then(response => response.text()) // Get response as text
                .then(data => {
                    const productTableBody = document.querySelector('#products-content tbody');
                    productTableBody.innerHTML = data; // Insert the HTML directly

                    const billingTableBody = document.querySelector('#billing-content tbody');
                    billingTableBody.innerHTML = data; // Insert the HTML directly into the billing table
                })
                .catch(error => console.error('Error loading products:', error));
        }
*/
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
            const productDropdown = document.getElementById('selectProduct');
            const billingTableBody = document.querySelector('#billing-content tbody');

            // Clear any existing rows in the product table and dropdown
            productTableBody.innerHTML = '';
            productDropdown.innerHTML = '';

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
                productDropdown.appendChild(option);
            });

            //Copy producttable to billingtable
            billingTableBody.innerHTML = productTableBody.innerHTML;

        })
        .catch(error => console.error('Error loading products:', error));
}
/*
        // Load customers function
        function loadCustomers() {
            fetch('fetch_customers.php')
                .then(response => response.text()) // Get response as text
                .then(data => {
                    const customerTableBody = document.querySelector('#admin-content tbody');
                    customerTableBody.innerHTML = data; // Insert the HTML directly
                })
                .catch(error => console.error('Error loading customers:', error));
        }
*/
function loadCustomers() {
    fetch('fetch_customers.php')
        .then(response => response.json()) // Expect a JSON response
        .then(data => {
            if (data.error) {
                alert('Error loading customers:', data.error);
                return;
            }

            // Get references to the product table body and product dropdown
            const customerTableBody = document.querySelector('#admin-content tbody');
            const customerDropdown = document.getElementById('selectCustomer');

            // Clear any existing rows in the product table and dropdown
            customerTableBody.innerHTML = '';
            customerDropdown.innerHTML = '';

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
                customerDropdown.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading customers:', error));
}
    document.getElementById('products-tab').addEventListener('click', function() {
        document.getElementById('products-content').style.display = 'block';
        document.getElementById('billing-content').style.display = 'none';
        document.getElementById('admin-content').style.display = 'none';
        this.classList.add('active');
        document.getElementById('billing-tab').classList.remove('active');
        document.getElementById('admin-tab').classList.remove('active');
    });

    document.getElementById('billing-tab').addEventListener('click', function() {
        document.getElementById('products-content').style.display = 'none';
        document.getElementById('billing-content').style.display = 'block';
        document.getElementById('admin-content').style.display = 'none';
        this.classList.add('active');
        document.getElementById('products-tab').classList.remove('active');
        document.getElementById('admin-tab').classList.remove('active');
    });

    document.getElementById('admin-tab').addEventListener('click', function() {
        document.getElementById('admin-content').style.display = 'block';
        document.getElementById('billing-content').style.display = 'none';
        document.getElementById('products-content').style.display = 'none';
        this.classList.add('active');
        document.getElementById('billing-tab').classList.remove('active');
        document.getElementById('products-tab').classList.remove('active');
    });

//notification control
const showMessage = (message, type = 'info') => {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
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

function CreateOrder(){
    const selectCustomer = document.getElementById('selectCustomer');
    const selectProduct = document.getElementById('selectProduct');
    const discardOrder = document.getElementById('discard-order');
    const quantityInput = document.getElementById('quantity');
    const addBillButton = document.getElementById('add-bill');
    const removeLastButton = document.getElementById('remove-last');
    const generateBillButton = document.getElementById('generate-bill');
    const billSummary = document.getElementById('bill-summary');

    selectCustomer.disabled = true
    discardOrder.disabled = false;;
    selectProduct.disabled = false;
    quantityInput.disabled = false;
    addBillButton.disabled = false;
    removeLastButton.disabled = false;
    generateBillButton.disabled = false;

    const CustID = selectCustomer.value;

    fetch('uuid.php' , {
         method: 'POST', // Use GET method to fetch UUID
         headers: {
                    'Content-Type': 'application/json',
                },
         body: JSON.stringify({ action: "initBill", CustomerID : CustID })
    })
    .then(response => response.json()) // This will still try to parse the response
    .then(data => {
        if (data.success) {
            document.getElementById('order-id').innerText = `Order ID: ${data.uuid}`;
        } else {
            document.getElementById('order-id').innerText = 'Order ID could not be generated.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred.', 'error');
    });

    billSummary.style.display = 'block';
    this.disabled = true;;
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