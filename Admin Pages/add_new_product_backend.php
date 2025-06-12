<?php
// Database connection
include '../db.php';

// Set JSON response header
header('Content-Type: application/json');

function sendResponse($success, $message = '', $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function uploadPhoto($inputName, $isRequired = false) {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] == UPLOAD_ERR_NO_FILE) {
        if ($isRequired) {
            return ['success' => false, 'message' => "$inputName is required."];
        }
        return ['success' => true, 'path' => null];
    }

    if ($_FILES[$inputName]['error'] == UPLOAD_ERR_OK) {
        $tmpName = $_FILES[$inputName]['tmp_name'];
        $fileName = time() . "_" . basename($_FILES[$inputName]['name']);
        $uploadDir = "../uploads/";
        $targetFile = $uploadDir . $fileName;

        // Check if file is an image
        $fileType = mime_content_type($tmpName);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'message' => "Only JPG, PNG, and GIF files are allowed."];
        }

        // Check file size (max 5MB)
        if ($_FILES[$inputName]['size'] > 5000000) {
            return ['success' => false, 'message' => "File is too large (max 5MB)."];
        }

        // Create uploads directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return ['success' => false, 'message' => "Failed to create upload directory. Please contact administrator."];
            }
            chmod($uploadDir, 0777); // Ensure directory has proper permissions
        }

        // Extra checks with detailed error messages
        if (!is_dir($uploadDir)) {
            return ['success' => false, 'message' => "Upload directory '$uploadDir' does not exist and could not be created."];
        }

        if (!is_writable($uploadDir)) {
            return ['success' => false, 'message' => "Upload directory '$uploadDir' is not writable. Please check permissions."];
        }

        // Move the uploaded file
        if (move_uploaded_file($tmpName, $targetFile)) {
            // Set proper permissions on the uploaded file
            chmod($targetFile, 0777);
            return ['success' => true, 'path' => $targetFile];
        } else {
            $error = error_get_last();
            return ['success' => false, 'message' => "Failed to move uploaded file: " . ($error ? $error['message'] : 'Unknown error')];
        }
    }

    // Map PHP upload errors to user-friendly messages
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.",
        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
        UPLOAD_ERR_NO_FILE => "No file was uploaded.",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
        UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
    ];

    $errorMessage = isset($uploadErrors[$_FILES[$inputName]['error']]) 
        ? $uploadErrors[$_FILES[$inputName]['error']] 
        : "Unknown upload error code: " . $_FILES[$inputName]['error'];

    return ['success' => false, 'message' => $errorMessage];
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get and validate form data
        $product_name = trim($_POST['product_name'] ?? '');
        $quantity_small = intval($_POST['quantity_small'] ?? 0);
        $quantity_medium = intval($_POST['quantity_medium'] ?? 0);
        $quantity_large = intval($_POST['quantity_large'] ?? 0);
        $product_price = floatval($_POST['product_price'] ?? 0);
        $product_status = $_POST['product_status'] ?? '';
        $product_category = $_POST['product_category'] ?? '';

        // Validate product name
        if (empty($product_name)) {
            sendResponse(false, "Product name is required.");
        }

        if (strlen($product_name) < 3) {
            sendResponse(false, "Product name must be at least 3 characters.");
        }

        if (preg_match('/^[0-9]+$/', $product_name)) {
            sendResponse(false, "Product name cannot be numbers only.");
        }

        if (preg_match('/^[^a-zA-Z0-9]+$/', $product_name)) {
            sendResponse(false, "Product name cannot contain only special characters.");
        }

        // Check if name already exists
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM products WHERE BINARY product_name = ?");
        $stmtCheck->bind_param("s", $product_name);
        $stmtCheck->execute();
        $stmtCheck->bind_result($nameCount);
        $stmtCheck->fetch();
        $stmtCheck->close();

        if ($nameCount > 0) {
            sendResponse(false, "A product with this name already exists.");
        }

        // Validate price and quantities
        if ($product_price <= 0) {
            sendResponse(false, "Price must be greater than 0.");
        }

        if ($quantity_small < 0 || $quantity_medium < 0 || $quantity_large < 0) {
            sendResponse(false, "Quantities cannot be negative.");
        }

        if ($quantity_small + $quantity_medium + $quantity_large === 0) {
            sendResponse(false, "At least one size must have stock.");
        }

        // Validate category and status
        if (!in_array($product_category, ['Men', 'Women'])) {
            sendResponse(false, "Invalid category selected.");
        }

        if (!in_array($product_status, ['live', 'unpublished'])) {
            sendResponse(false, "Invalid status selected.");
        }

        // Handle photo uploads
        $photoFront = uploadPhoto('photo_front', true);
        if (!$photoFront['success']) {
            sendResponse(false, "Front photo: " . $photoFront['message']);
        }

        $photo1 = uploadPhoto('photo_1');
        $photo2 = uploadPhoto('photo_2');
        $photo3 = uploadPhoto('photo_3');
        $photo4 = uploadPhoto('photo_4');

        // Check if any additional photo upload failed
        foreach ([$photo1, $photo2, $photo3, $photo4] as $photo) {
            if (!$photo['success']) {
                sendResponse(false, "Additional photo: " . $photo['message']);
            }
        }

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO products 
            (product_name, quantity_small, quantity_medium, quantity_large, product_price, 
            product_status, photoFront, photo1, photo2, photo3, photo4, product_category) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "siiidsssssss",
            $product_name,
            $quantity_small,
            $quantity_medium,
            $quantity_large,
            $product_price,
            $product_status,
            $photoFront['path'],
            $photo1['path'],
            $photo2['path'],
            $photo3['path'],
            $photo4['path'],
            $product_category
        );

        if ($stmt->execute()) {
            sendResponse(true, "Product added successfully!");
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }

    } catch (Exception $e) {
        sendResponse(false, "Error: " . $e->getMessage());
    }
} else {
    sendResponse(false, "Invalid request method.");
}
?>
