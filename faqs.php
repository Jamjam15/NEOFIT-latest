<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs - NEOFIT</title>
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

        /* Adjust container margin when header is present */
        body:has(header) .content-container {
            margin-top: 100px;
        }

        .page-title {
            font-size: 36px;
            margin-bottom: 30px;
            text-align: center;
        }

        .faq-section {
            margin-bottom: 40px;
        }

        .faq-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .faq-item {
            margin-bottom: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }

        .faq-question {
            padding: 20px;
            background-color: #f9f9f9;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s;
        }

        .faq-question:hover {
            background-color: #f0f0f0;
        }

        .faq-question h3 {
            font-size: 18px;
            color: #333;
            margin: 0;
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease;
        }

        .faq-answer.active {
            padding: 20px;
            max-height: 1000px;
        }

        .faq-answer p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
        }

        .faq-toggle {
            font-size: 20px;
            color: #666;
            transition: transform 0.3s;
        }

        .faq-toggle.active {
            transform: rotate(180deg);
        }

        .contact-section {
            text-align: center;
            margin-top: 40px;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .contact-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .contact-section p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .contact-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .contact-button:hover {
            background-color: #444;
        }

        @media (max-width: 768px) {
            .content-container {
                padding: 20px;
            }

            .page-title {
                font-size: 28px;
            }

            .faq-question h3 {
                font-size: 16px;
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
        <h1 class="page-title">Frequently Asked Questions</h1>

        <?php if (!isset($_SESSION['email'])): ?>
        <div style="background: #e6f7ff; border: 1px solid #b3e0ff; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center;">
            <strong>Have more questions?</strong> <br>
            <a href="index.php" style="color: #0077cc; text-decoration: underline; font-weight: bold;">Sign up now</a> to get personalized support and exclusive offers!
        </div>
        <?php endif; ?>

        <div class="faq-section">
            <h2>Ordering & Shipping</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How long does shipping take?</h3>
                    <span class="faq-toggle"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-answer">
                    <p>Standard shipping within the Philippines takes 3-5 business days. International shipping typically takes 7-14 business days, depending on the destination.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>What payment methods do you accept?</h3>
                    <span class="faq-toggle"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-answer">
                    <p>We accept major credit cards (Visa, MasterCard, American Express), PayPal, and local payment methods such as GCash and Maya.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Do you ship internationally?</h3>
                    <span class="faq-toggle"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-answer">
                    <p>Yes, we ship to most countries worldwide. International shipping rates and delivery times vary by location.</p>
                </div>
            </div>
        </div>

        <div class="faq-section">
            <h2>Returns & Exchanges</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What is your return policy?</h3>
                    <span class="faq-toggle"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-answer">
                    <p>We accept returns within 30 days of delivery. Items must be unworn, unwashed, and in their original packaging with all tags attached.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I exchange an item?</h3>
                    <span class="faq-toggle"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-answer">
                    <p>To exchange an item, please contact our customer service team within 30 days of delivery. We'll guide you through the exchange process.</p>
                </div>
            </div>
        </div>

        <div class="faq-section">
            <h2>Product Information</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    <h3>How do I find my size?</h3>
                    <span class="faq-toggle"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-answer">
                    <p>We provide detailed size charts for each product. You can find these in the product description or by clicking the "Size Guide" link on the product page.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Are your products sustainable?</h3>
                    <span class="faq-toggle"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-answer">
                    <p>Yes, we're committed to sustainable fashion. Our products are made using eco-friendly materials and ethical manufacturing processes.</p>
                </div>
            </div>
        </div>

        <div class="contact-section">
            <h2>Still Have Questions?</h2>
            <p>Can't find what you're looking for? Our customer service team is here to help.</p>
            <a href="contact.php" class="contact-button">Contact Us</a>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script>
        // FAQ Toggle Functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const toggle = question.querySelector('.faq-toggle');
                
                // Close all other answers
                document.querySelectorAll('.faq-answer').forEach(otherAnswer => {
                    if (otherAnswer !== answer) {
                        otherAnswer.classList.remove('active');
                        otherAnswer.previousElementSibling.querySelector('.faq-toggle').classList.remove('active');
                    }
                });

                // Toggle current answer
                answer.classList.toggle('active');
                toggle.classList.toggle('active');
            });
        });
    </script>
</body>
</html> 