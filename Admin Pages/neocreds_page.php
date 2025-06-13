<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin@1'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - NeoCreds Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Base Styles */
        .content-wrapper {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        /* Tab Navigation */
        .tabs-container {
            background: #f8fafc;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 24px;
        }

        .tabs {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            gap: 32px;
        }

        .tab {
            padding: 20px 24px;
            border: none;
            background: none;
            font-size: 15px;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .tab i {
            font-size: 16px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .tab:hover {
            color: #3498db;
            background: rgba(52, 152, 219, 0.04);
        }

        .tab:hover i {
            opacity: 1;
            transform: scale(1.1);
        }

        .tab.active {
            color: #3498db;
            font-weight: 600;
            background: rgba(52, 152, 219, 0.08);
        }

        .tab.active i {
            opacity: 1;
            transform: scale(1.1);
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #3498db, #2980b9);
            border-radius: 3px 3px 0 0;
            box-shadow: 0 1px 3px rgba(52, 152, 219, 0.3);
        }

        /* Tab Content */
        .tab-content {
            display: none;
            padding: 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Section Controls */
        .section-controls {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* Pending Requests Controls */
        #requestsContent .section-controls {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: end;
        }

        /* History Controls */
        #historyContent .section-controls {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        /* Search Box */
        .search-box {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: #1e293b;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .search-box input:hover {
            border-color: #cbd5e1;
        }

        .search-box input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        /* Filters */
        .filters {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: #1e293b;
            background-color: white;
            cursor: pointer;
            appearance: none;
            transition: all 0.2s ease;
        }

        .select-wrapper select:hover {
            border-color: #cbd5e1;
        }

        .select-wrapper select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        .select-wrapper i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            pointer-events: none;
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .select-wrapper select:focus + i {
            transform: translateY(-50%) rotate(180deg);
        }

        /* Export Button */
        .btn-export {
            padding: 12px 20px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #1e293b;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-export:hover {
            border-color: #3498db;
            color: #3498db;
            background-color: #f8fafc;
        }

        /* Request Cards */
        .requests-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .request-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            transition: all 0.2s ease;
        }

        .request-card:hover {
            border-color: #3498db;
            box-shadow: 0 4px 6px rgba(0,0,0,0.04);
        }

        .request-info {
            flex-grow: 1;
        }

        .user-details {
            margin-bottom: 12px;
        }

        .user-name {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .user-email {
            font-size: 14px;
            color: #64748b;
        }

        .amount {
            font-size: 20px;
            font-weight: 600;
            color: #2ecc71;
            margin: 8px 0;
        }

        .timestamp {
            font-size: 13px;
            color: #94a3b8;
        }

        .processor, .admin-notes {
            font-size: 13px;
            color: #64748b;
            margin-top: 8px;
        }

        .admin-notes {
            font-style: italic;
        }

        /* Request Actions */
        .request-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-width: 200px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-approve, .btn-deny {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-approve {
            background: #22c55e;
            color: white;
        }

        .btn-approve:hover {
            background: #16a34a;
        }

        .btn-deny {
            background: #ef4444;
            color: white;
        }

        .btn-deny:hover {
            background: #dc2626;
        }

        .notes-field textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 13px;
            resize: vertical;
            min-height: 60px;
            transition: all 0.2s ease;
        }

        .notes-field textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.1);
        }

        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-badge.approved {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-badge.denied {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .loading i {
            font-size: 20px;
            color: #3498db;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .filters {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .tabs {
                padding: 0 16px;
            }

            .tab {
                padding: 16px;
                font-size: 14px;
            }

            #requestsContent .section-controls {
                grid-template-columns: 1fr;
            }

            .controls-header {
                flex-direction: column;
            }

            .search-box {
                max-width: none;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .btn-export {
                width: 100%;
                justify-content: center;
            }

            .request-card {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }

            .stat-value {
                font-size: 24px;
            }
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-color: transparent;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s ease;
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, #fff8e1 0%, #ffe082 100%);
            color: #f57c00;
        }

        .stat-icon.approved {
            background: linear-gradient(135deg, #e8f5e9 0%, #a5d6a7 100%);
            color: #2e7d32;
        }

        .stat-icon.denied {
            background: linear-gradient(135deg, #fee2e2 0%, #ef4444 100%);
            color: #dc2626;
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #e3f2fd 0%, #90caf9 100%);
            color: #1976d2;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .stat-label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        /* Page Header */
        .page-header {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .header-content {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 14px;
            margin: 0;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px dashed #cbd5e1;
        }

        .empty-state i {
            font-size: 48px;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .empty-state-text {
            font-size: 14px;
            color: #64748b;
            max-width: 400px;
            margin: 0 auto;
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
                    <span>Messages</span>
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
                <li class="active">
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
            <div class="page-header">
                <div class="header-content">
                    <h1 class="page-title">NeoCreds Management</h1>
                    <p class="page-subtitle">Manage and track NeoCreds transactions</p>
                </div>
                <div class="header-actions">
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Pending Requests</span>
                                <span class="stat-value" id="pendingCount">0</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon approved">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Total Approved</span>
                                <span class="stat-value" id="approvedCount">0</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon denied">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Total Denied</span>
                                <span class="stat-value" id="deniedCount">0</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon total">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-label">Total NeoCreds Processed</span>
                                <span class="stat-value" id="totalNeocreds">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="content-wrapper">
                <!-- Tabs -->
                <div class="tabs-container">
                    <div class="tabs">
                        <button class="tab active" data-tab="requests">
                            <i class="fas fa-inbox"></i>
                            Pending Requests
                        </button>
                        <button class="tab" data-tab="history">
                            <i class="fas fa-history"></i>
                            Transaction History
                        </button>
                    </div>
                </div>

                <!-- Requests Tab Content -->
                <div class="tab-content active" id="requestsContent">
                    <div class="section-controls">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="pendingSearchInput" placeholder="Search pending requests...">
                        </div>
                        <div class="filter-group">
                            <label>Sort By</label>
                            <div class="select-wrapper">
                                <select id="pendingSortFilter">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="amount_high">Amount (High to Low)</option>
                                    <option value="amount_low">Amount (Low to High)</option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                    <div id="requestsContainer" class="requests-container">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            Loading requests...
                        </div>
                    </div>
                </div>

                <!-- History Tab Content -->
                <div class="tab-content" id="historyContent">
                    <div class="section-controls">
                        <div class="controls-header">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" id="historySearchInput" placeholder="Search transaction history...">
                            </div>
                            <button class="btn-export">
                                <i class="fas fa-download"></i>
                                Export Data
                            </button>
                        </div>
                        <div class="filters">
                            <div class="filter-group">
                                <label>Transaction Status</label>
                                <div class="select-wrapper">
                                    <select id="historyStatusFilter">
                                        <option value="all">All Status</option>
                                        <option value="approved">Approved</option>
                                        <option value="denied">Denied</option>
                                    </select>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label>Time Period</label>
                                <div class="select-wrapper">
                                    <select id="historyDateFilter">
                                        <option value="all">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                    </select>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label>Sort By</label>
                                <div class="select-wrapper">
                                    <select id="historySortFilter">
                                        <option value="newest">Newest First</option>
                                        <option value="oldest">Oldest First</option>
                                        <option value="amount_high">Amount (High to Low)</option>
                                        <option value="amount_low">Amount (Low to High)</option>
                                    </select>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="historyContainer" class="requests-container">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            Loading history...
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Make updateStatus function globally accessible
        function updateStatus(requestId, status) {
            if (!confirm(`Are you sure you want to ${status} this request?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('request_id', requestId);
            formData.append('status', status);

            fetch('process_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove the request from pending list
                    const card = document.getElementById(`request-${requestId}`);
                    if (card) {
                        card.remove();
                    }

                    // Refresh both tabs
                    loadRequests();
                    loadHistory();
                    updateStats();

                    alert(`Request ${status} successfully`);
                } else {
                    throw new Error(data.message || 'Failed to process request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred while processing the request');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Cache DOM elements
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            const pendingSearchInput = document.getElementById('pendingSearchInput');
            const pendingSortFilter = document.getElementById('pendingSortFilter');
            const historySearchInput = document.getElementById('historySearchInput');
            const historyStatusFilter = document.getElementById('historyStatusFilter');
            const historyDateFilter = document.getElementById('historyDateFilter');
            const historySortFilter = document.getElementById('historySortFilter');
            const exportButton = document.querySelector('.btn-export');

            // Tab switching
            function switchTab(tabId) {
                tabs.forEach(tab => tab.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                const activeTab = document.querySelector(`[data-tab="${tabId}"]`);
                const activeContent = document.getElementById(`${tabId}Content`);
                
                activeTab.classList.add('active');
                activeContent.classList.add('active');
                
                if (tabId === 'requests') {
                    loadRequests();
                } else {
                    loadHistory();
                }
            }

            // Empty state templates
            const emptyStates = {
                requests: `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <div class="empty-state-title">No Pending Requests</div>
                        <div class="empty-state-text">There are currently no NeoCreds requests waiting for approval.</div>
                    </div>
                `,
                history: `
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <div class="empty-state-title">No Transaction History</div>
                        <div class="empty-state-text">No NeoCreds transactions have been processed yet.</div>
                    </div>
                `,
                error: `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="empty-state-title">Unable to Load Data</div>
                        <div class="empty-state-text">There was a problem loading the data. Please try again.</div>
                    </div>
                `
            };

            tabs.forEach(tab => {
                tab.addEventListener('click', () => switchTab(tab.dataset.tab));
            });

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Display Functions
            function displayRequests(container, requests, searchTerm = '', sortBy = 'newest') {
                let filteredRequests = searchTerm 
                    ? requests.filter(request => 
                        request.user_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        request.user_email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        request.amount.toString().includes(searchTerm)
                    )
                    : requests;

                // Apply sorting
                filteredRequests.sort((a, b) => {
                    switch(sortBy) {
                        case 'newest':
                            return new Date(b.request_date) - new Date(a.request_date);
                        case 'oldest':
                            return new Date(a.request_date) - new Date(b.request_date);
                        case 'amount_high':
                            return parseFloat(b.amount) - parseFloat(a.amount);
                        case 'amount_low':
                            return parseFloat(a.amount) - parseFloat(b.amount);
                        default:
                            return 0;
                    }
                });

                if (!filteredRequests || filteredRequests.length === 0) {
                    container.innerHTML = emptyStates.requests;
                    return;
                }

                container.innerHTML = filteredRequests.map(request => `
                    <div class="request-card" id="request-${request.id}">
                        <div class="request-info">
                            <div class="user-details">
                                <div class="user-name">${request.user_name}</div>
                                <div class="user-email">${request.user_email}</div>
                            </div>
                            <div class="amount">₱${parseFloat(request.amount).toFixed(2)}</div>
                            <div class="timestamp">Requested on ${new Date(request.request_date).toLocaleString()}</div>
                        </div>
                        <div class="request-actions">
                            <button class="btn-approve" onclick="updateStatus(${request.id}, 'approved')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn-deny" onclick="updateStatus(${request.id}, 'denied')">
                                <i class="fas fa-times"></i> Deny
                            </button>
                        </div>
                    </div>
                `).join('');
            }

            function displayHistory(container, transactions, searchTerm = '', filters = {}) {
                let filteredTransactions = transactions;

                // Apply search term filter
                if (searchTerm) {
                    filteredTransactions = filteredTransactions.filter(transaction =>
                        transaction.user_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        transaction.user_email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                        transaction.amount.toString().includes(searchTerm)
                    );
                }

                // Apply status filter
                if (filters.status && filters.status !== 'all') {
                    filteredTransactions = filteredTransactions.filter(transaction =>
                        transaction.status.toLowerCase() === filters.status.toLowerCase()
                    );
                }

                // Apply date filter
                if (filters.date) {
                    const now = new Date();
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    const filterDate = new Date(today);

                    switch(filters.date) {
                        case 'today':
                            filteredTransactions = filteredTransactions.filter(transaction =>
                                new Date(transaction.process_date) >= today
                            );
                            break;
                        case 'week':
                            filterDate.setDate(filterDate.getDate() - 7);
                            filteredTransactions = filteredTransactions.filter(transaction =>
                                new Date(transaction.process_date) >= filterDate
                            );
                            break;
                        case 'month':
                            filterDate.setMonth(filterDate.getMonth() - 1);
                            filteredTransactions = filteredTransactions.filter(transaction =>
                                new Date(transaction.process_date) >= filterDate
                            );
                            break;
                    }
                }

                // Apply sorting
                if (filters.sort) {
                    filteredTransactions.sort((a, b) => {
                        switch(filters.sort) {
                            case 'newest':
                                return new Date(b.process_date) - new Date(a.process_date);
                            case 'oldest':
                                return new Date(a.process_date) - new Date(b.process_date);
                            case 'amount_high':
                                return parseFloat(b.amount) - parseFloat(a.amount);
                            case 'amount_low':
                                return parseFloat(a.amount) - parseFloat(b.amount);
                            default:
                                return 0;
                        }
                    });
                }

                if (!filteredTransactions || filteredTransactions.length === 0) {
                    container.innerHTML = emptyStates.history;
                    return;
                }

                container.innerHTML = filteredTransactions.map(transaction => `
                    <div class="request-card">
                        <div class="request-info">
                            <div class="user-details">
                                <div class="user-name">${transaction.user_name}</div>
                                <div class="user-email">${transaction.user_email}</div>
                            </div>
                            <div class="amount">₱${parseFloat(transaction.amount).toFixed(2)}</div>
                            <div class="timestamp">
                                Requested: ${new Date(transaction.request_date).toLocaleString()}<br>
                                Processed: ${new Date(transaction.process_date).toLocaleString()}
                            </div>
                        </div>
                        <div class="status-badge ${transaction.status.toLowerCase()}">
                            ${transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1)}
                        </div>
                    </div>
                `).join('');
            }

            // Function to export transaction history
            function exportTransactionHistory(transactions) {
                if (!transactions || transactions.length === 0) {
                    alert('No data to export');
                    return;
                }

                let csvContent = 'Transaction ID,User Name,Email,Amount,Status,Request Date,Process Date\n';
                
                transactions.forEach(transaction => {
                    const row = [
                        transaction.id,
                        transaction.user_name,
                        transaction.user_email,
                        parseFloat(transaction.amount).toFixed(2),
                        transaction.status,
                        new Date(transaction.request_date).toLocaleString(),
                        new Date(transaction.process_date).toLocaleString()
                    ].map(cell => `"${cell}"`).join(',');
                    
                    csvContent += row + '\n';
                });

                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                
                link.setAttribute('href', url);
                link.setAttribute('download', `neocreds_transactions_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // Event Listeners for Search and Filters
            let currentRequests = [];
            let currentTransactions = [];

            pendingSearchInput.addEventListener('input', debounce((e) => {
                const container = document.getElementById('requestsContainer');
                displayRequests(container, currentRequests, e.target.value, pendingSortFilter.value);
            }, 300));

            pendingSortFilter.addEventListener('change', () => {
                const container = document.getElementById('requestsContainer');
                displayRequests(container, currentRequests, pendingSearchInput.value, pendingSortFilter.value);
            });

            historySearchInput.addEventListener('input', debounce((e) => {
                const container = document.getElementById('historyContainer');
                const filters = {
                    status: historyStatusFilter.value,
                    date: historyDateFilter.value,
                    sort: historySortFilter.value
                };
                displayHistory(container, currentTransactions, e.target.value, filters);
            }, 300));

            // Event listeners for filters
            [historyStatusFilter, historyDateFilter, historySortFilter].forEach(filter => {
                filter.addEventListener('change', () => {
                    const container = document.getElementById('historyContainer');
                    const filters = {
                        status: historyStatusFilter.value,
                        date: historyDateFilter.value,
                        sort: historySortFilter.value
                    };
                    displayHistory(container, currentTransactions, historySearchInput.value, filters);
                });
            });

            // Export button event listener
            exportButton.addEventListener('click', () => {
                fetch('process_request.php?action=get_history')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            exportTransactionHistory(data.transactions);
                        } else {
                            throw new Error(data.message || 'Failed to load transactions for export');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to export data: ' + error.message);
                    });
            });

            // Load history
            function loadHistory() {
                const container = document.getElementById('historyContainer');
                container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading history...</div>';

                fetch('process_request.php?action=get_history')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            currentTransactions = data.transactions;
                            const filters = {
                                status: historyStatusFilter.value,
                                date: historyDateFilter.value,
                                sort: historySortFilter.value
                            };
                            displayHistory(container, currentTransactions, historySearchInput.value, filters);
                        } else {
                            throw new Error(data.message || 'Failed to load history');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        container.innerHTML = emptyStates.error;
                    });
            }

            // Load pending requests
            function loadRequests() {
                const container = document.getElementById('requestsContainer');
                container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading requests...</div>';

                fetch('process_request.php?action=get_requests')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            currentRequests = data.requests;
                            displayRequests(
                                container, 
                                currentRequests, 
                                pendingSearchInput.value, 
                                pendingSortFilter.value
                            );
                        } else {
                            throw new Error(data.message || 'Failed to load requests');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        container.innerHTML = emptyStates.error;
                    });
            }

            // Update statistics
            function updateStats() {
                fetch('process_request.php?action=get_stats')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById('pendingCount').textContent = data.pending_count;
                            document.getElementById('approvedCount').textContent = data.approved_count;
                            document.getElementById('deniedCount').textContent = data.denied_count;
                            document.getElementById('totalNeocreds').textContent = 
                                '₱' + parseFloat(data.total_neocreds).toFixed(2);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Make these functions globally available
            window.loadRequests = loadRequests;
            window.loadHistory = loadHistory;
            window.updateStats = updateStats;

            // Initial load
            loadRequests();
            loadHistory();
            updateStats();

            // Auto refresh every 30 seconds
            setInterval(() => {
                loadRequests();
                loadHistory();
                updateStats();
            }, 30000);
        });
    </script>
</body>
</html> 