<?php
session_start();

// Get the search query
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Get all products' sold counts
$sold_counts = getAllProductsSoldCount();

// If there's a search query, fetch matching products
if (!empty($search)) {
    $sql = "SELECT p.*, COALESCE(SUM(o.quantity), 0) as total_sold 
            FROM products p 
            LEFT JOIN orders o ON p.product_name = o.product_name 
            WHERE p.product_status = 'live' 
            AND (p.product_name LIKE ? OR p.product_description LIKE ? OR p.product_category LIKE ?)
            GROUP BY p.id 
            ORDER BY total_sold DESC";
    
    $search_term = "%{$search}%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - NEOFIT</title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            background-color: #fff;
            color: #1e1e1e;
        }

        .page-title {
            text-align: center;
            padding: 40px 0;
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .search-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .search-term {
            color: #55a39b;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            font-size: 18px;
            color: #666;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }

        .product-card {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            box-shadow: 0 1px 20px rgba(0, 0, 0, 0.1);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-link {
            text-decoration: none;
            color: inherit;
        }

        .product-image {
            width: 100%;
            height: 250px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-info {
            padding: 15px;
            text-align: left;
        }

        .product-name {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .product-price {
            font-size: 16px;
            font-weight: bold;
            color: #00a0a0;
            display: inline-block;
        }

        .product-sold {
            font-size: 14px;
            color: #999;
            float: right;
        }

        .browse-products {
            display: inline-block;
            padding: 12px 30px;
            border: 1px solid #000;
            background: transparent;
            color: #000;
            font-size: 16px;
            margin: 40px auto;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-transform: lowercase;
            display: block;
            width: fit-content;
        }

        .browse-products:hover {
            background: #000;
            color: #fff;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div class="search-header">
            <h1 class="page-title">Search Results</h1>
            <?php if (!empty($search)): ?>
                <p>Showing results for "<span class="search-term"><?php echo htmlspecialchars($search); ?></span>"</p>
            <?php endif; ?>
        </div>

        <?php if (empty($search)): ?>
            <div class="no-results">
                <p>Please enter a search term.</p>
                <a href="landing_page.php" class="browse-products">Browse Products</a>
            </div>
        <?php elseif ($result->num_rows === 0): ?>
            <div class="no-results">
                <p>No products found matching "<?php echo htmlspecialchars($search); ?>"</p>
                <a href="landing_page.php" class="browse-products">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php while($product = $result->fetch_assoc()): 
                    $id = $product['id'];
                    $productName = $product['product_name'];
                    $productPrice = $product['product_price'];
                    $photoFront = "Admin Pages/" . $product['photoFront'];
                    $link = 'product_detail.php?id=' . $id;
                    $sold_count = $product['total_sold'];
                    
                    // Format the sold count
                    $formatted_sold_count = $sold_count;
                    if ($sold_count >= 1000) {
                        $formatted_sold_count = number_format($sold_count/1000, 1) . 'k';
                    }
                ?>
                    <div class="product-card" id="product-box-<?php echo $id; ?>">
                        <a href="<?php echo $link; ?>" class="product-link">
                            <div class="product-image">
                                <img src="<?php echo $photoFront; ?>" alt="<?php echo $productName; ?>">
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo $productName; ?></h3>
                                <span class="product-price">₱ <?php echo $productPrice; ?></span>
                                <span class="product-sold"><?php echo $formatted_sold_count; ?> sold</span>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    // Update cart count
    function updateCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                    cartCount.style.display = data.count > 0 ? 'flex' : 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Update cart count on page load
    updateCartCount();
    </script>
</body>
</html>
