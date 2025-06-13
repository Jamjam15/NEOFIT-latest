<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
    header('Location: inbox.php?tab=sent'); 
    exit();
}

// Handle move to trash
if (isset($_POST['move_to_trash'])) {
    $message_id = $_POST['message_id'];
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'deleted' WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
}

// Handle restore from trash
if (isset($_POST['restore_message'])) {
    $message_id = $_POST['message_id'];
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'unread' WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
}

// Get messages with pagination and tab filter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'inbox'; // Default to inbox tab
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clause = "";

switch ($active_tab) {
    case 'inbox':
        $where_clause .= " WHERE status IN ('unread', 'read')";
        break;
    case 'sent':
        $where_clause .= " WHERE status = 'replied'";
        break;
    case 'trash':
        $where_clause .= " WHERE status = 'deleted'";
        break;
    default:
        $where_clause .= " WHERE status IN ('unread', 'read')"; // Default to inbox
        break;
}

if ($search) {
    $where_clause .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%')";
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
        /* Original styles (before extensive modal CSS changes) */
        .tabs-navigation {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .tab-button {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            border-bottom: none;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            margin-right: 5px;
            font-weight: 500;
            color: #555;
            transition: all 0.2s;
        }

        .tab-button:hover {
            background-color: #e0e0e0;
        }

        .tab-button.active {
            background-color: white;
            border-color: #ddd;
            color: #333;
            border-bottom: 1px solid white;
        }

        .tab-content-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 20px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .message-list {
            background: none;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 0;
        }

        .message-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: background-color 0.2s;
            cursor: pointer;
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

        .message-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 5px;
        }

        .message-date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .message-actions .button-group {
            display: flex;
            gap: 10px;
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

        .status-deleted {
            background-color: #ffebee;
            color: #c62828;
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

        .restore-button {
            background-color: #e0f2f7;
            color: #00796b;
        }

        .restore-button:hover {
            background-color: #b2dfdb;
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
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto; /* 10% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .modal-body p {
            margin: 5px 0;
            color: #666;
        }

        .modal-body strong {
            color: #333;
        }

        #modalReply {
            margin-top: 15px;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }

        .reply-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
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
                </li>                <li onclick="window.location.href='manage_order_details_page.php'">
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
                <li onclick="window.location.href='settings.php'">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <h1 class="page-title">Messages</h1>
            
            <div class="tabs-navigation">
                <div class="tab-button active" data-tab="inbox">Inbox</div>
                <div class="tab-button" data-tab="sent">Sent</div>
                <div class="tab-button" data-tab="trash">Trash</div>
            </div>

            <div class="tab-content-container">
                <div id="inboxContent" class="tab-pane active">
                    <div class="filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInputInbox" placeholder="Search inbox..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="message-list">
                        <?php if ($active_tab === 'inbox' && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <div class="message-item <?php echo $row['status'] === 'unread' ? 'unread' : ''; ?>" onclick="viewMessage(<?php echo $row['id']; ?>)">
                                    <div class="message-content">
                                        <div class="message-header">
                                            <span class="message-sender"><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                        <div class="message-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
                                        <div class="message-preview"><?php echo htmlspecialchars(substr($row['message'], 0, 100)) . '...'; ?></div>
                                    </div>
                                    <div class="message-actions">
                                        <span class="message-date"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></span>
                                        <div class="button-group">
                                            <button class="action-button reply-button" onclick="showReplyModal(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-reply"></i> Reply
                                            </button>
                                            <button class="action-button delete-button" onclick="moveToTrash(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i> Move to Trash
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php elseif ($active_tab === 'inbox'): ?>
                            <div class="message-item">
                                <div class="message-content">
                                    <div class="message-subject">No messages found in Inbox.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="sentContent" class="tab-pane <?php echo $active_tab === 'sent' ? 'active' : ''; ?>">
                    <div class="filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInputSent" placeholder="Search sent messages..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="message-list">
                        <?php if ($active_tab === 'sent' && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <div class="message-item" onclick="viewMessage(<?php echo $row['id']; ?>)">
                                    <div class="message-content">
                                        <div class="message-header">
                                            <span class="message-sender"><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                        <div class="message-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
                                        <div class="message-preview"><?php echo htmlspecialchars(substr($row['message'], 0, 100)) . '...'; ?></div>
                                    </div>
                                    <div class="message-actions">
                                        <span class="message-date"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></span>
                                        <div class="button-group">
                                            <button class="action-button delete-button" onclick="moveToTrash(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i> Move to Trash
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php elseif ($active_tab === 'sent'): ?>
                            <div class="message-item">
                                <div class="message-content">
                                    <div class="message-subject">No sent messages found.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="trashContent" class="tab-pane <?php echo $active_tab === 'trash' ? 'active' : ''; ?>">
                    <div class="filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInputTrash" placeholder="Search trash..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="message-list">
                        <?php if ($active_tab === 'trash' && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <div class="message-item" onclick="viewMessage(<?php echo $row['id']; ?>)">
                                    <div class="message-content">
                                        <div class="message-header">
                                            <span class="message-sender"><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                        <div class="message-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
                                        <div class="message-preview"><?php echo htmlspecialchars(substr($row['message'], 0, 100)) . '...'; ?></div>
                                    </div>
                                    <div class="message-actions">
                                        <span class="message-date"><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></span>
                                        <div class="button-group">
                                            <button class="action-button restore-button" onclick="restoreMessage(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-undo"></i> Restore
                                            </button>
                                            <button class="action-button delete-button" onclick="deleteMessagePermanently(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete Permanently
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php elseif ($active_tab === 'trash'): ?>
                            <div class="message-item">
                                <div class="message-content">
                                    <div class="message-subject">No messages found in Trash.</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <button class="page-button <?php echo $i === $page ? 'active' : ''; ?>" 
                                onclick="window.location.href='?tab=<?php echo $active_tab; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>'">
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
                    <p><strong>Subject:</strong> <span id="modalSubject"></span></p>
                    <p><strong>Date:</strong> <span id="modalDate"></span></p>
                </div>
                <div id="modalMessage" style="white-space: pre-wrap;"></div>
                <div id="modalReply" style="display: none;">
                    <h3>Your Reply:</h3>
                    <div id="modalReplyContent" style="white-space: pre-wrap;"></div>
                </div>
                <div class="modal-actions">
                    <button class="action-button reply-button" id="modalReplyButton" style="display: none;">
                        <i class="fas fa-reply"></i> Reply
                    </button>
                    <button class="action-button delete-button" id="modalMoveToTrashButton" style="display: none;">
                        <i class="fas fa-trash"></i> Move to Trash
                    </button>
                    <button class="action-button restore-button" id="modalRestoreButton" style="display: none;">
                        <i class="fas fa-undo"></i> Restore
                    </button>
                    <button class="action-button delete-button" id="modalDeletePermanentlyButton" style="display: none;">
                        <i class="fas fa-trash"></i> Delete Permanently
                    </button>
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
                    <div class="message-container">
                        <div class="message-details">
                            <p><strong>To:</strong> <span id="replyTo"></span></p>
                            <p><strong>Subject:</strong> <span id="replySubject"></span></p>
                        </div>
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
        // Tab switching functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tab = this.dataset.tab;
                window.location.href = `?tab=${tab}`;
            });
        });

        // Set active tab on load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'inbox';
            document.querySelectorAll('.tab-button').forEach(button => {
                if (button.dataset.tab === activeTab) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                if (pane.id === `${activeTab}Content`) {
                    pane.classList.add('active');
                } else {
                    pane.classList.remove('active');
                }
            });

            // Set initial search value based on active tab
            const searchInput = document.getElementById(`searchInput${activeTab.charAt(0).toUpperCase() + activeTab.slice(1)}`);
            if (searchInput) {
                const currentSearch = urlParams.get('search') || '';
                searchInput.value = currentSearch;
            }
        });

        // Search functionality for each tab
        document.getElementById('searchInputInbox').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const search = this.value;
                window.location.href = `?tab=inbox&search=${encodeURIComponent(search)}`;
            }
        });

        document.getElementById('searchInputSent').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const search = this.value;
                window.location.href = `?tab=sent&search=${encodeURIComponent(search)}`;
            }
        });

        document.getElementById('searchInputTrash').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const search = this.value;
                window.location.href = `?tab=trash&search=${encodeURIComponent(search)}`;
            }
        });

        // View message functionality (original, with subject and blue border)
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
                    
                    // Show/hide action buttons in modal based on message status
                    const replyButton = document.getElementById('modalReplyButton');
                    const moveToTrashButton = document.getElementById('modalMoveToTrashButton');
                    const restoreButton = document.getElementById('modalRestoreButton');
                    const deletePermanentlyButton = document.getElementById('modalDeletePermanentlyButton');

                    replyButton.style.display = 'none';
                    moveToTrashButton.style.display = 'none';
                    restoreButton.style.display = 'none';
                    deletePermanentlyButton.style.display = 'none';

                    if (data.status === 'unread' || data.status === 'read') {
                        replyButton.style.display = 'inline-flex';
                        moveToTrashButton.style.display = 'inline-flex';
                    } else if (data.status === 'replied') {
                        moveToTrashButton.style.display = 'inline-flex';
                    } else if (data.status === 'deleted') {
                        restoreButton.style.display = 'inline-flex';
                        deletePermanentlyButton.style.display = 'inline-flex';
                    }

                    // Assign event listeners to modal action buttons (original onclick assignment)
                    replyButton.onclick = () => {
                        closeModal('viewModal');
                        showReplyModal(data.id);
                    };
                    moveToTrashButton.onclick = () => {
                        closeModal('viewModal');
                        moveToTrash(data.id);
                    };
                    restoreButton.onclick = () => {
                        closeModal('viewModal');
                        restoreMessage(data.id);
                    };
                    deletePermanentlyButton.onclick = () => {
                        closeModal('viewModal');
                        deleteMessagePermanently(data.id);
                    };

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

        // Move to trash functionality
        function moveToTrash(id) {
            if (confirm('Are you sure you want to move this message to trash?')) {
                fetch('inbox.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `move_to_trash=1&message_id=${id}`
                })
                .then(response => response.text())
                .then(data => {
                    location.reload(); // Original reload behavior
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error moving message to trash.');
                });
            }
        }

        // Restore message functionality
        function restoreMessage(id) {
            if (confirm('Are you sure you want to restore this message from trash?')) {
                fetch('inbox.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `restore_message=1&message_id=${id}`
                })
                .then(response => response.text())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error restoring message.');
                });
            }
        }

        // Delete message permanently functionality
        function deleteMessagePermanently(id) {
            if (confirm('Are you sure you want to permanently delete this message? This action cannot be undone.')) {
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
                        alert('Error deleting message: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting message permanently.');
                });
            }
        }

        // Close modal functionality
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

    </script>
</body>
</html>