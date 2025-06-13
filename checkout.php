<?php
session_start();
include 'db.php';
include 'includes/address_functions.php';

$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_email = $_SESSION['email'];


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's address
$address_data = get_user_address($user_id, $conn);
$address = $address_data ? get_complete_address($address_data) : '';

// Get user's contact
$stmt = $conn->prepare("SELECT contact FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($contact);
$stmt->fetch();
$stmt->close();

// Check if we're checking out a specific cart item or the entire cart
$cart_id = $_GET['cart_id'] ?? null;

// Get selected items from POST data
$selected_items = isset($_POST['selected_items']) ? json_decode($_POST['selected_items'], true) : [];

if (empty($selected_items)) {
    header('Location: cart.php');
    exit;
}

// Get selected cart items with their details
$placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
$sql = "SELECT c.*, p.product_name, p.product_price, p.photoFront 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id IN ($placeholders) AND c.user_id = ?";

$stmt = $conn->prepare($sql);
$params = array_merge($selected_items, [$user_id]);
$types = str_repeat('i', count($selected_items)) . 'i';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$total_amount = 0;
$total_items = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT - Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            grid-column: 1 / -1;
        }

        .checkout-title {
            font-size: 24px;
            font-weight: bold;
        }

        .checkout-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #000;
        }

        .address-info, .contact-info {
            margin-bottom: 20px;
        }

        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #666;
        }

        .info-value {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .error-message {
            color: #ff4d4d;
            margin-top: 5px;
        }

        .order-items {
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-size {
            color: #666;
            font-size: 14px;
        }

        .item-price {
            color: #55a39b;
            font-weight: bold;
        }

        .payment-method {
            margin-bottom: 20px;
        }

        .payment-select {
            width: 100%;
            padding: 12px 35px 12px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
            background-color: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23666' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .payment-select:focus {
            border-color: #4a90e2;
            outline: none;
        }

        #neocreds-balance-display {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        #neocreds-balance-display .balance-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        #balance-amount {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            display: block;
        }

        #neocreds-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }

        #neocreds-summary .summary-row {
            font-size: 14px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #neocreds-summary .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 12px;
            border-top: 1px solid #ddd;
        }

        .deduction-amount {
            color: #dc3545;
            font-weight: bold;
        }

        #remaining-balance {
            font-size: 18px;
            font-weight: bold;
        }

        #remaining-balance.positive {
            color: #28a745;
        }

        #remaining-balance.negative {
            color: #dc3545;
        }

        .order-summary {
            position: sticky;
            top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .summary-total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .place-order-btn {
            width: 100%;
            padding: 15px;
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        .place-order-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .place-order-btn:not(:disabled):hover {
            opacity: 0.9;
        }

        .edit-link {
            color: #55a39b;
            text-decoration: none;
            font-size: 14px;
            margin-left: 10px;
        }

        .edit-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="checkout-title">Checkout</h1>
        </div>

        <div class="main-content">
            <div class="checkout-section">
                <h2 class="section-title">Shipping Information</h2>
                <div class="address-info">
                    <div class="info-label">
                        Delivery Address
                        <a href="user-settings.php" class="edit-link">Edit</a>
                    </div>
                    <div class="info-value">
                        <?php if ($address): ?>
                            <?php echo htmlspecialchars($address); ?>
                        <?php else: ?>
                            <span class="error-message">Please add your delivery address in your profile settings.</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="contact-info">
                    <div class="info-label">
                        Contact Number
                        <a href="user-settings.php" class="edit-link">Edit</a>
                    </div>
                    <div class="info-value">
                        <?php if ($contact): ?>
                            <?php echo htmlspecialchars($contact); ?>
                        <?php else: ?>
                            <span class="error-message">Please add your contact number in your profile settings.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="section-title">Order Items</h2>
                <div class="order-items">
                    <?php 
                    while ($item = $result->fetch_assoc()):
                        $subtotal = $item['quantity'] * $item['product_price'];
                        $total_amount += $subtotal;
                        $total_items += $item['quantity'];
                    ?>
                        <div class="order-item">
                            <img src="Admin Pages/<?php echo $item['photoFront']; ?>" alt="<?php echo $item['product_name']; ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?php echo $item['product_name']; ?></div>
                                <div class="item-size">Size: <?php echo strtoupper($item['size']); ?></div>
                                <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                                <div class="item-price">₱<?php echo number_format($item['product_price'], 2); ?></div>
                                <div class="item-subtotal">Subtotal: ₱<?php echo number_format($subtotal, 2); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="checkout-section">
                <h2 class="section-title">Payment Method</h2>
                <div class="payment-method">
                    <select name="payment_method" id="payment-method" class="payment-select" onchange="handlePaymentMethodChange()">
                        <option value="" disabled selected>Select Payment Method</option>
                        <option value="NeoCreds">NeoCreds</option>
                        <option value="Cash On Delivery">Cash On Delivery</option>
                        <option value="Pick Up">Pick Up</option>
                    </select>
                    <div id="neocreds-balance-display" style="display: none;">
                        <div class="balance-title">Your NeoCreds Balance</div>
                        <span id="balance-amount">₱0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-summary checkout-section">
            <h2 class="section-title">Order Summary</h2>
            <div class="summary-row">
                <span>Items (<?php echo $total_items; ?>)</span>
                <span>₱<?php echo number_format($total_amount, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping Fee</span>
                <span>Free</span>
            </div>
            <?php if (isset($_POST['payment_method']) && $_POST['payment_method'] === 'NeoCreds'): ?>
            <div class="summary-row">
                <span>NeoCreds Applied</span>
                <span class="deduction-amount">-₱<?php echo number_format(min($total_amount, $neocreds_balance), 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row summary-total">
                <span>Total</span>
                <span>₱<?php 
                    $final_total = $total_amount;
                    if (isset($_POST['payment_method']) && $_POST['payment_method'] === 'NeoCreds') {
                        $final_total = max(0, $total_amount - min($total_amount, $neocreds_balance));
                    }
                    echo number_format($final_total, 2); 
                ?></span>
            </div>
            <button id="place-order-btn" class="place-order-btn" <?php echo (!$address || !$contact) ? 'disabled' : ''; ?>>
                Place Order
            </button>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script>
        let userNeocredsBalance = 0;

        // Fetch NeoCreds balance when page loads
        window.addEventListener('DOMContentLoaded', function() {
            fetchNeocredsBalance();
        });

        // Fetch user's NeoCreds balance
        function fetchNeocredsBalance() {
            fetch('process_neocreds.php?action=balance')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        userNeocredsBalance = parseFloat(data.balance);
                        updateNeocredsDisplay();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Handle payment method change
        function handlePaymentMethodChange() {
            const paymentMethod = document.getElementById('payment-method').value;
            const balanceDisplay = document.getElementById('neocreds-balance-display');
            const neocredsSummary = document.getElementById('neocreds-summary');
            
            balanceDisplay.style.display = paymentMethod === 'NeoCreds' ? 'block' : 'none';
            neocredsSummary.style.display = paymentMethod === 'NeoCreds' ? 'block' : 'none';
            
            if (paymentMethod === 'NeoCreds') {
                updateNeocredsDisplay();
            }
        }

        function updateNeocredsDisplay() {
            const totalAmount = <?php echo $total_amount; ?>;
            const remainingBalance = userNeocredsBalance - totalAmount;
            
            // Update all NeoCreds-related displays
            document.getElementById('balance-amount').textContent = '₱' + userNeocredsBalance.toFixed(2);
            document.getElementById('current-balance').textContent = '₱' + userNeocredsBalance.toFixed(2);
            document.getElementById('deduction-amount').textContent = '-₱' + totalAmount.toFixed(2);
            
            // Update remaining balance with proper styling
            const remainingBalanceElement = document.getElementById('remaining-balance');
            remainingBalanceElement.textContent = '₱' + remainingBalance.toFixed(2);
            remainingBalanceElement.classList.remove('positive', 'negative');
            remainingBalanceElement.classList.add(remainingBalance >= 0 ? 'positive' : 'negative');
        }

        const validateInputs = () => {
            const paymentMethod = document.getElementById('payment-method').value;
            if (!paymentMethod) {
                alert('Please select a payment method');
                return false;
            }

            const address = <?php echo json_encode($address); ?>;
            if (!address) {
                alert('Please add your delivery address in your profile settings');
                return false;
            }

            const contact = <?php echo json_encode($contact); ?>;
            if (!contact) {
                alert('Please add your contact number in your profile settings');
                return false;
            }

            return true;
        };

        document.getElementById('place-order-btn').addEventListener('click', async function(e) {
            e.preventDefault();
            
            if (!validateInputs()) {
                return;
            }

            const paymentMethod = document.getElementById('payment-method').value;
            
            // Handle NeoCreds payment with additional validation
            if (paymentMethod === 'NeoCreds') {
                const totalAmount = <?php echo $total_amount; ?>;
                if (userNeocredsBalance < totalAmount) {
                    alert('Insufficient NeoCreds balance.\n\nRequired: ₱' + totalAmount.toFixed(2) + 
                          '\nYour Balance: ₱' + userNeocredsBalance.toFixed(2) +
                          '\nShort By: ₱' + (totalAmount - userNeocredsBalance).toFixed(2));
                    return;
                }
            }

            // Disable the button and show processing state
            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = 'Processing...';

            try {
                const formData = new FormData();
                formData.append('payment_method', paymentMethod);
                formData.append('delivery_address', <?php echo json_encode($address); ?>);
                formData.append('contact_number', <?php echo json_encode($contact); ?>);
                formData.append('user_name', '<?php echo htmlspecialchars($user_name); ?>');
                formData.append('user_email', '<?php echo htmlspecialchars($user_email); ?>');
                formData.append('amount', <?php echo $total_amount; ?>);
                <?php if (!empty($selected_items)): ?>
                formData.append('selected_items', JSON.stringify(<?php echo json_encode($selected_items); ?>));
                <?php endif; ?>

                // Log the data being sent
                console.log('Sending order data:', Object.fromEntries(formData));

                const response = await fetch('process_order.php', {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Server returned invalid JSON response');
                }

                if (data.success) {
                    alert('Order placed successfully!');
                    window.location.href = 'orders.php';
                } else {
                    throw new Error(data.message || 'Error processing order');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while processing your order. Please try again.');
            } finally {
                this.disabled = false;
                this.textContent = originalText;
            }
        });
    </script>
</body>
</html>