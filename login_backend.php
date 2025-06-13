<?php
// Start session to track user login status
session_start();

//Database Connection
include 'db_connection.php';

// Function to send JSON response
function sendJsonResponse($status, $message) {
    header('Content-Type: application/json');
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get email and password from the POST request
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if email contains '@' (except for admin)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== "admin@1") {
        sendJsonResponse('error', 'Invalid email format');
    }

    // Check if both fields are filled
    if (empty($email) || empty($password)) {
        sendJsonResponse('error', 'Email and password are required');
    }

    // Handle admin login
    if ($email === "admin@1") {
        $stmt = $conn->prepare("SELECT password FROM admin WHERE email = 'admin@1'");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            // If password matches stored password or is the default 'admin'
            if ($password === $admin['password'] || ($admin['password'] === 'admin' && $password === 'admin')) {
                $_SESSION['admin@1'] = true;
                sendJsonResponse('success', 'Admin login successful');
            } else {
                sendJsonResponse('error', 'Invalid email or password');
            }
        } else {
            // If no admin record exists, create one with default password
            $stmt = $conn->prepare("INSERT INTO admin (email, password) VALUES ('admin@1', 'admin')");
            if ($stmt->execute() && $password === 'admin') {
                $_SESSION['admin@1'] = true;
                sendJsonResponse('success', 'Admin login successful');
            } else {
                sendJsonResponse('error', 'Invalid email or password');
            }
        }
        $stmt->close();
        exit();
    }

    // Regular user login
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If the user exists
    if ($stmt->num_rows > 0) {
        // Bind the result variables
        $stmt->bind_result($id, $db_email, $db_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $db_password)) {
            // Password is correct, set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $db_email;

            // Get User's name to display
            $stmt_name = $conn->prepare("SELECT first_name, last_name FROM users WHERE email = ?");
            $stmt_name->bind_param("s", $db_email);
            $stmt_name->execute();
            $stmt_name->bind_result($first_name, $last_name);
            $stmt_name->fetch();

            //Store the name in session
            $user_name = $first_name . ' ' . $last_name;
            $_SESSION['user_name'] = $user_name;

            // Send success response
            sendJsonResponse('success', 'Login successful');
        } else {
            sendJsonResponse('error', 'Invalid email or password');
        }
    } else {
        sendJsonResponse('error', 'Invalid email or password');
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
