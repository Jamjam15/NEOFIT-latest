<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - NEOFIT</title>
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

        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            flex: 1;
        }

  

        .page-title {
            font-size: 36px;
            margin-bottom: 30px;
            text-align: center;
            padding-top: 100px;
        }

        .policy-section {
            margin-bottom: 40px;
        }

        .policy-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .policy-section h3 {
            font-size: 20px;
            margin: 25px 0 15px;
            color: #444;
        }

        .policy-section p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 15px;
        }

        .policy-section ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .policy-section li {
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

        .contact-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .contact-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .contact-info p {
            color: #666;
            margin-bottom: 5px;
        }
        

        @media (max-width: 768px) {
            .content-container {
                padding: 20px;
            }

            .page-title {
                font-size: 28px;
            }

            .policy-section h2 {
                font-size: 22px;
            }

            .policy-section h3 {
                font-size: 18px;
            }   
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
    <div class="content-container">
        <h1 class="page-title">Privacy Policy</h1>

        <?php if (!isset($_SESSION['email'])): ?>
        <div style="background: #e6f7ff; border: 1px solid #b3e0ff; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center;">
            <strong>We care about your privacy!</strong> <br>
            <a href="index.php" style="color: #0077cc; text-decoration: underline; font-weight: bold;">Sign up now</a> to get the best of NEOFIT and member-only privacy features!
        </div>
        <?php endif; ?>

        <div class="policy-section">
            <h2>1. Information We Collect</h2>
            <p>We collect information that you provide directly to us, including:</p>
            <ul>
                <li>Name and contact information</li>
                <li>Billing and shipping address</li>
                <li>Payment information</li>
                <li>Account credentials</li>
                <li>Order history and preferences</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>2. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process your orders and payments</li>
                <li>Communicate with you about your orders</li>
                <li>Send you marketing communications (with your consent)</li>
                <li>Improve our website and services</li>
                <li>Prevent fraud and enhance security</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>3. Information Sharing</h2>
            <p>We may share your information with:</p>
            <ul>
                <li>Service providers who assist in our operations</li>
                <li>Payment processors for secure transactions</li>
                <li>Shipping partners for order delivery</li>
                <li>Law enforcement when required by law</li>
            </ul>
            <p>We do not sell your personal information to third parties.</p>
        </div>

        <div class="policy-section">
            <h2>4. Cookies and Tracking</h2>
            <p>We use cookies and similar tracking technologies to:</p>
            <ul>
                <li>Remember your preferences</li>
                <li>Understand how you use our website</li>
                <li>Improve your shopping experience</li>
                <li>Provide personalized content</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>5. Data Security</h2>
            <p>We implement appropriate security measures to protect your personal information, including:</p>
            <ul>
                <li>Encryption of sensitive data</li>
                <li>Secure servers and networks</li>
                <li>Regular security assessments</li>
                <li>Limited access to personal information</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>6. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Correct inaccurate data</li>
                <li>Request deletion of your data</li>
                <li>Opt-out of marketing communications</li>
                <li>Withdraw consent at any time</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>7. Children's Privacy</h2>
            <p>Our website is not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13.</p>
        </div>

        <div class="policy-section">
            <h2>8. Changes to This Policy</h2>
            <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.</p>
        </div>

        <div class="contact-info">
            <h3>Contact Us</h3>
            <p>If you have any questions about this Privacy Policy, please contact us at:</p>
            <p>Email: imus@cvsu.edu.ph</p>
            <p>Phone: (046) 471-6607</p>
            <p>Address: Cavite State University Imus Campus, Palico IV, Imus, Cavite, Philippines 4103</p>
        </div>

        <p class="last-updated">Last Updated: March 15, 2024</p>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 