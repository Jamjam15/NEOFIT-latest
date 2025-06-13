<?php
require_once dirname(__FILE__) . '/../db_connection.php';

// Get total revenue from successful payments
function getTotalRevenue() {
    global $conn;
    try {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Error getting total revenue: " . mysqli_error($conn));
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Exception getting total revenue: " . $e->getMessage());
        return 0;
    }
}

// Get today's earnings from successful payments
function getTodayEarnings() {
    global $conn;
    try {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments 
                WHERE status = 'success' 
                AND DATE(payment_date) = CURDATE()";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Error getting today's earnings: " . mysqli_error($conn));
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Exception getting today's earnings: " . $e->getMessage());
        return 0;
    }
}

// Get total amount of pending payments
function getPendingPayments() {
    global $conn;
    try {
        $query = "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'pending'";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            error_log("Error getting pending payments: " . mysqli_error($conn));
            return 0;
        }
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Exception getting pending payments: " . $e->getMessage());
        return 0;
    }
}

// Get filtered payments
function getFilteredPayments($search = '', $status = '', $date = '', $page = 1) {
    global $conn;
    try {
        $conditions = [];
        $params = [];
        $types = "";
        
        // Build search condition for order_id or customer name
        if (!empty($search)) {
            $conditions[] = "(p.order_id LIKE ? OR o.user_name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        // Add status condition
        if (!empty($status)) {
            $conditions[] = "p.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Add date condition
        if (!empty($date)) {
            $conditions[] = "DATE(p.payment_date) = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        // Build the WHERE clause
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // Calculate offset
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT p.*, o.user_name as customer_name 
                FROM payments p 
                LEFT JOIN orders o ON p.order_id = o.id 
                $whereClause 
                ORDER BY p.payment_date DESC 
                LIMIT ? OFFSET ?";
                
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Error preparing query: " . mysqli_error($conn));
            return [];
        }
        
        // Add limit and offset to params
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $payments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $payments[] = $row;
        }
        
        return $payments;
    } catch (Exception $e) {
        error_log("Error in getFilteredPayments: " . $e->getMessage());
        return [];
    }
}

// Get count of filtered payments
function getFilteredPaymentsCount($search = '', $status = '', $date = '') {
    global $conn;
    try {
        $conditions = [];
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $conditions[] = "(p.order_id LIKE ? OR o.user_name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        if (!empty($status)) {
            $conditions[] = "p.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if (!empty($date)) {
            $conditions[] = "DATE(p.payment_date) = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        $query = "SELECT COUNT(*) as total 
                FROM payments p 
                LEFT JOIN orders o ON p.order_id = o.id 
                $whereClause";
                
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Error preparing count query: " . mysqli_error($conn));
            return 0;
        }
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        return (int)$row['total'];
    } catch (Exception $e) {
        error_log("Error in getFilteredPaymentsCount: " . $e->getMessage());
        return 0;
    }
}

// Update payment status
function updatePaymentStatus($transaction_id, $new_status) {
    global $conn;
    
    if (!in_array($new_status, ['pending', 'success', 'failed'])) {
        error_log("Invalid payment status attempted: $new_status");
        return false;
    }
    
    try {
        mysqli_begin_transaction($conn);
        
        // Get current status for logging
        $current_stmt = mysqli_prepare($conn, "SELECT status FROM payments WHERE transaction_id = ?");
        mysqli_stmt_bind_param($current_stmt, "s", $transaction_id);
        mysqli_stmt_execute($current_stmt);
        $result = mysqli_stmt_get_result($current_stmt);
        $current = mysqli_fetch_assoc($result);
        
        if (!$current) {
            mysqli_rollback($conn);
            error_log("Transaction ID not found: $transaction_id");
            return false;
        }
        
        // Update payment status
        $update_stmt = mysqli_prepare($conn, "UPDATE payments SET status = ? WHERE transaction_id = ?");
        mysqli_stmt_bind_param($update_stmt, "ss", $new_status, $transaction_id);
        $success = mysqli_stmt_execute($update_stmt);
        
        if (!$success) {
            mysqli_rollback($conn);
            error_log("Failed to update payment status: " . mysqli_error($conn));
            return false;
        }
        
        // Log the status change
        $log_stmt = mysqli_prepare($conn, 
            "INSERT INTO payment_status_logs (transaction_id, old_status, new_status, changed_by, changed_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        $admin = $_SESSION['admin@1'] ?? 'system';
        mysqli_stmt_bind_param($log_stmt, "ssss", 
            $transaction_id, 
            $current['status'], 
            $new_status, 
            $admin
        );
        $log_success = mysqli_stmt_execute($log_stmt);
        
        if (!$log_success) {
            mysqli_rollback($conn);
            error_log("Failed to log payment status change: " . mysqli_error($conn));
            return false;
        }
        
        mysqli_commit($conn);
        return true;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Exception in updatePaymentStatus: " . $e->getMessage());
        return false;
    }
}

// Function to auto-update payment status based on delivery status
function updatePaymentStatusFromDelivery($order_id, $delivery_status) {
    global $conn;
    try {
        // Only update for COD and Pickup orders
        $query = "UPDATE payments p
                 INNER JOIN orders o ON p.order_id = o.id
                 SET p.status = CASE 
                    WHEN LOWER(o.delivery_status) = 'delivered' THEN 'success'
                    WHEN LOWER(o.delivery_status) = 'cancelled' THEN 'failed'
                    ELSE p.status
                 END
                 WHERE p.order_id = ? 
                 AND (LOWER(p.payment_method) = 'cash on delivery' OR LOWER(p.payment_method) = 'pickup')";
                 
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            error_log("Error preparing update payment status query: " . mysqli_error($conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $order_id);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Error updating payment status: " . mysqli_stmt_error($stmt));
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error in updatePaymentStatusFromDelivery: " . $e->getMessage());
        return false;
    }
}

// Update payment status based on payment method
function updatePaymentStatusByMethod($order_id, $payment_method) {
    global $conn;
    
    try {
        // If payment method is Cash on Delivery, set status to success
        if (strtolower($payment_method) === 'cod' || strtolower($payment_method) === 'cash on delivery') {
            $query = "UPDATE payments SET status = 'success' WHERE order_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            if (!$stmt) {
                error_log("Error preparing query: " . mysqli_error($conn));
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, "s", $order_id);
            $result = mysqli_stmt_execute($stmt);
            
            if (!$result) {
                error_log("Error updating payment status: " . mysqli_error($conn));
                return false;
            }
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error in updatePaymentStatusByMethod: " . $e->getMessage());
        return false;
    }
}

?>