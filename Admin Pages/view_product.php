<?php
include '../db.php';

if (!isset($_GET['id'])) {
    header('Location: all_product_page.php');
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: all_product_page.php');
    exit;
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - View Product</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .product-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            padding: 20px;
        }

        .product-images {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .main-image {
            grid-column: 1 / -1;
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }

        .thumbnail {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        .thumbnail:hover {
            opacity: 0.8;
        }

        .product-info {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .product-info h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1em;
            color: #333;
        }

        .stock-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .stock-item {
            text-align: center;
        }

        .stock-label {
            font-size: 0.9em;
            color: #666;
        }

        .stock-value {
            font-size: 1.2em;
            font-weight: 500;
            color: #333;
            margin-top: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }

        .status-live {
            background-color: #d4edda;
            color: #155724;
        }

        .status-unpublished {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #000;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <h1>NEOFIT</h1>
            <span class="admin-tag">Admin</span>
        </div>
        <div class="user-icon">
            <i class="fas fa-user-circle"></i>
        </div>
    </header>
    
    <div class="container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li onclick="window.location.href='dashboard_page.php'">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </li>
                <li onclick="window.location.href='manage_order_details_page.php'">
                    <i class="fas fa-list"></i>
                    <span>Manage Orders</span>
                </li>
                <li onclick="window.location.href='customer_orders_page.php'">
                    <i class="fas fa-users"></i>
                    <span>Customer Orders</span>
                </li>
                <li class="active">
                    <i class="fas fa-tshirt"></i>
                    <span>All Products</span>
                </li>
                <li onclick="window.location.href='add_new_product_page.php'">
                    <i class="fas fa-plus-square"></i>
                    <span>Add New Product</span>
                </li>
                <li onclick="window.location.href='payments_page.php'">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </li>
                <li onclick="window.location.href='neocreds_page.php'">
                    <i class="fas fa-coins"></i>
                    <span>NeoCreds</span>
                </li>
                <li onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </li>
                <li onclick="window.location.href='inbox.php'">
                    <i class="fas fa-inbox"></i>
                    <span>Messages</span>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="content-card">
                <div class="product-details">
                    <div class="product-images">
                        <img src="<?php echo $product['photoFront']; ?>" alt="Main Product Image" class="main-image" id="mainImage">
                        <?php
                        $photos = [
                            $product['photo1'],
                            $product['photo2'],
                            $product['photo3'],
                            $product['photo4']
                        ];
                        
                        foreach ($photos as $photo) {
                            if (!empty($photo)) {
                                echo '<img src="' . $photo . '" alt="Product Thumbnail" class="thumbnail" onclick="updateMainImage(this.src)">';
                            }
                        }
                        ?>
                    </div>
                    
                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
                        
                        <div class="info-group">
                            <div class="info-label">Category</div>
                            <div class="info-value"><?php echo htmlspecialchars($product['product_category']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Price</div>
                            <div class="info-value">₱<?php echo number_format($product['product_price'], 2); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="status-badge <?php echo $product['product_status'] === 'live' ? 'status-live' : 'status-unpublished'; ?>">
                                    <?php echo ucfirst($product['product_status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Stock Information</div>
                            <div class="stock-info">
                                <div class="stock-item">
                                    <div class="stock-label">Small</div>
                                    <div class="stock-value"><?php echo $product['quantity_small']; ?></div>
                                </div>
                                <div class="stock-item">
                                    <div class="stock-label">Medium</div>
                                    <div class="stock-value"><?php echo $product['quantity_medium']; ?></div>
                                </div>
                                <div class="stock-item">
                                    <div class="stock-label">Large</div>
                                    <div class="stock-value"><?php echo $product['quantity_large']; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-back" onclick="window.location.href='all_product_page.php'">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </button>
                            <button class="btn btn-edit" onclick="window.location.href='edit_product.php?id=<?php echo $product['id']; ?>'">
                                <i class="fas fa-edit"></i> Edit Product
                            </button>
                            <button class="btn btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateMainImage(src) {
            document.getElementById('mainImage').src = src;
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch(`delete_product.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'all_product_page.php?deleted=1';
                    } else {
                        alert(data.message || 'Error deleting product');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting product');
                });
            }
        }
    </script>
</body>
</html> 