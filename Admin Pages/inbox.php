<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin@1'])) {
    header('Location: ../index.php');
    exit();
}

// Handle message status updates
if (isset($_POST['update_status'])) {
    $message_id = $_POST['message_id'];
    $new_status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $message_id);
    $stmt->execute();
    $stmt->close();
}

// Handle admin replies
if (isset($_POST['send_reply'])) {
    $message_id = $_POST['message_id'];
    $reply = $_POST['reply'];
    $stmt = $conn->prepare("UPDATE contact_messages SET admin_reply = ?, status = 'replied', replied_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("si", $reply, $message_id);
    $stmt->execute();
    $stmt->close();
}

// Get messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clause = "";
if ($status_filter !== 'all') {
    $where_clause .= " WHERE status = '$status_filter'";
}
if ($search) {
    $where_clause .= $where_clause ? " AND" : " WHERE";
    $where_clause .= " (name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%')";
}

$sql = "SELECT * FROM contact_messages" . $where_clause . " ORDER BY created_at DESC LIMIT $offset, $per_page";
$result = $conn->query($sql);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM contact_messages" . $where_clause;
$total_result = $conn->query($count_sql);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Inbox</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .message-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .message-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: background-color 0.2s;
        }

        .message-item:hover {
            background-color: #f8f9fa;
        }

        .message-item.unread {
            background-color: #f0f7ff;
        }

        .message-checkbox {
            width: 20px;
            height: 20px;
        }

        .message-content {
            flex: 1;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .message-sender {
            font-weight: 600;
            color: #333;
        }

        .message-date {
            color: #666;
            font-size: 0.9em;
        }

        .message-subject {
            font-weight: 500;
            margin-bottom: 5px;
            color: #444;
        }

        .message-preview {
            color: #666;
            font-size: 0.9em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }

        .status-unread {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-read {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-replied {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .message-actions {
            display: flex;
            gap: 10px;
        }

        .action-button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }

        .view-button {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .view-button:hover {
            background-color: #bbdefb;
        }

        .reply-button {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .reply-button:hover {
            background-color: #c8e6c9;
        }

        .delete-button {
            background-color: #ffebee;
            color: #c62828;
        }

        .delete-button:hover {
            background-color: #ffcdd2;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            padding-left: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .status-filter {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
            background-color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .page-button {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-button:hover {
            background-color: #f5f5f5;
        }

        .page-button.active {
            background-color: #1976d2;
            color: white;
            border-color: #1976d2;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 50px auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-button {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .message-details {
            margin-bottom: 20px;
        }

        .message-details p {
            margin: 5px 0;
            color: #666;
        }

        .message-details strong {
            color: #333;
        }

        .reply-form textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            resize: vertical;
        }

        .send-reply-button {
            background-color: #1976d2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .send-reply-button:hover {
            background-color: #1565c0;
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
                <li class="active">
                    <i class="fas fa-inbox"></i>
                    <span>Inbox</span>
                </li>
                <li onclick="window.location.href='manage_order_details_page.php'">
                    <i class="fas fa-list"></i>
                    <span>Manage Orders</span>
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
                <li onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <h1 class="page-title">Inbox</h1>
            
            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select class="status-filter" id="statusFilter">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Messages</option>
                    <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                    <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>
            </div>

            <div class="message-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="message-item <?php echo $row['status'] === 'unread' ? 'unread' : ''; ?>">
                            <input type="checkbox" class="message-checkbox" value="<?php echo $row['id']; ?>">
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="message-sender"><?php echo htmlspecialchars($row['name']); ?></span>
                                    <span class="message-date"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></span>
                                </div>
                                <div class="message-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
                                <div class="message-preview"><?php echo htmlspecialchars(substr($row['message'], 0, 100)) . '...'; ?></div>
                            </div>
                            <span class="message-status status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                            <div class="message-actions">
                                <button class="action-button view-button" onclick="viewMessage(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <?php if ($row['status'] !== 'replied'): ?>
                                    <button class="action-button reply-button" onclick="showReplyModal(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                <?php endif; ?>
                                <button class="action-button delete-button" onclick="deleteMessage(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="message-item">
                        <div class="message-content">
                            <div class="message-subject">No messages found</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <button class="page-button <?php echo $i === $page ? 'active' : ''; ?>" 
                                onclick="window.location.href='?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>'">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- View Message Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('viewModal')">&times;</span>
            <div class="modal-header">
                <h2 id="modalSubject"></h2>
            </div>
            <div class="modal-body">
                <div class="message-details">
                    <p><strong>From:</strong> <span id="modalSender"></span></p>
                    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                    <p><strong>Date:</strong> <span id="modalDate"></span></p>
                    <p><strong>Message:</strong></p>
                    <div id="modalMessage" style="white-space: pre-wrap;"></div>
                </div>
                <div id="modalReply" style="display: none;">
                    <h3>Your Reply:</h3>
                    <div id="modalReplyContent" style="white-space: pre-wrap;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeModal('replyModal')">&times;</span>
            <div class="modal-header">
                <h2>Reply to Message</h2>
            </div>
            <div class="modal-body">
                <form id="replyForm" method="POST">
                    <input type="hidden" name="message_id" id="replyMessageId">
                    <div class="message-details">
                        <p><strong>To:</strong> <span id="replyTo"></span></p>
                        <p><strong>Subject:</strong> <span id="replySubject"></span></p>
                    </div>
                    <div class="reply-form">
                        <textarea name="reply" id="replyText" required placeholder="Type your reply here..."></textarea>
                        <button type="submit" name="send_reply" class="send-reply-button">Send Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const search = this.value;
                const status = document.getElementById('statusFilter').value;
                window.location.href = `?search=${encodeURIComponent(search)}&status=${status}`;
            }
        });

        document.getElementById('statusFilter').addEventListener('change', function() {
            const search = document.getElementById('searchInput').value;
            const status = this.value;
            window.location.href = `?search=${encodeURIComponent(search)}&status=${status}`;
        });

        // View message functionality
        function viewMessage(id) {
            fetch(`get_message.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalSubject').textContent = data.subject;
                    document.getElementById('modalSender').textContent = data.name;
                    document.getElementById('modalEmail').textContent = data.email;
                    document.getElementById('modalDate').textContent = new Date(data.created_at).toLocaleString();
                    document.getElementById('modalMessage').textContent = data.message;
                    
                    if (data.admin_reply) {
                        document.getElementById('modalReply').style.display = 'block';
                        document.getElementById('modalReplyContent').textContent = data.admin_reply;
                    } else {
                        document.getElementById('modalReply').style.display = 'none';
                    }
                    
                    document.getElementById('viewModal').style.display = 'block';
                });
        }

        // Reply functionality
        function showReplyModal(id) {
            fetch(`get_message.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('replyMessageId').value = id;
                    document.getElementById('replyTo').textContent = data.name;
                    document.getElementById('replySubject').textContent = `Re: ${data.subject}`;
                    document.getElementById('replyText').value = '';
                    document.getElementById('replyModal').style.display = 'block';
                });
        }

        // Close modal functionality
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Delete message functionality
        function deleteMessage(id) {
            if (confirm('Are you sure you want to delete this message?')) {
                fetch('delete_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `message_id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting message');
                    }
                });
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html> 