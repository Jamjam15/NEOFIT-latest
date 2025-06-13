<?php
session_start();
include 'db.php';

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

$user_email = $_SESSION['email'];
$user_name = $_SESSION['user_name'] ?? '';

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($subject) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
        if ($stmt) {
            $stmt->bind_param("ssss", $user_name, $user_email, $subject, $message);
            if ($stmt->execute()) {
                $success_message = "Message sent successfully!";
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

// Get user's messages
$stmt = $conn->prepare("SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unread message count
$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM contact_messages WHERE email = ? AND status = 'unread'");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$unread_result = $stmt->get_result();
$unread_count = $unread_result->fetch_assoc()['unread_count'];
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - NEOFIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
        }

        .page-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .messages-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .message-form, .message-list {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-button {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #444;
        }

        .message-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .message-item:last-child {
            border-bottom: none;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .message-subject {
            font-weight: 500;
            color: #333;
        }

        .message-date {
            color: #666;
            font-size: 0.9em;
        }

        .message-content {
            color: #444;
            margin-bottom: 10px;
        }

        .admin-reply {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }

        .admin-reply-header {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
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

        .no-messages {
            text-align: center;
            color: #666;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .messages-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <h1 class="page-title">My Messages</h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="messages-grid">
            <div class="message-form">
                <h2>Send a Message</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" name="send_message" class="submit-button">Send Message</button>
                </form>
            </div>

            <div class="message-list">
                <h2>Message History</h2>
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item">
                            <div class="message-header">
                                <span class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></span>
                                <span class="message-date"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            <?php if (!empty($msg['admin_reply'])): ?>
                                <div class="admin-reply">
                                    <div class="admin-reply-header">Admin Reply:</div>
                                    <div class="admin-reply-content">
                                        <?php echo nl2br(htmlspecialchars($msg['admin_reply'])); ?>
                                    </div>
                                    <div class="admin-reply-date">
                                        Replied on: <?php echo date('M d, Y H:i', strtotime($msg['replied_at'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-messages">
                        No messages yet. Send a message to get started!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Update message count in header
        document.addEventListener('DOMContentLoaded', function() {
            const messageCount = document.querySelector('.message-count');
            if (messageCount) {
                messageCount.textContent = '<?php echo $unread_count; ?>';
                if (<?php echo $unread_count; ?> === 0) {
                    messageCount.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html> 