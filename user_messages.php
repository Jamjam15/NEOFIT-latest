<?php
session_start();
include 'db.php'; // Assuming db.php is in the parent directory

$user_email = '';
if (isset($_SESSION['user_email'])) { // Assuming you store user email in session upon login
    $user_email = $_SESSION['user_email'];
}

$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_user_message'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            if ($stmt->execute()) {
                $message_sent = true;
            } else {
                $error_message = "Error sending message: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Database prepare error: " . $conn->error;
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}

// Fetch messages for the current user's email if provided
$user_messages = [];
if (!empty($user_email)) {
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? AND (status = 'replied' OR admin_reply IS NOT NULL) ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $user_messages[] = $row;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - Neofit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        form input[type="text"],
        form input[type="email"],
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding in width */
        }
        form textarea {
            resize: vertical;
            min-height: 100px;
        }
        form button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        form button:hover {
            background-color: #0056b3;
        }
        .message-item {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 5px solid #007bff;
        }
        .message-item.admin-reply {
            background: #e8f5e9; /* Light green */
            border-left: 5px solid #28a745; /* Darker green */
        }
        .message-item p {
            margin: 5px 0;
        }
        .message-item strong {
            color: #555;
        }
        .no-messages {
            text-align: center;
            color: #666;
            padding: 20px;
            border: 1px dashed #ccc;
            border-radius: 8px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Messages</h1>

        <?php if ($message_sent): ?>
            <div class="success-message">
                Your message has been sent successfully!
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <h2>Send a New Message</h2>
        <form action="user_messages.php" method="POST">
            <label for="name">Your Name:</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
            
            <label for="email">Your Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user_email); ?>">
            
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
            
            <label for="message">Message:</label>
            <textarea id="message" name="message" required></textarea>
            
            <button type="submit" name="send_user_message">Send Message</button>
        </form>

        <h2>My Correspondence</h2>
        <?php if (!empty($user_messages)): ?>
            <?php foreach ($user_messages as $msg): ?>
                <div class="message-item <?php echo !empty($msg['admin_reply']) ? 'admin-reply' : ''; ?>">
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?></p>
                    <p><strong>Sent On:</strong> <?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></p>
                    <p><strong>Your Message:</strong><br><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    <?php if (!empty($msg['admin_reply'])): ?>
                        <p><strong>Admin Reply:</strong><br><?php echo nl2br(htmlspecialchars($msg['admin_reply'])); ?></p>
                        <p><strong>Replied On:</strong> <?php echo date('M d, Y H:i', strtotime($msg['replied_at'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-messages">
                No correspondence found. Send a message to start!
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 