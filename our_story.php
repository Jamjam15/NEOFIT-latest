<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Story - NEOFIT</title>
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
        }

        .story-section {
            margin-bottom: 40px;
        }

        .story-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .story-section p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 15px;
        }

        .image-container {
            margin: 30px 0;
            text-align: center;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .timeline {
            margin: 40px 0;
            position: relative;
        }

        .timeline-item {
            margin-bottom: 30px;
            padding-left: 30px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 2px;
            height: 100%;
            background-color: #ddd;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: -4px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #333;
        }

        .timeline-year {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
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


    <div class="content-container">
        <h1 class="page-title">Our Story</h1>

        <?php if (!isset($_SESSION['email'])): ?>
        <div style="background: #e6f7ff; border: 1px solid #b3e0ff; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center;">
            <strong>Enjoying our story?</strong> <br>
            <a href="index.php" style="color: #0077cc; text-decoration: underline; font-weight: bold;">Sign up now</a> to get the best of NEOFIT and exclusive member benefits!
        </div>
        <?php endif; ?>

        <div class="story-section">
            <h2>The Beginning</h2>
            <p>NEOFIT was born from a passion for streetwear and a vision to create something unique in the fashion industry. Founded in 2020, our brand emerged from the vibrant streets of Manila, where urban culture meets contemporary fashion.</p>
            
           
        </div>

        <div class="story-section">
            <h2>Our Journey</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-year">2020</div>
                    <p>NEOFIT was established with a small collection of streetwear essentials.</p>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2021</div>
                    <p>Expanded our product line to include both men's and women's collections.</p>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2022</div>
                    <p>Launched our first flagship store in Manila.</p>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2023</div>
                    <p>Introduced our sustainable fashion initiative.</p>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2024</div>
                    <p>Expanded our online presence and launched international shipping.</p>
                </div>
            </div>
        </div>

        <div class="story-section">
            <h2>Our Vision</h2>
            <p>At NEOFIT, we believe in creating more than just clothing. We're building a community of individuals who express themselves through fashion. Our vision is to be at the forefront of streetwear culture, constantly innovating and pushing boundaries while maintaining our commitment to quality and sustainability.</p>
        </div>

        <div class="story-section">
            <h2>Our Values</h2>
            <p>Quality, innovation, and community are at the heart of everything we do. We're committed to:</p>
            <ul style="list-style-position: inside; margin: 15px 0;">
                <li>Creating high-quality, durable streetwear</li>
                <li>Supporting sustainable fashion practices</li>
                <li>Fostering a diverse and inclusive community</li>
                <li>Staying true to our streetwear roots</li>
            </ul>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 