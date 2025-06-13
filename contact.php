<?php
session_start();
include 'db.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Store message in database
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success_message = "Thank you for your message. We'll get back to you soon!";
        } else {
            $error_message = "Error sending message. Please try again later.";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - NEOFIT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #fff;
            color: #000;
        }


        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            text-decoration: none;
            letter-spacing: 1px;
        }

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            flex: 1;
        }

        /* Adjust container margin when header is present */
        body:has(header) .content-container {
            margin-top: 100px;
        }

        .page-title {
            font-size: 36px;
            margin-bottom: 30px;
            text-align: center;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }

        .contact-info {
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .contact-info h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .info-item {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
        }

        .info-icon {
            font-size: 20px;
            color: #333;
            margin-right: 15px;
            width: 24px;
        }

        .info-content h3 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }

        .info-content p {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }

        .contact-form {
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .contact-form h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-size: 16px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-button {
            background-color: #333;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #444;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .map-container {
            margin-top: 40px;
            border-radius: 8px;
            overflow: hidden;
        }

        .map-container iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .content-container {
                padding: 20px;
            }

            .page-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
     <?php include 'header.php'; ?>
    <?php if (isset($_SESSION['email'])): ?>
    <?php endif; ?>

    <div class="content-container">
        <h1 class="page-title">Contact Us</h1>

        <?php if (!isset($_SESSION['email'])): ?>
        <div style="background: #e6f7ff; border: 1px solid #b3e0ff; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center;">
            <strong>Want to reach out?</strong> <br>
            <a href="index.php" style="color: #0077cc; text-decoration: underline; font-weight: bold;">Sign up now</a> to get priority support and member-only updates!
        </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="contact-grid">
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt info-icon"></i>
                    <div class="info-content">
                        <h3>Address</h3>
                        <p>Cavite State University - Imus Campus<br>
                           Palico IV, Imus City, Cavite</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-phone info-icon"></i>
                    <div class="info-content">
                        <h3>Phone</h3>
                        <p>+63 912 345 6789</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope info-icon"></i>
                    <div class="info-content">
                        <h3>Email</h3>
                        <p>support@neofit.com</p>
                    </div>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock info-icon"></i>
                    <div class="info-content">
                        <h3>Business Hours</h3>
                        <p>Monday - Friday: 9:00 AM - 6:00 PM<br>
                           Saturday: 10:00 AM - 4:00 PM<br>
                           Sunday: Closed</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" class="submit-button">Send Message</button>
                </form>
            </div>
        </div>

        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3877.235073289889!2d120.9407853148307!3d14.429547189889073!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c9e2e2e2e2e3%3A0x7e2e2e2e2e2e2e2e!2sCavite%20State%20University%20Imus%20Campus!5e0!3m2!1sen!2sph!4v1686241234567!5m2!1sen!2sph" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 