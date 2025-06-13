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
            min-height: calc(100vh - 200px);
            position: relative;
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
<<<<<<< caleb
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
=======
            gap: 30px;
            align-items: start;
        }

        .cart-items {
            min-width: 0;
            flex: 1;
        }

        .cart-item {
            display: grid;
            grid-template-columns: auto 100px 1fr auto auto;
            gap: 20px;
            align-items: center;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            position: relative;
            margin-bottom: 15px;
            min-height: 140px;
>>>>>>> main
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
<<<<<<< caleb
            accent-color: #55a39b;
            cursor: pointer;
=======
            cursor: pointer;
            margin: 0;
>>>>>>> main
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
<<<<<<< caleb
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
=======
            border-radius: 8px;
>>>>>>> main
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
<<<<<<< caleb
=======
            min-width: 0;
>>>>>>> main
        }

        .item-name {
            font-size: 16px;
<<<<<<< caleb
            color: #333;
            font-weight: 500;
            text-decoration: none;
=======
            font-weight: 500;
            color: #333;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
>>>>>>> main
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
<<<<<<< caleb
=======
            min-width: 120px;
            justify-content: center;
>>>>>>> main
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
<<<<<<< caleb
            border-radius: 4px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            color: #666;
=======
            font-size: 16px;
            color: #666;
            transition: all 0.2s ease;
>>>>>>> main
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
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
<<<<<<< caleb
            -moz-appearance: textfield;
=======
>>>>>>> main
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
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 50%;
            color: #666;
            font-size: 12px;
            cursor: pointer;
<<<<<<< caleb
            padding: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            border-radius: 4px;
=======
            transition: all 0.2s ease;
            padding: 0;
>>>>>>> main
        }

        .remove-btn:hover {
            background: #fff5f5;
<<<<<<< caleb
            color: #ff0000;
=======
            border-color: #ff4d4d;
            color: #ff4d4d;
>>>>>>> main
        }

        .remove-btn:active {
            transform: scale(0.95);
<<<<<<< caleb
=======
        }

        .remove-btn i {
            display: none;
>>>>>>> main
        }

        .cart-summary {
            position: sticky;
            top: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 20px;
<<<<<<< caleb
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
=======
            height: fit-content;
            min-width: 350px;
        }

        .summary-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            height: 40px;
        }

        .summary-content {
            margin-bottom: 20px;
            min-height: 120px;
>>>>>>> main
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
<<<<<<< caleb
            margin-bottom: 12px;
            font-size: 14px;
            color: #666;
            padding: 8px 0;
=======
            align-items: center;
            margin-bottom: 15px;
            min-height: 24px;
>>>>>>> main
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
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #4CAF50;
            border: 1px solid #4CAF50;
            color: #fff;
<<<<<<< caleb
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
=======
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            justify-content: center;
            min-height: 45px;
        }

        .checkout-btn:hover {
            background: #45a049;
            border-color: #45a049;
        }

        .checkout-btn:active {
            transform: scale(0.95);
>>>>>>> main
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
<<<<<<< caleb
=======
                min-width: 100%;
>>>>>>> main
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
<<<<<<< caleb
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
=======
                grid-template-columns: auto 80px 1fr auto;
                gap: 15px;
                padding: 15px;
            }

            .item-image {
                width: 80px;
                height: 80px;
            }

            .quantity-controls {
                min-width: 100px;
            }
        }

        .cart-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
            border-top: 1px solid #eee;
            min-height: 60px;
            width: 100%;
            position: relative;
        }

        .cart-actions-left {
            display: flex;
            align-items: center;
            gap: 20px;
            min-width: 300px;
            max-width: 300px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            position: relative;
            z-index: 1;
        }

        .cart-actions-right {
            display: flex;
            align-items: center;
            min-width: 120px;
            max-width: 120px;
            position: relative;
            z-index: 2;
        }

        .select-all-container {
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            min-width: 140px;
            max-width: 140px;
            position: relative;
        }

        .select-all-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }

        .selected-count {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            white-space: nowrap;
            min-width: 120px;
            max-width: 120px;
            text-align: left;
            position: relative;
        }

        .delete-selected {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #fff;
            border: 1px solid #ff4d4d;
            color: #ff4d4d;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            width: 100%;
            justify-content: center;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
        }

        .delete-selected.show {
            display: flex;
        }

        @media (max-width: 768px) {
            .cart-actions {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .cart-actions-left {
                min-width: 100%;
                max-width: 100%;
                justify-content: space-between;
            }

            .cart-actions-right {
                min-width: 100%;
                max-width: 100%;
            }

            .select-all-container {
                min-width: 120px;
                max-width: 120px;
            }

            .selected-count {
                min-width: 100px;
                max-width: 100px;
            }

            .delete-selected {
                position: relative;
                transform: none;
                top: auto;
                right: auto;
>>>>>>> main
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
     
    <?php include 'header.php'; ?>
     
    <div class="container">
        <div class="cart-header">
            <h1 class="cart-title">
                <i class="fas fa-shopping-cart"></i>
                Shopping Cart
            </h1>
        </div>

        <div class="cart-content">
            <div class="cart-items">
                <?php if ($result->num_rows > 0): ?>
<<<<<<< caleb
                    <div class="select-all-container">
                        <input type="checkbox" id="select-all" class="item-checkbox">
                        <label for="select-all" class="select-all-label">Select All Items</label>
                    </div>

=======
>>>>>>> main
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

<<<<<<< caleb
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
=======
                            <button class="remove-btn" title="Remove item">×</button>
                        </div>
                    <?php endwhile; ?>

                    <div class="cart-actions">
                        <div class="cart-actions-left">
                            <div class="select-all-container">
                                <input type="checkbox" id="select-all" class="item-checkbox">
                                <label for="select-all" class="select-all-label">Select All Items</label>
                            </div>
                            <span class="selected-count">0 items selected</span>
                        </div>
                        <div class="cart-actions-right">
                            <button class="delete-selected" id="delete-selected">
                                <i class="fas fa-trash"></i>
                                Delete Selected
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
>>>>>>> main
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
<<<<<<< caleb
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
=======
                    <div class="summary-header">
                        <h2>Order Summary</h2>
                    </div>
                    <div class="summary-content">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="summary-subtotal">₱0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Total</span>
                            <span id="summary-total">₱0.00</span>
                        </div>
                        <form id="checkout-form" action="checkout.php" method="POST">
                            <input type="hidden" name="selected_items" id="selected-items-input" value="">
                            <button type="submit" class="checkout-btn" id="checkout-btn" disabled>
                                <i class="fas fa-shopping-cart"></i>
                                Proceed to Checkout
                            </button>
                        </form>
                    </div>
>>>>>>> main
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
<<<<<<< caleb
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
=======
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const itemCheckboxes = document.querySelectorAll('.cart-item .item-checkbox');
            const selectedCount = document.querySelector('.selected-count');
            const deleteSelectedBtn = document.getElementById('delete-selected');
            const summarySubtotal = document.getElementById('summary-subtotal');
            const summaryTotal = document.getElementById('summary-total');
            const selectedItemsInput = document.getElementById('selected-items-input');
            let totalItems = itemCheckboxes.length;

            // Calculate selected items total
            function calculateSelectedTotal() {
                const selectedItems = document.querySelectorAll('.cart-item .item-checkbox:checked');
                let total = 0;
                let totalItems = 0;

                selectedItems.forEach(checkbox => {
                    const cartItem = checkbox.closest('.cart-item');
                    // Remove currency symbol and commas, then parse as float
                    const priceText = cartItem.querySelector('.item-price').textContent;
                    const price = parseFloat(priceText.replace(/[₱,]/g, '').trim());
                    const quantity = parseInt(cartItem.querySelector('.quantity-input').value);
                    
                    // Calculate item total and add to running total
                    const itemTotal = price * quantity;
                    total += itemTotal;
                    totalItems += quantity;
                });

                // Update selected count with total quantity
                selectedCount.textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''} selected`;

                return total;
            }

            // Update summary
            function updateSummary() {
                const total = calculateSelectedTotal();
                // Format total with 2 decimal places and thousands separator
                const formattedTotal = total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                summarySubtotal.textContent = `₱${formattedTotal}`;
                summaryTotal.textContent = `₱${formattedTotal}`;
            }

            // Update select all checkbox state
            function updateSelectAllState() {
                const checkedItems = document.querySelectorAll('.cart-item .item-checkbox:checked').length;
                selectAllCheckbox.checked = checkedItems === totalItems;
                selectAllCheckbox.indeterminate = checkedItems > 0 && checkedItems < totalItems;
            }

            // Update selected items input for checkout
            function updateSelectedItemsInput() {
                const selectedItems = Array.from(document.querySelectorAll('.cart-item .item-checkbox:checked'))
                    .map(checkbox => checkbox.value);
                selectedItemsInput.value = JSON.stringify(selectedItems);
            }

            // Update selected count and delete button
            function updateSelectedCount() {
                const checkedItems = document.querySelectorAll('.cart-item .item-checkbox:checked');
                const checkoutBtn = document.getElementById('checkout-btn');
                deleteSelectedBtn.style.display = checkedItems.length > 0 ? 'flex' : 'none';
                checkoutBtn.disabled = checkedItems.length === 0;
                updateSummary(); // This will update the count with total quantity
                updateSelectedItemsInput();
            }

            // Handle select all checkbox
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelectedCount();
            });

            // Handle individual checkboxes
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectAllState();
                    updateSelectedCount();
                });
            });

            // Handle quantity controls
            document.querySelectorAll('.cart-item').forEach(item => {
                const quantityInput = item.querySelector('.quantity-input');
                const decreaseBtn = item.querySelector('.decrease');
                const increaseBtn = item.querySelector('.increase');
                const cartId = item.getAttribute('data-cart-id');

                // Handle decrease button
                decreaseBtn.addEventListener('click', function() {
                    const currentValue = parseInt(quantityInput.value);
                    if (currentValue > 1) {
                        quantityInput.value = currentValue - 1;
                        updateCartQuantity(cartId, quantityInput.value);
                        updateSummary(); // Update summary immediately
                    }
                });

                // Handle increase button
                increaseBtn.addEventListener('click', function() {
                    const currentValue = parseInt(quantityInput.value);
                    if (currentValue < 10) {
                        quantityInput.value = currentValue + 1;
                        updateCartQuantity(cartId, quantityInput.value);
                        updateSummary(); // Update summary immediately
                    }
                });

                // Handle direct input
                quantityInput.addEventListener('input', function() {
                    let newValue = parseInt(this.value);
                    if (isNaN(newValue) || newValue < 1) {
                        newValue = 1;
                    } else if (newValue > 10) {
                        newValue = 10;
                    }
                    this.value = newValue;
                    updateCartQuantity(cartId, newValue);
                    updateSummary(); // Update summary immediately
                });

                // Handle change event
                quantityInput.addEventListener('change', function() {
                    let newValue = parseInt(this.value);
                    if (isNaN(newValue) || newValue < 1) {
                        newValue = 1;
                    } else if (newValue > 10) {
                        newValue = 10;
                    }
                    this.value = newValue;
                    updateCartQuantity(cartId, newValue);
                    updateSummary(); // Update summary immediately
                });

                // Prevent non-numeric input
                quantityInput.addEventListener('keypress', function(e) {
                    if (!/[0-9]/.test(e.key)) {
                        e.preventDefault();
                    }
                });
            });

            // Update cart quantity in database
            function updateCartQuantity(cartId, quantity) {
                // Ensure cartId and quantity are valid
                if (!cartId || !quantity) {
                    console.error('Invalid cartId or quantity:', { cartId, quantity });
                    return;
                }

                fetch('update_cart.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        cart_id: parseInt(cartId),
                        quantity: parseInt(quantity)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.message || 'Error updating quantity');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating quantity');
                });
            }

            // Handle delete selected
            deleteSelectedBtn.addEventListener('click', function() {
                const selectedItems = Array.from(document.querySelectorAll('.cart-item .item-checkbox:checked'))
                    .map(checkbox => checkbox.value);
                
                if (selectedItems.length > 0) {
                    if (confirm('Are you sure you want to delete the selected items?')) {
                        // Create FormData object
                        const formData = new FormData();
                        formData.append('items', JSON.stringify(selectedItems));

                        fetch('remove_from_cart.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                alert('Selected items have been removed from your cart.');
                                
                                // Reload the page to show updated cart
                                window.location.reload();
                            } else {
                                alert(data.message || 'Error removing items. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error removing items. Please try again.');
                        });
                    }
                } else {
                    alert('Please select items to delete.');
                }
            });

            // Handle checkout form submission
            document.getElementById('checkout-form').addEventListener('submit', function(e) {
                const selectedItems = document.querySelectorAll('.cart-item .item-checkbox:checked');
                if (selectedItems.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one item to checkout');
                    return;
                }
                updateSelectedItemsInput();
            });

            // Handle remove button
            document.querySelectorAll('.remove-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const cartItem = this.closest('.cart-item');
                    const cartId = cartItem.getAttribute('data-id');
                    
                    if (confirm('Are you sure you want to remove this item from your cart?')) {
                        const formData = new FormData();
                        formData.append('items', JSON.stringify([cartId]));

                        fetch('remove_from_cart.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                cartItem.remove();
                                updateSummary();
                                updateSelectAllState();
                                updateSelectedCount();
                                
                                // If no items left, reload page to show empty cart
                                if (document.querySelectorAll('.cart-item').length === 0) {
                                    window.location.reload();
                                }
                            } else {
                                alert(data.message || 'Error removing item. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error removing item. Please try again.');
                        });
                    }
                });
            });

            // Initialize counts and summary
            updateSelectAllState();
            updateSelectedCount();
            updateSummary();
        });
    </script>
>>>>>>> main

</body>
</html>