<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - NeoFit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Alexandria', sans-serif;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f9f9f9;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* Header Styles */
        header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            text-decoration: none;
            letter-spacing: 1px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 40px 30px;
        }

        /* Adjust container margin when header is present */
        body:has(header) .container {
            margin-top: 100px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #111;
        }
        h2 {
            font-size: 1.2rem;
            margin-top: 30px;
            color: #333;
        }
        p, li {
            font-size: 1rem;
            line-height: 1.7;
            color: #444;
        }
        ul {
            margin-left: 20px;
        }
        @media (max-width: 600px) {
            .container { padding: 20px 8px; }
            h1 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['email'])): ?>
    <header>
        <div class="header-container">
            <a href="landing_page.php" class="logo">NEOFIT</a>
        </div>
    </header>
    <?php endif; ?>

    <div class="container">
        <h1>Privacy Policy</h1>
        <h2>1. Information We Collect</h2>
        <p>We collect information that you provide directly to us, including:</p>
        <ul>
            <li>Name and contact information</li>
            <li>Billing and shipping address</li>
            <li>Payment information</li>
            <li>Account credentials</li>
            <li>Order history and preferences</li>
        </ul>
        <h2>2. How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul>
            <li>Process your orders and payments</li>
            <li>Communicate with you about your orders</li>
            <li>Send you marketing communications (with your consent)</li>
            <li>Improve our website and services</li>
            <li>Prevent fraud and enhance security</li>
        </ul>
        <h2>3. Information Sharing</h2>
        <p>We may share your information with:</p>
        <ul>
            <li>Service providers who assist in our operations</li>
            <li>Payment processors for secure transactions</li>
            <li>Shipping partners for order delivery</li>
            <li>Law enforcement when required by law</li>
        </ul>
        <p>We do not sell your personal information to third parties.</p>
        <h2>4. Cookies and Tracking</h2>
        <p>We use cookies and similar tracking technologies to:</p>
        <ul>
            <li>Remember your preferences</li>
            <li>Understand how you use our website</li>
            <li>Improve your shopping experience</li>
            <li>Provide personalized content</li>
        </ul>
        <h2>5. Data Security</h2>
        <p>We implement appropriate security measures to protect your personal information, including:</p>
        <ul>
            <li>Encryption of sensitive data</li>
            <li>Secure servers and networks</li>
            <li>Regular security assessments</li>
            <li>Limited access to personal information</li>
        </ul>
        <h2>6. Your Rights</h2>
        <p>You have the right to:</p>
        <ul>
            <li>Access your personal information</li>
            <li>Correct inaccurate data</li>
            <li>Request deletion of your data</li>
            <li>Opt-out of marketing communications</li>
            <li>Withdraw consent at any time</li>
        </ul>
        <h2>7. Children's Privacy</h2>
        <p>Our website is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13.</p>
        <h2>8. Changes to This Policy</h2>
        <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.</p>
        <div class="contact-info">
            <h3>Contact Us</h3>
            <p>If you have any questions about this Privacy Policy, please contact us at:</p>
            <p><strong>Address:</strong> Cavite State University Imus Campus<br>Palico IV, Imus, Cavite<br>Philippines 4103</p>
            <p><strong>Phone:</strong> (046) 471-6607</p>
            <p><strong>Email:</strong> imus@cvsu.edu.ph</p>
            <p><strong>Business Hours:</strong><br>Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday & Sunday: Closed</p>
        </div>
        <p class="last-updated">Last Updated: March 15, 2024</p>
    </div>
</body>
</html> 