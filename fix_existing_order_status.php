<?php
require_once 'db.php';

// Status mapping from possible incorrect values to correct ENUM values
$status_map = [
    'to_pack' => 'To Pack',
    'to pack' => 'To Pack',
    'topack' => 'To Pack',
    'packed' => 'Packed',
    'in_transit' => 'In Transit',
    'in transit' => 'In Transit',
    'intransit' => 'In Transit',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
    'canceled' => 'Cancelled',
    'returned' => 'Returned'
];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get all orders with invalid status values
    $sql = "SELECT id, status FROM orders WHERE status NOT IN ('To Pack', 'Packed', 'In Transit', 'Delivered', 'Cancelled', 'Returned')";
    $result = $conn->query($sql);
    
    $fixed_count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $current_status = strtolower($row['status']);
            if (isset($status_map[$current_status])) {
                $new_status = $status_map[$current_status];
                $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $new_status, $row['id']);
                if ($stmt->execute()) {
                    $fixed_count++;
                    echo "Fixed order #{$row['id']}: {$row['status']} -> {$new_status}<br>";
                }
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<br>Fixed $fixed_count orders with invalid status values.<br>";
    echo "Done. All orders should now have correct status values.";

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}
?>
