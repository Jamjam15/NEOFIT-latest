<?php
// database connection
include 'db.php';
include 'includes/address_functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$house_number = '';
$street = '';
$place_type = '';
$barangay = '';
$city = '';
$province = '';
$region = '';
$contact = '';
$profile_picture = '';

if (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name'];
} else {
    $user_name = 'Guest';
}

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Get address data
    $address_data = get_user_address($user_id, $conn);
    if ($address_data) {
        $house_number = $address_data['house_number'];
        $street = $address_data['street'];
        $place_type = $address_data['place_type'];
        $barangay = $address_data['barangay'];
        $city = $address_data['city'];
        $province = $address_data['province'];
        $region = $address_data['region'];
    }

    // Get contact number and profile picture
    $stmt = $conn->prepare("SELECT contact, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($contact, $profile_picture);
    $stmt->fetch();
    $stmt->close();

    // Get complete address string
    $complete_address = get_complete_address($address_data);
}

$conn->close();
?>