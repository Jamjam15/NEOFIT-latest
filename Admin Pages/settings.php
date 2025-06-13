<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['admin@1'])) {
    header('Location: ../index.php');
    exit();
}

include '../db_connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['admin@1'])) {
    header('Location: ../index.php');
    exit();
}

include '../db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_account'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current admin password from database
        $stmt = $conn->prepare("SELECT password FROM admin WHERE email = 'admin@1'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stored_password = $admin['password'];
            
            // Check if current password matches stored password or is the default 'admin'
            if ($current_password === $stored_password || ($stored_password === 'admin' && $current_password === 'admin')) {
                if ($new_password === $confirm_password) {
                    // Update password in the database
                    $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE email = 'admin@1'");
                    $update_stmt->bind_param("s", $new_password);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Password updated successfully!";
                    } else {
                        $error_message = "Error updating password: " . $conn->error;
                    }
                    $update_stmt->close();
                } else {
                    $error_message = "New passwords do not match.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        } else {
            $error_message = "Admin account not found.";
        }
        $stmt->close();
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current admin password from database
        $stmt = $conn->prepare("SELECT password FROM admin WHERE email = 'admin@1'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stored_password = $admin['password'];
            
            // Check if current password matches stored password or is the default 'admin'
            if ($current_password === $stored_password || ($stored_password === 'admin' && $current_password === 'admin')) {
                if ($new_password === $confirm_password) {
                    // Update password in the database
                    $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE email = 'admin@1'");
                    $update_stmt->bind_param("s", $new_password);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Password updated successfully!";
                    } else {
                        $error_message = "Error updating password: " . $conn->error;
                    }
                    $update_stmt->close();
                } else {
                    $error_message = "New passwords do not match.";
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        } else {
            $error_message = "Admin account not found.";
        }
        $stmt->close();
    } elseif (isset($_POST['update_preferences'])) {
        // System preferences update logic would go here
        $success_message = "System preferences updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Settings</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .settings-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        .form-input {
            width: 100%;
            max-width: 400px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-save {
            background-color: #4d8d8b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-save:hover {
            background-color: #3c7c7a;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
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
                <li onclick="window.location.href='inbox.php'">
                    <i class="fas fa-inbox"></i>
                    <span>Inbox</span>
                </li>                <li onclick="window.location.href='manage_order_details_page.php'">
                    <i class="fas fa-list"></i>
                    <span>Manage Orders</span>
                </li>
                <li onclick="window.location.href='manage_returns.php'">
                    <i class="fas fa-undo"></i>
                    <span>Returns</span>
                </li>
                <li onclick="window.location.href='manage_returns.php'">
                    <i class="fas fa-undo"></i>
                    <span>Returns</span>
                </li>
                <li onclick="window.location.href='customer_orders_page.php'">
                    <i class="fas fa-users"></i>
                    <span>Customer Orders</span>
                </li>
                <li onclick="window.location.href='all_product_page.php'">
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
                <li class="active">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <h1 class="page-title">Settings</h1>

            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <!-- Account Security -->
                <div class="settings-section">
                    <h2 class="section-title">
                        <i class="fas fa-shield-alt"></i>
                        Account Security
                    </h2>
                    <form method="POST" action="">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-input" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-input" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-input" name="confirm_password" required>
                        </div>
                        <button type="submit" name="update_account" class="btn-save">Update Password</button>
                    </form>
                </div>

                <!-- System Preferences -->
                <div class="settings-section">
                    <h2 class="section-title">
                        <i class="fas fa-sliders-h"></i>
                        System Preferences
                    </h2>
                    <form method="POST" action="">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Low Stock Alert Threshold</label>
                            <input type="number" class="form-input" name="low_stock_threshold" value="5" min="1" required>
                            <small style="color: #666; display: block; margin-top: 5px;">Products with stock below this number will be highlighted</small>
                        </div>
                        <button type="submit" name="update_preferences" class="btn-save">Save Preferences</button>
                    </form>
                </div>

                <!-- Logout Section -->
                <div class="settings-section">
                    <h2 class="section-title">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </h2>
                    <form action="../logout.php" method="POST">
                        <button type="submit" class="btn-save" style="background-color: #dc3545;">Logout</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?>