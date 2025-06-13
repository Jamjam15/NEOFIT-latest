<?php
session_start();
if (!isset($_SESSION['admin@1'])) {
    header('Location: ../landing_page.php');
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/../db_connection.php';
require_once 'payment_functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Payments</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .payment-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        .payment-filters {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .payment-filters input[type="text"],
        .payment-filters select,
        .payment-filters input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            color: #333;
            background-color: white;
            transition: all 0.3s ease;
        }
        .payment-filters input[type="text"]:focus,
        .payment-filters select:focus,
        .payment-filters input[type="date"]:focus {
            border-color: #4d8d8b;
            box-shadow: 0 0 0 3px rgba(77, 141, 139, 0.1);
            outline: none;
        }
        .payment-filters input[type="text"]:hover,
        .payment-filters select:hover,
        .payment-filters input[type="date"]:hover {
            border-color: #4d8d8b;
        }
        .payment-filters input[type="text"]::placeholder {
            color: #6c757d;
        }
        .payment-filters select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234d8d8b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 35px;
            cursor: pointer;
        }

        /* Payment Status Dropdown Styling */
        .payment-filters select option[value="success"] {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            font-weight: 500;
        }

        .payment-filters select option[value="pending"] {
            background-color: #fff3cd;
            color: #856404;
            padding: 12px;
            font-weight: 500;
        }

        .payment-filters select option[value="failed"] {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            font-weight: 500;
        }

        /* Add this to ensure the dropdown options are visible */
        .payment-filters select option {
            padding: 12px;
            font-size: 14px;
            color: #333;
            background-color: white;
            transition: all 0.2s ease;
        }

        /* Add hover effect for options */
        .payment-filters select option:hover {
            filter: brightness(0.95);
        }

        /* Add this to ensure the dropdown is properly styled in different browsers */
        .payment-filters select::-ms-expand {
            display: none;
        }

        /* Add this to style the dropdown in Firefox */
        @-moz-document url-prefix() {
            .payment-filters select {
                color: #333;
                text-shadow: 0 0 0 #000;
            }
        }
        .payment-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .summary-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .summary-card .amount {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        .payment-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .payment-actions button {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
            color: white;
        }

        .btn-view {
            background-color: #6c757d;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .btn-view:hover { background-color: #5a6268; }
        .btn-approve:hover { background-color: #218838; }
        .btn-reject:hover { background-color: #c82333; }

        .payment-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
        }
        .page-link.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        #loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .payment-detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .payment-detail-row:last-child {
            border-bottom: none;
        }

        .payment-detail-label {
            flex: 0 0 150px;
            font-weight: 500;
            color: #666;
        }

        .payment-detail-value {
            flex: 1;
            color: #333;
        }

        .modal-content {
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .payment-method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-left: 8px;
        }
        
        .method-cod {
            background-color: #ffd700;
            color: #000;
        }
        
        .method-pickup {
            background-color: #87ceeb;
            color: #000;
        }
        
        .method-neocreds {
            background-color: #98fb98;
            color: #000;
        }
        
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        
        small {
            display: block;
            margin-top: 4px;
            color: #666;
            font-style: italic;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .payment-filters {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }

        /* Add styles for specific status options */
        .payment-filters select option[value="success"],
        .payment-filters select option[value="Success"] {
            background-color: #d4edda;
            color: #155724;
        }

        .payment-filters select option[value="pending"],
        .payment-filters select option[value="Pending"] {
            background-color: #fff3cd;
            color: #856404;
        }

        .payment-filters select option[value="failed"],
        .payment-filters select option[value="Failed"] {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Style for the select element when different options are selected */
        .payment-filters select.status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .payment-filters select.status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .payment-filters select.status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .payment-filters select option:hover {
            background-color: #f8f9fa;
        }

        /* Firefox specific styles */
        @-moz-document url-prefix() {
            .payment-filters select {
                color: #333;
                text-shadow: 0 0 0 #000;
            }
            .payment-filters select option {
                background-color: white;
            }
        }

        /* Chrome/Safari specific styles */
        @media screen and (-webkit-min-device-pixel-ratio:0) {
            .payment-filters select {
                color: #333;
            }
            .payment-filters select option {
                background-color: white;
            }
        }

        /* Add these styles */
        .date-filter-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .btn-reset-date {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            display: none;
            transition: color 0.2s;
        }

        .btn-reset-date:hover {
            color: #dc3545;
        }

        .date-filter-container:hover .btn-reset-date {
            display: block;
        }

        .date-filter {
            padding-right: 35px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFilter = document.getElementById('date-filter');
            const resetDateBtn = document.getElementById('reset-date');
            
            // Clear the date input on page load
            dateFilter.value = '';
            
            // Add event listener for date changes
            dateFilter.addEventListener('change', function() {
                if (this.value) {
                    // Format the date for display
                    const date = new Date(this.value);
                    const formattedDate = date.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    this.setAttribute('data-date', formattedDate);
                    resetDateBtn.style.display = 'block';
                } else {
                    this.removeAttribute('data-date');
                    resetDateBtn.style.display = 'none';
                }
            });

            // Add reset button functionality
            resetDateBtn.addEventListener('click', function() {
                dateFilter.value = '';
                dateFilter.removeAttribute('data-date');
                this.style.display = 'none';
                // Trigger the filter application if needed
                document.getElementById('apply-filters').click();
            });
        });
    </script>
</head>
<body>
    <?php 
// Admin authentication has already been checked at the top of the file
?>
    
    <div id="loading-overlay">
        <div class="spinner"></div>
    </div>
    
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
                <li class="active">
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
            <h1 class="page-title">Payments</h1>
            
            <!-- Payment Summary Cards -->
            <div class="payment-summary">
                <div class="summary-card">
                    <h3>Total Revenue</h3>
                    <div class="amount">₱<?php echo number_format(getTotalRevenue(), 2); ?></div>
                </div>
                <div class="summary-card">
                    <h3>Today's Earnings</h3>
                    <div class="amount">₱<?php echo number_format(getTodayEarnings(), 2); ?></div>
                </div>
                <div class="summary-card">
                    <h3>Pending Payments</h3>
                    <div class="amount">₱<?php echo number_format(getPendingPayments(), 2); ?></div>
                </div>
            </div>

            <div class="content-card">
                <!-- Payment Filters -->
                <div class="payment-filters">
                    <input type="text" placeholder="Search by Order ID or Customer" class="search-input" id="search-input">
                    <select class="filter-select" id="status-filter">
                        <option value="">Payment Status</option>
                        <option value="success">Success</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                    <div class="date-filter-container">
                        <input type="date" class="date-filter" id="date-filter" placeholder="Select Date">
                        <button class="btn-reset-date" id="reset-date" title="Reset Date">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <button class="btn-apply" id="apply-filters">Apply Filters</button>
                </div>

                <!-- Payments Table -->
                <table>
                    <thead>                        <tr>
                            <th>Transaction ID</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="payments-table-body">
                        <?php
                        $payments = getFilteredPayments();
                        foreach ($payments as $payment) {
                            $statusClass = '';
                            switch($payment['status']) {
                                case 'success':
                                    $statusClass = 'status-success';
                                    break;
                                case 'pending':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'failed':
                                    $statusClass = 'status-failed';
                                    break;
                            }
                            ?>
                            <tr>                        <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($payment['user_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></td>
                        <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></td>                        <td><span class="payment-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars(ucfirst($payment['status'])); ?></span></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="pagination" id="pagination">
                    <?php
                    $total = getFilteredPaymentsCount();
                    $total_pages = ceil($total / 10);
                    
                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = $i == 1 ? 'active' : '';
                        echo "<a href='#' class='page-link $active' data-page='$i'>$i</a>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Show loading overlay
        function showLoading() {
            document.getElementById('loading-overlay').style.display = 'flex';
        }

        // Hide loading overlay
        function hideLoading() {
            document.getElementById('loading-overlay').style.display = 'none';
        }

        // Function to update the payments table
        function updatePaymentsTable(data) {
            const tbody = document.getElementById('payments-table-body');
            tbody.innerHTML = '';
            
            data.payments.forEach(payment => {
                let statusClass = '';
                switch(payment.status) {
                    case 'success':
                        statusClass = 'status-success';
                        break;
                    case 'pending':
                        statusClass = 'status-pending';
                        break;
                    case 'failed':
                        statusClass = 'status-failed';
                        break;
                }
                
                tbody.innerHTML += `
                    <tr>                        <td>${payment.transaction_id}</td>
                        <td>${payment.order_id}</td>
                        <td>${payment.customer_name}</td>
                        <td>${payment.date}</td>
                        <td>₱${payment.amount}</td>
                        <td>${payment.payment_method}</td>
                        <td><span class="payment-status ${statusClass}">${payment.status}</span></td>
                    </tr>
                `;
            });
            
            // Update pagination
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            
            for (let i = 1; i <= data.total_pages; i++) {
                const active = i === currentPage ? 'active' : '';
                pagination.innerHTML += `<a href="#" class="page-link ${active}" data-page="${i}">${i}</a>`;
            }
            
            // Add click events to new pagination links
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = parseInt(this.dataset.page);
                    loadPayments();
                });
            });
        }

        // Function to load payments with filters
        let currentPage = 1;
          function loadPayments() {
            showLoading();
            
            const search = document.getElementById('search-input').value.trim();
            const status = document.getElementById('status-filter').value;
            const date = document.getElementById('date-filter').value;
            
            const url = new URL('filter_payments.php', window.location.href);
            url.searchParams.append('page', currentPage);
            if (search) url.searchParams.append('search', search);
            if (status) url.searchParams.append('status', status);
            if (date) url.searchParams.append('date', date);
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    updatePaymentsTable(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading payments: ' + error.message);
                })
                .finally(() => {
                    hideLoading();
                });
        }        // Event listeners for filters
        document.getElementById('apply-filters').addEventListener('click', function() {
            currentPage = 1;
            loadPayments();
        });

        // Add input event listeners for real-time filtering
        document.getElementById('search-input').addEventListener('input', debounce(function() {
            currentPage = 1;
            loadPayments();
        }, 500));

        document.getElementById('status-filter').addEventListener('change', function() {
            currentPage = 1;
            loadPayments();
        });

        document.getElementById('date-filter').addEventListener('change', function() {
            currentPage = 1;
            loadPayments();
        });

        // Debounce function to prevent too many requests
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
        };

        // Event listeners for pagination
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = parseInt(this.dataset.page);
                loadPayments();
            });
        });

        // Function to view payment details
        function updatePaymentStatus(transactionId, newStatus) {
            if (!confirm(`Are you sure you want to mark this payment as ${newStatus}?`)) {
                return;
            }
            
            showLoading();
            
            fetch('update_payment_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `transaction_id=${encodeURIComponent(transactionId)}&status=${encodeURIComponent(newStatus)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadPayments();
                    alert('Payment status updated successfully');
                } else {
                    alert(data.message || 'Error updating payment status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating payment status');
            })
            .finally(() => {
                hideLoading();
            });
        }        function viewPaymentDetails(transactionId) {
            showLoading();
            
            fetch(`get_payment_details.php?transaction_id=${encodeURIComponent(transactionId)}`)
                .then(response => response.json())
                .then(data => {
                    const content = document.getElementById('payment-details-content');
                    
                    const details = `
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Transaction ID</div>
                            <div class="payment-detail-value">${data.transaction_id}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Order ID</div>
                            <div class="payment-detail-value">${data.order_id}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Customer</div>
                            <div class="payment-detail-value">${data.customer_name}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Email</div>
                            <div class="payment-detail-value">${data.customer_email}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Amount</div>
                            <div class="payment-detail-value">₱${data.amount}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Payment Method</div>
                            <div class="payment-detail-value">${data.payment_method}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Date</div>
                            <div class="payment-detail-value">${data.payment_date}</div>
                        </div>
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Status</div>
                            <div class="payment-detail-value">
                                <span class="payment-status ${data.status_class}">${data.status}</span>
                                ${data.payment_method === 'NeoCreds' ? '<br><small>(Auto-completed)</small>' : ''}
                            </div>
                        </div>
                        ${data.delivery_status ? `
                        <div class="payment-detail-row">
                            <div class="payment-detail-label">Delivery Status</div>
                            <div class="payment-detail-value">${data.delivery_status}</div>
                        </div>
                        ` : ''}
                    `;
                    
                    content.innerHTML = details;
                    document.getElementById('payment-modal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading payment details');
                })
                .finally(() => {
                    hideLoading();
                });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('payment-modal');
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Add JavaScript to update select background color on change
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.querySelector('select[name="status"]');
            if (statusSelect) {
                function updateSelectAppearance() {
                    const value = statusSelect.value.toLowerCase();
                    // Remove all status classes
                    statusSelect.classList.remove('status-success', 'status-pending', 'status-failed');
                    // Add the appropriate class
                    if (value) {
                        statusSelect.classList.add('status-' + value);
                    }
                }

                // Update on change
                statusSelect.addEventListener('change', updateSelectAppearance);
                
                // Set initial state
                updateSelectAppearance();
            }
        });
    </script>

    <!-- Payment Details Modal -->
    <div id="payment-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
        <div class="modal-content" style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; position: relative;">
            <span class="close-button" onclick="document.getElementById('payment-modal').style.display='none'" style="position: absolute; right: 15px; top: 10px; cursor: pointer; font-size: 20px;">&times;</span>
            <h2 style="margin-bottom: 20px;">Payment Details</h2>
            <div id="payment-details-content"></div>
            <div class="modal-footer" style="margin-top: 20px; text-align: right;">
                <button onclick="document.getElementById('payment-modal').style.display='none'" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>
</body>
</html>