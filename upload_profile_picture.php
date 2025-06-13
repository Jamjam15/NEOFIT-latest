<?php
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $response = ['success' => false, 'message' => ''];

    // Check if file was uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            $response['message'] = 'Invalid file type. Please upload a JPG, PNG, or GIF image.';
            echo json_encode($response);
            exit();
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $response['message'] = 'File is too large. Maximum size is 5MB.';
            echo json_encode($response);
            exit();
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = 'uploads/profile_pictures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Delete old profile picture if exists
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($old_picture);
            $stmt->fetch();
            $stmt->close();

            if ($old_picture && file_exists($old_picture)) {
                unlink($old_picture);
            }

            // Update database with new profile picture path
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $upload_path, $user_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Profile picture updated successfully.';
                $response['picture_path'] = $upload_path;
            } else {
                $response['message'] = 'Failed to update profile picture in database.';
                // Delete uploaded file if database update fails
                unlink($upload_path);
            }
            $stmt->close();
        } else {
            $response['message'] = 'Failed to upload file.';
        }
    } else {
        $response['message'] = 'No file uploaded or upload error occurred.';
    }

    echo json_encode($response);
    exit();
}

$conn->close();
?> 