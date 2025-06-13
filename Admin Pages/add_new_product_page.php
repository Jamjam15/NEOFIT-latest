<?php

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<script>alert('Product added successfully!');</script>";
}

//DB Connection
include '../db.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEOFIT Admin - Add New Product</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }

        .form-container h2 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4d8d8b;
    }

        .form-section {
            margin-bottom: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .form-section-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 20px;
        display: flex;
        align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: #4d8d8b;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
    }

        .form-label {
        display: flex;
        align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 15px;
            color: #2c3e50;
            font-weight: 500;
        }

        .required-asterisk {
            color: #dc3545;
        font-weight: bold;
        }

        .field-description {
            font-size: 13px;
            color: #6c757d;
            margin-top: 4px;
            margin-bottom: 8px;
    }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-input:focus {
            border-color: #4d8d8b;
            box-shadow: 0 0 0 3px rgba(77, 141, 139, 0.1);
            outline: none;
        }

        .form-input:hover {
            border-color: #4d8d8b;
        }

        .input-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
    }

        .input-group-item {
        display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .input-group-label {
            font-size: 14px;
            color: #495057;
            font-weight: 500;
    }

        .custom-select-trigger {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            background: white;
    }

        .error-message {
            font-size: 13px;
            color: #dc3545;
            margin-top: 6px;
            display: none;
            padding: 4px 8px;
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: 4px;
    }

        .error-message.show {
            display: block;
            animation: fadeIn 0.3s ease;
    }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .drag-drop-zone {
            border: 2px dashed #4d8d8b;
            background-color: rgba(77, 141, 139, 0.05);
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
    }

        .drag-drop-zone:hover, .drag-drop-zone.dragover {
            background-color: rgba(77, 141, 139, 0.1);
            border-color: #3c7c7a;
        }

        .drag-drop-zone i {
            font-size: 24px;
            color: #4d8d8b;
            margin-bottom: 10px;
    }

        .drag-drop-zone p {
            color: #495057;
            font-size: 14px;
            margin: 0;
    }

        .btn-container {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
    }

        .btn {
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 500;
            letter-spacing: 0.3px;
    }

        .btn-primary {
            background-color: #4d8d8b;
            box-shadow: 0 2px 4px rgba(77, 141, 139, 0.2);
    }

        .btn-primary:hover {
            background-color: #3c7c7a;
            transform: translateY(-1px);
    }

        .image-preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
    }

        .image-preview {
            position: relative;
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
        border-radius: 4px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color 0.3s ease;
            background-color: #f8f9fa;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .image-preview:hover img {
            transform: scale(1.05);
    }

        .image-preview .placeholder {
            color: #666;
            text-align: center;
            padding: 10px;
    }

        .image-preview .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
        color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
    }

        .image-preview:hover .remove-image {
            display: flex;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 4px;
        color: white;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
    }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
    }

        .notification.show {
            opacity: 1;
    }

        /* Enhanced Dropdown Styles */
        .select-wrapper {
            position: relative;
        width: 100%;
        }

        .select-wrapper::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
            transition: transform 0.3s ease;
    }

        .select-wrapper.active::after {
            transform: translateY(-50%) rotate(180deg);
        }

        .form-input.select {
            appearance: none;
            padding-right: 30px;
            cursor: pointer;
        background-color: white;
        }

        .form-input.select:focus {
            border-color: #4d8d8b;
            box-shadow: 0 0 0 2px rgba(77, 141, 139, 0.1);
        }

        .form-input.select option {
            padding: 10px;
            background-color: white;
        color: #333;
    }
    
        .form-input.select option:hover {
            background-color: #f8f9fa;
    }
    
        .form-input.select::-ms-expand {
            display: none;
        }

        /* Custom Select Styling */
        .custom-select {
            position: relative;
        width: 100%;
        }

        .custom-select-trigger {
            padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
            background: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .custom-select-trigger:hover {
            border-color: #4d8d8b;
        }

        .custom-select-trigger.active {
            border-color: #4d8d8b;
            border-radius: 4px 4px 0 0;
        }

        .custom-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #4d8d8b;
            border-top: none;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .custom-options.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
    }
    
        .custom-option {
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .custom-option:hover {
            background-color: rgba(77, 141, 139, 0.1);
    }
    
        .custom-option.selected {
            background-color: rgba(77, 141, 139, 0.2);
            color: #4d8d8b;
        }

        /* Scrollbar styling for custom select */
        .custom-options::-webkit-scrollbar {
            width: 6px;
        }

        .custom-options::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 0 4px 0;
        }

        .custom-options::-webkit-scrollbar-thumb {
            background: #4d8d8b;
            border-radius: 3px;
    }
    
        .custom-options::-webkit-scrollbar-thumb:hover {
            background: #3c7c7a;
    }

        /* Modal for larger image preview */
        .image-preview-modal {
        display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
        justify-content: center;
            align-items: center;
        }

        .image-preview-modal.show {
            display: flex;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .modal-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    }
    
        .close-modal {
            position: absolute;
            top: -30px;
            right: -30px;
            color: white;
            font-size: 24px;
        cursor: pointer;
            background: none;
            border: none;
            padding: 5px;
        }

        .close-modal:hover {
            color: #ddd;
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
                <li class="active">
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
            <div class="content-card">
                <div class="form-container">
                    <h2>Add New Product</h2>
                    <form id="addProductForm" method="POST" action="add_new_product_backend.php" enctype="multipart/form-data">
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <h3 class="form-section-title">
                                <i class="fas fa-info-circle"></i>
                                Basic Information
                            </h3>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    Product Name
                                    <span class="required-asterisk">*</span>
                                </label>
                                <div class="field-description">Enter a unique name for your product (minimum 3 characters)</div>
                                <input type="text" name="product_name" class="form-input" required>
                                <div class="error-message" id="nameError"></div>
                            </div>

                                <div class="form-group">
                                <label class="form-label">
                                    Category
                                    <span class="required-asterisk">*</span>
                                </label>
                                <div class="field-description">Select the product category</div>
                                <div class="custom-select" id="categorySelect">
                                    <div class="custom-select-trigger">
                                        <span class="selected-text">Select Category</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="custom-options">
                                        <div class="custom-option" data-value="Men">Men</div>
                                        <div class="custom-option" data-value="Women">Women</div>
                                    </div>
                                    <input type="hidden" name="product_category" required>
                                    <div class="error-message" id="categoryError"></div>
                                </div>
                            </div>
                            </div>

                        <!-- Pricing and Stock Section -->
                        <div class="form-section">
                            <h3 class="form-section-title">
                                <i class="fas fa-tags"></i>
                                Pricing & Stock
                            </h3>

                                <div class="form-group">
                                <label class="form-label">
                                    Price
                                    <span class="required-asterisk">*</span>
                                </label>
                                <div class="field-description">Set the product price in PHP</div>
                                <input type="text" name="product_price" class="form-input" required>
                                <div class="error-message" id="priceError"></div>
                                </div>

                                <div class="form-group">
                                <label class="form-label">
                                    Stock Quantities
                                    <span class="required-asterisk">*</span>
                                </label>
                                <div class="field-description">Enter the available stock for each size</div>
                                <div class="input-group">
                                    <div class="input-group-item">
                                        <label class="input-group-label">Small</label>
                                        <input type="number" name="quantity_small" class="form-input" min="0" required>
                                    </div>
                                    <div class="input-group-item">
                                        <label class="input-group-label">Medium</label>
                                        <input type="number" name="quantity_medium" class="form-input" min="0" required>
                                    </div>
                                    <div class="input-group-item">
                                        <label class="input-group-label">Large</label>
                                        <input type="number" name="quantity_large" class="form-input" min="0" required>
                                    </div>
                                </div>
                                <div class="error-message" id="quantityError"></div>
                                </div>

                                <div class="form-group">
                                <label class="form-label">
                                    Status
                                    <span class="required-asterisk">*</span>
                                </label>
                                <div class="field-description">Set the product's visibility status</div>
                                <div class="custom-select" id="statusSelect">
                                    <div class="custom-select-trigger">
                                        <span class="selected-text">Select Status</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="custom-options">
                                        <div class="custom-option" data-value="live">Live</div>
                                        <div class="custom-option" data-value="unpublished">Unpublished</div>
                                    </div>
                                    <input type="hidden" name="product_status" required>
                                    <div class="error-message" id="statusError"></div>
                                </div>
                            </div>
                                </div>

                        <!-- Product Images Section -->
                        <div class="form-section">
                            <h3 class="form-section-title">
                                <i class="fas fa-images"></i>
                                Product Images
                            </h3>

                                <div class="form-group">
                                <label class="form-label">
                                    Front Photo
                                    <span class="required-asterisk">*</span>
                                </label>
                                <div class="field-description">Upload the main product image (Max size: 5MB, Formats: JPG, PNG, GIF)</div>
                                <div class="drag-drop-zone" id="frontPhotoZone">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Drag & drop your front photo here or click to browse</p>
                                </div>
                                <input type="file" name="photo_front" id="photoFront" class="form-input" accept="image/*" style="display: none;" required>
                                <div class="image-preview-container" id="frontPhotoPreview"></div>
                                <div class="error-message" id="frontPhotoError"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Additional Photos</label>
                                <div class="field-description">Upload up to 4 additional product images (Optional)</div>
                                <div class="drag-drop-zone" id="additionalPhotosZone">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Drag & drop additional photos here or click to browse (up to 4)</p>
                                </div>
                                <input type="file" name="photo_1" class="additional-photos" accept="image/*" style="display: none;">
                                <input type="file" name="photo_2" class="additional-photos" accept="image/*" style="display: none;">
                                <input type="file" name="photo_3" class="additional-photos" accept="image/*" style="display: none;">
                                <input type="file" name="photo_4" class="additional-photos" accept="image/*" style="display: none;">
                                <div class="image-preview-container" id="additionalPhotosPreview"></div>
                                <div class="error-message" id="additionalPhotosError"></div>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" name="product_submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i>
                                Add Product
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='all_product_page.php'">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                                </div>
                        </form>
                </div>
            </div>
        </main>
    </div>

    <div class="notification" id="notification"></div>

    <div class="image-preview-modal" id="imagePreviewModal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeImageModal()">&times;</button>
            <img src="" alt="Large preview" id="modalImage">
                    </div>
            </div>

            <script>
        // Form validation
        const form = document.getElementById('addProductForm');
        const notification = document.getElementById('notification');

        // Input restrictions and validations
        const RESTRICTIONS = {
            PRODUCT_NAME: {
                MIN_LENGTH: 3,
                MAX_LENGTH: 100,
                PATTERN: /^[a-zA-Z0-9\s\-_]+$/
            },
            PRICE: {
                MIN: 0.01,
                MAX: 1000000,
                DECIMALS: 2,
                FORMAT: /^\d+(\.\d{0,2})?$/  // Allows numbers with up to 2 decimal places
            },
            QUANTITY: {
                MIN: 0,
                MAX: 10000 // Maximum reasonable stock
            },
            IMAGE: {
                MAX_SIZE: 5 * 1024 * 1024, // 5MB
                ALLOWED_TYPES: ['image/jpeg', 'image/png', 'image/gif']
            }
        };

        // Setup input restrictions
        function setupInputRestrictions() {
            // Product Name restrictions
            const productNameInput = form.querySelector('input[name="product_name"]');
            productNameInput.setAttribute('maxlength', RESTRICTIONS.PRODUCT_NAME.MAX_LENGTH);
            productNameInput.setAttribute('pattern', RESTRICTIONS.PRODUCT_NAME.PATTERN.source);
            
            // Price restrictions
            const priceInput = form.querySelector('input[name="product_price"]');
            priceInput.setAttribute('min', RESTRICTIONS.PRICE.MIN);
            priceInput.setAttribute('max', RESTRICTIONS.PRICE.MAX);
            priceInput.setAttribute('step', `0.${'0'.repeat(RESTRICTIONS.PRICE.DECIMALS-1)}1`);

            // Quantity restrictions
            ['small', 'medium', 'large'].forEach(size => {
                const quantityInput = form.querySelector(`input[name="quantity_${size}"]`);
                quantityInput.setAttribute('min', RESTRICTIONS.QUANTITY.MIN);
                quantityInput.setAttribute('max', RESTRICTIONS.QUANTITY.MAX);
                quantityInput.setAttribute('step', '1');
            });
        }

        // Real-time validation functions
        function validateProductName(name) {
            if (!name) return 'Product name is required';
            if (name.length < RESTRICTIONS.PRODUCT_NAME.MIN_LENGTH) {
                return `Product name must be at least ${RESTRICTIONS.PRODUCT_NAME.MIN_LENGTH} characters`;
            }
            if (name.length > RESTRICTIONS.PRODUCT_NAME.MAX_LENGTH) {
                return `Product name cannot exceed ${RESTRICTIONS.PRODUCT_NAME.MAX_LENGTH} characters`;
            }
            if (!RESTRICTIONS.PRODUCT_NAME.PATTERN.test(name)) {
                return 'Product name can only contain letters, numbers, spaces, hyphens, and underscores';
            }
            if (/^\d+$/.test(name)) {
                return 'Product name cannot be numbers only';
        }
            return '';
        }

        function formatPrice(value) {
            if (!value) return '';
            
            // Remove any non-digit characters except decimal point
            value = value.replace(/[^\d.]/g, '');
            
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limit to 2 decimal places
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].slice(0, 2);
            }
            
            return value;
        }

        function validatePrice(price) {
            if (!price) return 'Price is required';
            
            if (!RESTRICTIONS.PRICE.FORMAT.test(price)) {
                return 'Please enter a valid price (e.g., 99.99)';
            }
            
            const numPrice = parseFloat(price);
            if (isNaN(numPrice)) return 'Please enter a valid price';
            
            if (numPrice < RESTRICTIONS.PRICE.MIN) {
                return `Price must be at least ₱${RESTRICTIONS.PRICE.MIN}`;
            }
            
            if (numPrice > RESTRICTIONS.PRICE.MAX) {
                return `Price cannot exceed ₱${RESTRICTIONS.PRICE.MAX.toLocaleString()}`;
            }
            
            return '';
        }

        function validateQuantity(small, medium, large) {
            const quantities = [small, medium, large];
            for (let qty of quantities) {
                if (qty < RESTRICTIONS.QUANTITY.MIN) {
                    return 'Quantities cannot be negative';
                }
                if (qty > RESTRICTIONS.QUANTITY.MAX) {
                    return `Quantity cannot exceed ${RESTRICTIONS.QUANTITY.MAX.toLocaleString()} units`;
                }
                if (!Number.isInteger(parseFloat(qty))) {
                    return 'Quantities must be whole numbers';
                }
            }
            if (quantities.reduce((a, b) => a + b, 0) === 0) {
                return 'At least one size must have stock';
            }
            return '';
        }

        // Real-time validation event listeners
        function setupRealTimeValidation() {
            // Product Name validation
            const productNameInput = form.querySelector('input[name="product_name"]');
            productNameInput.addEventListener('input', function() {
                const error = validateProductName(this.value);
                showFieldError('nameError', error);
                this.classList.toggle('error', !!error);
            });

            // Price input validation and formatting
            const priceInput = form.querySelector('input[name="product_price"]');
            priceInput.addEventListener('input', function(e) {
                let formattedValue = formatPrice(this.value);
                
                // Only update if the value has changed to prevent cursor jumping
                if (this.value !== formattedValue) {
                    const cursorPos = this.selectionStart;
                    const lengthDiff = this.value.length - formattedValue.length;
                    this.value = formattedValue;
                    this.setSelectionRange(cursorPos - lengthDiff, cursorPos - lengthDiff);
                }
                
                const error = validatePrice(this.value);
                showFieldError('priceError', error);
                this.classList.toggle('error', !!error);
            });

            priceInput.addEventListener('blur', function() {
                if (this.value && !this.value.includes('.')) {
                    this.value = this.value + '.00';
                } else if (this.value && this.value.endsWith('.')) {
                    this.value = this.value + '00';
                } else if (this.value && this.value.match(/\.\d$/)) {
                    this.value = this.value + '0';
                }
            });

            // Prevent increment/decrement on scroll
            priceInput.addEventListener('wheel', function(e) {
            e.preventDefault();
            });

            // Quantity validation
            ['small', 'medium', 'large'].forEach(size => {
                const quantityInput = form.querySelector(`input[name="quantity_${size}"]`);
                quantityInput.addEventListener('input', function() {
                    const small = parseInt(form.querySelector('input[name="quantity_small"]').value) || 0;
                    const medium = parseInt(form.querySelector('input[name="quantity_medium"]').value) || 0;
                    const large = parseInt(form.querySelector('input[name="quantity_large"]').value) || 0;
                    const error = validateQuantity(small, medium, large);
                    showFieldError('quantityError', error);
                    this.classList.toggle('error', !!error);
                });
            });
        }

        // Image validation
        async function validateImage(file) {
            return new Promise((resolve) => {
                if (!file) {
                    resolve({ valid: false, message: 'No file selected' });
            return;
        }

                if (!RESTRICTIONS.IMAGE.ALLOWED_TYPES.includes(file.type)) {
                    resolve({ valid: false, message: 'Only JPG, PNG, and GIF files are allowed' });
                    return;
                }

                if (file.size > RESTRICTIONS.IMAGE.MAX_SIZE) {
                    resolve({ valid: false, message: 'File size must be less than 5MB' });
                    return;
                }

                resolve({ valid: true });
            });
        }

        function showFieldError(elementId, error) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = error;
            errorElement.classList.toggle('show', !!error);
        }

        function showNotification(message, type) {
            notification.textContent = message;
            notification.className = `notification ${type} show`;
            setTimeout(() => {
                notification.className = 'notification';
            }, 3000);
        }

        // Initialize all validations
        setupInputRestrictions();
        setupRealTimeValidation();

        // Image preview functionality
        function createImagePreview(file, container) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-image" aria-label="Remove image">×</button>
                `;
                container.appendChild(preview);

                // Add click handler for larger preview
                preview.querySelector('img').addEventListener('click', function() {
                    showImageModal(e.target.result);
                });

                preview.querySelector('.remove-image').addEventListener('click', function(e) {
                    e.stopPropagation();
                    preview.remove();
                    const input = container.previousElementSibling;
                    input.value = '';
                    showFieldError(container.id === 'frontPhotoPreview' ? 'frontPhotoError' : 'additionalPhotosError', '');
                });
            };
            reader.readAsDataURL(file);
        }

        // Enhanced drag and drop functionality
        function setupDragDrop(zone, input, previewContainer) {
            const dragEvents = ['dragenter', 'dragover', 'dragleave', 'drop'];
            
            // Prevent default behavior for all drag events
            dragEvents.forEach(eventName => {
                zone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
            e.preventDefault();
                e.stopPropagation();
            }

            // Handle dragenter and dragover
            ['dragenter', 'dragover'].forEach(eventName => {
                zone.addEventListener(eventName, () => {
                    zone.classList.add('dragover');
                });
            });

            // Handle dragleave and drop
            ['dragleave', 'drop'].forEach(eventName => {
                zone.addEventListener(eventName, () => {
                    zone.classList.remove('dragover');
                });
            });

            // Handle drop
            zone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files, input, previewContainer);
            });

            // Handle click to upload
            zone.addEventListener('click', () => {
                input.click();
            });

            // Handle file input change
            input.addEventListener('change', () => {
                handleFiles(input.files, input, previewContainer);
            });
        }

        // Enhanced file handling
        async function handleFiles(files, input, previewContainer) {
            // Clear previous previews if this is the front photo
            if (previewContainer.id === 'frontPhotoPreview') {
                previewContainer.innerHTML = '';
            }

            // Limit additional photos to 4
            if (previewContainer.id === 'additionalPhotosPreview' && 
                previewContainer.children.length + files.length > 4) {
                showNotification('You can only upload up to 4 additional photos', 'error');
            return;
        }

            for (const file of files) {
                const validation = await validateImage(file);
                if (validation.valid) {
                    createImagePreview(file, previewContainer);
                } else {
                    showNotification(validation.message, 'error');
                }
            }
        }

        // Enhanced Custom Select Implementation
        function initializeCustomSelects() {
            document.querySelectorAll('.custom-select').forEach(select => {
                const trigger = select.querySelector('.custom-select-trigger');
                const options = select.querySelector('.custom-options');
                const hiddenInput = select.querySelector('input[type="hidden"]');
                const selectedText = trigger.querySelector('.selected-text');
                const icon = trigger.querySelector('i');

                // Close all other selects when clicking outside
                document.addEventListener('click', (e) => {
                    if (!select.contains(e.target)) {
                        closeSelect(select);
                    }
                });

                // Toggle options on trigger click
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = options.classList.contains('show');
                    
                    // Close all other selects
                    document.querySelectorAll('.custom-select').forEach(otherSelect => {
                        if (otherSelect !== select) {
                            closeSelect(otherSelect);
                        }
                    });

                    // Toggle current select
                    if (isOpen) {
                        closeSelect(select);
                    } else {
                        openSelect(select);
                    }
                });

                // Handle option selection
                select.querySelectorAll('.custom-option').forEach(option => {
                    option.addEventListener('click', () => {
                        selectOption(select, option);
                    });

                    // Keyboard navigation
                    option.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
                            selectOption(select, option);
                        }
                    });
                });

                // Keyboard navigation for trigger
                trigger.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        trigger.click();
                    }
                });
            });
        }

        function closeSelect(select) {
            const options = select.querySelector('.custom-options');
            const trigger = select.querySelector('.custom-select-trigger');
            const icon = trigger.querySelector('i');
            
            options.classList.remove('show');
            trigger.classList.remove('active');
            icon.style.transform = 'rotate(0deg)';
        }

        function openSelect(select) {
            const options = select.querySelector('.custom-options');
            const trigger = select.querySelector('.custom-select-trigger');
            const icon = trigger.querySelector('i');
            
            options.classList.add('show');
            trigger.classList.add('active');
            icon.style.transform = 'rotate(180deg)';
        }

        function selectOption(select, option) {
            const value = option.dataset.value;
            const trigger = select.querySelector('.custom-select-trigger');
            const selectedText = trigger.querySelector('.selected-text');
            const hiddenInput = select.querySelector('input[type="hidden"]');
            
            selectedText.textContent = option.textContent;
            hiddenInput.value = value;
            
            // Update selected state
            select.querySelectorAll('.custom-option').forEach(opt => {
                opt.classList.remove('selected');
                opt.setAttribute('aria-selected', 'false');
            });
            option.classList.add('selected');
            option.setAttribute('aria-selected', 'true');

            // Close dropdown
            closeSelect(select);

            // Trigger change event
            const event = new Event('change', { bubbles: true });
            hiddenInput.dispatchEvent(event);

            // Clear any error messages
            const errorElement = select.querySelector('.error-message');
            if (errorElement) {
                errorElement.textContent = '';
                errorElement.classList.remove('show');
            }
        }

        // Add validation for custom selects
        function validateSelect(select) {
            const hiddenInput = select.querySelector('input[type="hidden"]');
            const errorElement = select.querySelector('.error-message');
            
            if (!hiddenInput.value) {
                errorElement.textContent = 'Please select an option';
                errorElement.classList.add('show');
                return false;
            }
            
            errorElement.textContent = '';
            errorElement.classList.remove('show');
            return true;
        }

        // Setup drag and drop zones
        setupDragDrop(
            document.getElementById('frontPhotoZone'),
            document.getElementById('photoFront'),
            document.getElementById('frontPhotoPreview')
        );

        setupDragDrop(
            document.getElementById('additionalPhotosZone'),
            document.querySelector('.additional-photos'),
            document.getElementById('additionalPhotosPreview')
        );

        // Initialize custom selects
        initializeCustomSelects();

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate all fields
            const nameError = validateProductName(form.product_name.value);
            const priceError = validatePrice(form.product_price.value);
            const quantityError = validateQuantity(
                parseInt(form.quantity_small.value) || 0,
                parseInt(form.quantity_medium.value) || 0,
                parseInt(form.quantity_large.value) || 0
            );

            // Validate category and status
            const categoryValid = validateSelect(document.getElementById('categorySelect'));
            const statusValid = validateSelect(document.getElementById('statusSelect'));

            // Show errors if any
            showFieldError('nameError', nameError);
            showFieldError('priceError', priceError);
            showFieldError('quantityError', quantityError);

            if (nameError || priceError || quantityError || !categoryValid || !statusValid) {
                showNotification('Please fix the errors before submitting', 'error');
            return;
        }

            // Validate front photo
            const frontPhoto = document.getElementById('photoFront').files[0];
            const frontPhotoValidation = await validateImage(frontPhoto);
            if (!frontPhotoValidation.valid) {
                showFieldError('frontPhotoError', frontPhotoValidation.message);
                showNotification(frontPhotoValidation.message, 'error');
                return;
            }

            // Validate additional photos if any
            const additionalPhotos = Array.from(document.querySelectorAll('.additional-photos'))
                .map(input => input.files[0])
                .filter(file => file);

            for (const photo of additionalPhotos) {
                const validation = await validateImage(photo);
                if (!validation.valid) {
                    showFieldError('additionalPhotosError', validation.message);
                    showNotification(validation.message, 'error');
                    return;
                }
            }

            // Submit the form if all validations pass
            const formData = new FormData(form);
            fetch('add_new_product_backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Product added successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'all_product_page.php';
                    }, 1500);
                } else {
                    showNotification(data.message || 'Error adding product', 'error');
                }
            })
            .catch(error => {
                showNotification('Error adding product', 'error');
                console.error('Error:', error);
            });
        });

        // Add modal functions
        function showImageModal(imageSrc) {
            const modal = document.getElementById('imagePreviewModal');
            const modalImg = document.getElementById('modalImage');
            modalImg.src = imageSrc;
            modal.classList.add('show');
            
            // Close modal when clicking outside the image
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeImageModal();
                }
            });

            // Add escape key handler
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeImageModal();
                }
            });
        }

        function closeImageModal() {
            const modal = document.getElementById('imagePreviewModal');
            modal.classList.remove('show');
        }
    </script>
</body>
</html>
            
           
    
    