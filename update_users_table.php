<?php
include 'db.php';

try {
    // Add security_question and security_answer columns if they don't exist
    $sql = "ALTER TABLE users 
            ADD COLUMN IF NOT EXISTS security_question VARCHAR(50) NOT NULL AFTER password,
            ADD COLUMN IF NOT EXISTS security_answer VARCHAR(255) NOT NULL AFTER security_question,
            ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL";
    
    if ($conn->query($sql)) {
        echo "Users table updated successfully.";
    } else {
        echo "Error updating users table: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 