<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - NEOFIT</title>
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

        .terms-section {
            margin-bottom: 40px;
        }

        .terms-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .terms-section h3 {
            font-size: 20px;
            margin: 25px 0 15px;
            color: #444;
        }

        .terms-section p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 15px;
        }

        .terms-section ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .terms-section li {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 10px;
        }

        .last-updated {
            font-style: italic;
            color: #888;
            margin-top: 40px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .content-container {
                padding: 20px;
            }

            .page-title {
                font-size: 28px;
            }

            .terms-section h2 {
                font-size: 22px;
            }

            .terms-section h3 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

 <?php include 'header.php'; ?>
    <?php if (isset($_SESSION['email'])): ?>
    <?php endif; ?>

    <div class="content-container">
        <h1 class="page-title">Terms and Conditions</h1>

        <?php if (!isset($_SESSION['email'])): ?>
        <div style="background: #e6f7ff; border: 1px solid #b3e0ff; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center;">
            <strong>Read our terms?</strong> <br>
            <a href="index.php" style="color: #0077cc; text-decoration: underline; font-weight: bold;">Sign up now</a> to enjoy a seamless shopping experience and exclusive perks!
        </div>
        <?php endif; ?>

        <div class="terms-section">
            <h2>1. Introduction</h2>
            <p>Welcome to NEOFIT. These terms and conditions outline the rules and regulations for the use of our website and services.</p>
            <p>By accessing this website, we assume you accept these terms and conditions in full. Do not continue to use NEOFIT's website if you do not accept all of the terms and conditions stated on this page.</p>
        </div>

        <div class="terms-section">
            <h2>2. Intellectual Property Rights</h2>
            <p>Unless otherwise stated, NEOFIT and/or its licensors own the intellectual property rights for all material on NEOFIT. All intellectual property rights are reserved.</p>
            <p>You may view and/or print pages from our website for your own personal use subject to restrictions set in these terms and conditions.</p>
        </div>

        <div class="terms-section">
            <h2>3. User Account</h2>
            <p>To access certain features of our website, you must register for an account. You agree to:</p>
            <ul>
                <li>Provide accurate and complete information when creating your account</li>
                <li>Maintain the security of your account and password</li>
                <li>Notify us immediately of any unauthorized use of your account</li>
                <li>Accept responsibility for all activities that occur under your account</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2>4. Product Information</h2>
            <p>We strive to display our products as accurately as possible. However, we cannot guarantee that your computer monitor's display of any color will be accurate.</p>
            <p>We reserve the right to limit the sales of our products to any person, geographic region, or jurisdiction.</p>
        </div>

        <div class="terms-section">
            <h2>5. Pricing and Payment</h2>
            <p>All prices are in Philippine Pesos (₱) and are subject to change without notice. We reserve the right to modify or discontinue any product without notice at any time.</p>
            <p>Payment must be made in full before the order is processed. We accept various payment methods as indicated during checkout.</p>
        </div>

        <div class="terms-section">
            <h2>6. Shipping and Delivery</h2>
            <p>Shipping times may vary depending on your location and the shipping method selected. We are not responsible for delays beyond our control.</p>
            <p>Risk of loss and title for items purchased pass to you upon delivery of the items to the carrier.</p>
        </div>

        <div class="terms-section">
            <h2>7. Returns and Refunds</h2>
            <p>We accept returns within 30 days of delivery. Items must be unworn, unwashed, and in their original packaging with all tags attached.</p>
            <p>Refunds will be processed within 7-14 business days after we receive and inspect the returned item.</p>
        </div>

        <div class="terms-section">
            <h2>8. Privacy Policy</h2>
            <p>Your use of our website is also governed by our Privacy Policy. Please review our Privacy Policy, which also governs the site and informs users of our data collection practices.</p>
        </div>

        <div class="terms-section">
            <h2>9. Limitation of Liability</h2>
            <p>In no event shall NEOFIT, nor any of its officers, directors, and employees, be liable to you for anything arising out of or in any way connected with your use of this website.</p>
        </div>

        <div class="terms-section">
            <h2>10. Changes to Terms</h2>
            <p>We reserve the right to modify these terms at any time. We will notify users of any changes by updating the "Last Updated" date of these terms.</p>
        </div>

        <p class="last-updated">Last Updated: March 15, 2024</p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 