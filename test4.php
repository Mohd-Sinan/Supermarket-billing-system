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

        .bill-summary {
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
        }
        #customer-content table, #customer-content .bill-summary {
            margin-top: 20px;
        }
	#deleteProductID,#deleteCustomerID {
	    padding: 10px;
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
                <option value="1">1 - Aashi Singh</option>
                <option value="2">2 - John Doe</option>
            </select>
        </div>

        <button>Create new Order for Customer</button>
        <button>Discard Order</button>

        <div class="form-controls">
            <label for="selectProduct">Select Product:</label>
            <select id="selectProduct">
                <option value="1">1 - Basmati Rice</option>
                <option value="2">2 - Chana Dal</option>
                <option value="3">3 - Ghee</option>
                <option value="4">4 - Masoor Dal</option>
            </select>
        </div>

        <div class="form-controls">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" value="1" min="1">
        </div>

        <button>Add to Bill</button>
        <button>Remove Last</button>
        <button>Generate Bill</button>
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
        <div class="bill-summary">
            <h4>---------- Gurukul Supermarket ----------</h4>
            <p>Customer Name: Aashi Singh</p>
            <p>Order ID: 1</p>
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
                    <tr>
                        <td>1</td>
                        <td>Basmati Rice</td>
                        <td>2</td>
                        <td>999.98</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Ghee</td>
                        <td>2</td>
                        <td>1199.98</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Masoor Dal</td>
                        <td>1</td>
                        <td>249.99</td>
                    </tr>
                </tbody>
            </table>
            <p>Grand Total: 2449.95</p>
        </div>
    </div>

    <!-- Products Information Tab -->
    <div class="tab-content" id="products-content" style="display: none;">
        <h3>Product Information</h3>
        <div class="input-group">
            <label for="name">Name:</label>
            <input type="text" id="name">
        </div>
        <div class="input-group">
            <label for="price">Price:</label>
            <input type="number" id="price">
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

        document.getElementById('refresh').addEventListener('click', loadProducts);
        document.getElementById('refreshProduct').addEventListener('click', loadProducts);
        document.getElementById('refreshCustomer').addEventListener('click', loadCustomers);

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
});

    // Add more functionality here for adding/deleting products and handling customer orders
</script>

</body>
</html>
