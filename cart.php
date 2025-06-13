<?php
session_start();
include 'db.php';


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart items
$sql = "SELECT c.*, p.product_name, p.product_price, p.photoFront 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - NEOFIT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cart-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .cart-items {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 20px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .item-size {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .item-price {
            font-weight: bold;
            color: #55a39b;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: 20px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
        }

        .quantity-input {
            width: 50px;
            height: 30px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .remove-btn {
            color: #ff4d4d;
            background: none;
            border: none;
            cursor: pointer;
            margin-left: 20px;
        }

        .cart-summary {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: bold;
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }

        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: #55a39b;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            background: #fff;
            border-radius: 8px;
        }

        .empty-cart p {
            margin-bottom: 20px;
            color: #666;
        }

        .continue-shopping {
            display: inline-block;
            padding: 10px 20px;
            background: #55a39b;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
     
    <div class="container">
        <h1 class="cart-title">Shopping Cart</h1>

        <?php if ($result->num_rows > 0): ?>
            <div class="cart-items">
                <?php 
                while ($item = $result->fetch_assoc()):
                    $subtotal = $item['quantity'] * $item['product_price'];
                    $total_amount += $subtotal;
                ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <img src="Admin Pages/<?php echo $item['photoFront']; ?>" alt="<?php echo $item['product_name']; ?>" class="item-image">
                        <div class="item-details">
                            <div class="item-name"><?php echo $item['product_name']; ?></div>
                            <div class="item-size">Size: <?php echo strtoupper($item['size']); ?></div>
                            <div class="item-price">₱<?php echo number_format($item['product_price'], 2); ?></div>
                        </div>
                        <div class="quantity-controls">
                            <button class="quantity-btn decrease">-</button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                            <button class="quantity-btn increase">+</button>
                        </div>
                        <button class="remove-btn">×</button>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₱<?php echo number_format($total_amount, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>₱<?php echo number_format($total_amount, 2); ?></span>
                </div>
                <button class="checkout-btn">Proceed to Checkout</button>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="landing_page.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartItems = document.querySelectorAll('.cart-item');

        cartItems.forEach(item => {
            const decreaseBtn = item.querySelector('.decrease');
            const increaseBtn = item.querySelector('.increase');
            const quantityInput = item.querySelector('.quantity-input');
            const removeBtn = item.querySelector('.remove-btn');
            const itemId = item.dataset.id;

            // Update quantity
            function updateQuantity(newQuantity) {
                const formData = new FormData();
                formData.append('cart_id', itemId);
                formData.append('quantity', newQuantity);

                fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_id: itemId,
                        quantity: newQuantity  // 👈 this was missing
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error updating quantity');
                        quantityInput.value = data.current_quantity || quantityInput.value;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating quantity');
                });

            }

            // Decrease quantity
            decreaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    updateQuantity(currentValue - 1);
                }
            });

            // Increase quantity
            increaseBtn.addEventListener('click', () => {
                const currentValue = parseInt(quantityInput.value);
                updateQuantity(currentValue + 1);
            });

            // Manual quantity input
            quantityInput.addEventListener('change', () => {
                let value = parseInt(quantityInput.value);
                if (isNaN(value) || value < 1) {
                    value = 1;
                }
                updateQuantity(value);
            });

            // Remove item
            removeBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to remove this item?')) {
                    fetch('remove_from_cart.php', {
                        method: 'POST',
                        body: JSON.stringify({ cart_id: itemId }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Error removing item');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error removing item');
                    });
                }
            });
        });

        // Proceed to checkout
        const checkoutBtn = document.querySelector('.checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => {
                window.location.href = 'checkout.php';
            });
        }
    });
    </script>
</body>
</html>