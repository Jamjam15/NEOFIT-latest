<?php
include '../db.php';

// Create contact_messages table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied', 'deleted') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    admin_reply TEXT NULL,
    replied_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
)";

if ($conn->query($sql)) {
    echo "Contact messages table created successfully or already exists.";
} else {
    echo "Error creating contact messages table: " . $conn->error;
}

$conn->close();
?> 