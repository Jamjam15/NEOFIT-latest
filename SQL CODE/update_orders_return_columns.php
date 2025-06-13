<?php
include '../db.php';

$alterTableSQL = "ALTER TABLE orders 
    ADD COLUMN return_requested_at DATETIME DEFAULT NULL,
    ADD COLUMN return_status VARCHAR(20) DEFAULT NULL,
    ADD COLUMN return_reason TEXT DEFAULT NULL,
    ADD COLUMN return_processed_at DATETIME DEFAULT NULL,
    ADD COLUMN return_processed_by INT DEFAULT NULL,
    ADD COLUMN admin_notes TEXT DEFAULT NULL";

if ($conn->query($alterTableSQL) === TRUE) {
    echo "Orders table updated successfully with return columns";
} else {
    echo "Error updating orders table: " . $conn->error;
}

$conn->close();
?>
