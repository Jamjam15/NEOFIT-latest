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

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .cart-title {
            font-size: 24px;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-count {
            background: #55a39b;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
        }

        .cart-items {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .select-all-container {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .select-all-label {
            font-size: 14px;
            color: #666;
            margin-left: 10px;
            font-weight: 500;
        }

        .cart-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: all 0.2s ease;
            align-items: center;
        }

        .cart-item:hover {
            background-color: #f8f9fa;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-checkbox {
            width: 18px;
            height: 18px;
            accent-color: #55a39b;
            cursor: pointer;
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-name {
            font-size: 16px;
            color: #333;
            font-weight: 500;
            text-decoration: none;
        }

        .item-name:hover {
            color: #55a39b;
        }

        .item-size {
            display: inline-block;
            padding: 4px 12px;
            background: #f8f9fa;
            border-radius: 20px;
            color: #666;
            font-size: 13px;
        }

        .item-price {
            font-weight: 600;
            color: #55a39b;
            font-size: 16px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            color: #666;
        }

        .quantity-btn:hover {
            background: #f8f9fa;
            border-color: #55a39b;
            color: #55a39b;
        }

        .quantity-btn:active {
            transform: scale(0.95);
        }

        .quantity-input {
            width: 50px;
            height: 30px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            -moz-appearance: textfield;
        }

        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input:focus {
            outline: none;
            border-color: #55a39b;
            box-shadow: 0 0 0 2px rgba(85, 163, 155, 0.1);
        }

        .remove-btn {
            color: #ff4d4d;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            border-radius: 4px;
        }

        .remove-btn:hover {
            background: #fff5f5;
            color: #ff0000;
        }

        .remove-btn:active {
            transform: scale(0.95);
        }

        .cart-summary {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .summary-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            color: #666;
            padding: 8px 0;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: 600;
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            color: #333;
        }

        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: #55a39b;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .checkout-btn:hover {
            background: #4a8f88;
            transform: translateY(-1px);
        }

        .checkout-btn:active {
            transform: translateY(0);
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .empty-cart-icon {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-cart p {
            margin-bottom: 20px;
            color: #666;
            font-size: 16px;
        }

        .continue-shopping {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #55a39b;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .continue-shopping:hover {
            background: #4a8f88;
            transform: translateY(-1px);
        }

        .continue-shopping:active {
            transform: translateY(0);
        }

        @media (max-width: 1024px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .cart-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
                padding: 20px 10px;
            }

            .item-image {
                margin: 0 auto;
            }

            .item-details {
                align-items: center;
            }

            .quantity-controls {
                justify-content: center;
                margin: 15px 0;
            }

            .remove-btn {
                margin: 10px auto;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
     
    <div class="container">
        <div class="cart-header">
            <h1 class="cart-title">
                <i class="fas fa-shopping-cart"></i>
                Shopping Cart
                <span class="cart-count"><?php echo $result->num_rows; ?> items</span>
            </h1>
        </div>

        <div class="cart-content">
            <div class="cart-items">
                <?php if ($result->num_rows > 0): ?>
                    <div class="select-all-container">
                        <input type="checkbox" id="select-all" class="item-checkbox">
                        <label for="select-all" class="select-all-label">Select All Items</label>
                    </div>

                    <?php while ($item = $result->fetch_assoc()): ?>
                        <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                            <input type="checkbox" class="item-checkbox" value="<?php echo $item['id']; ?>">
                            
                            <img src="Admin Pages/<?php echo $item['photoFront']; ?>" alt="<?php echo $item['product_name']; ?>" class="item-image">
                            
                            <div class="item-details">
                                <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" class="item-name">
                                    <?php echo $item['product_name']; ?>
                                </a>
                                <span class="item-size">Size: <?php echo strtoupper($item['size']); ?></span>
                                <span class="item-price">₱<?php echo number_format($item['product_price'], 2); ?></span>
                            </div>

                            <div class="quantity-controls">
                                <button class="quantity-btn decrease">-</button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                <button class="quantity-btn increase">+</button>
                            </div>

                            <button class="remove-btn">
                                <i class="fas fa-trash"></i>
                                Remove
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <p>Your cart is empty</p>
                        <a href="landing_page.php" class="continue-shopping">
                            <i class="fas fa-arrow-left"></i>
                            Continue Shopping
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="cart-summary">
                    <h2 class="summary-title">
                        <i class="fas fa-receipt"></i>
                        Order Summary
                    </h2>

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

                    <form id="checkout-form" action="checkout.php" method="GET">
                        <input type="hidden" name="cart_ids" id="selected-items-input">
                        <button type="submit" class="checkout-btn">
                            <i class="fas fa-lock"></i>
                            Proceed to Checkout
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const cartItems = document.querySelectorAll('.cart-item');
                    const checkoutForm = document.getElementById('checkout-form');
                    const selectAllCheckbox = document.getElementById('select-all');
                    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
                    const hiddenInput = document.getElementById('selected-items-input');

                    // QUANTITY + REMOVE BUTTONS
                    cartItems.forEach(item => {
                        const decreaseBtn = item.querySelector('.decrease');
                        const increaseBtn = item.querySelector('.increase');
                        const quantityInput = item.querySelector('.quantity-input');
                        const removeBtn = item.querySelector('.remove-btn');
                        const itemId = item.dataset.id;

                        function updateQuantity(newQuantity) {
                            fetch('update_cart.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    cart_id: itemId,
                                    quantity: newQuantity
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

                        // Event: Decrease quantity
                        decreaseBtn.addEventListener('click', () => {
                            const currentValue = parseInt(quantityInput.value);
                            if (currentValue > 1) {
                                updateQuantity(currentValue - 1);
                            }
                        });

                        // Event: Increase quantity
                        increaseBtn.addEventListener('click', () => {
                            const currentValue = parseInt(quantityInput.value);
                            updateQuantity(currentValue + 1);
                        });

                        // Event: Manual input
                        quantityInput.addEventListener('change', () => {
                            let value = parseInt(quantityInput.value);
                            if (isNaN(value) || value < 1) value = 1;
                            updateQuantity(value);
                        });

                        // Event: Remove item
                        removeBtn.addEventListener('click', () => {
                            if (confirm('Are you sure you want to remove this item?')) {
                                fetch('remove_from_cart.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ cart_id: itemId })
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

                    // SELECT ALL Functionality
                    selectAllCheckbox.addEventListener('change', function () {
                        itemCheckboxes.forEach(cb => cb.checked = this.checked);
                    });

                    itemCheckboxes.forEach(cb => {
                        cb.addEventListener('change', function () {
                            if (!this.checked) {
                                selectAllCheckbox.checked = false;
                            } else if ([...itemCheckboxes].every(cb => cb.checked)) {
                                selectAllCheckbox.checked = true;
                            }
                        });
                    });

                    // CHECKOUT Form Submission
                    checkoutForm.addEventListener('submit', function (e) {
                        const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
                                                .map(cb => cb.value);

                        if (selectedIds.length === 0) {
                            e.preventDefault();
                            alert('Please select at least one item to checkout.');
                            return;
                        }

                        hiddenInput.value = selectedIds.join(',');
                    });
                });
                </script>

</body>
</html>