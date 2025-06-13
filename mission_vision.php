<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mission & Vision - NEOFIT</title>
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

        .mission-vision-section {
            margin-bottom: 60px;
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .mission-vision-section h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .mission-vision-section p {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 20px;
            text-align: center;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .value-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-5px);
        }

        .value-icon {
            font-size: 40px;
            color: #333;
            margin-bottom: 15px;
        }

        .value-card h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }

        .value-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .content-container {
                padding: 20px;
            }

            .page-title {
                font-size: 28px;
            }

            .mission-vision-section {
                padding: 20px;
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
        <h1 class="page-title">Mission & Vision</h1>

        <?php if (!isset($_SESSION['email'])): ?>
        <div style="background: #e6f7ff; border: 1px solid #b3e0ff; padding: 20px; margin-bottom: 30px; border-radius: 8px; text-align: center;">
            <strong>Discover our mission and vision?</strong> <br>
            <a href="index.php" style="color: #0077cc; text-decoration: underline; font-weight: bold;">Sign up now</a> to join the NEOFIT community and enjoy exclusive benefits!
        </div>
        <?php endif; ?>

        <div class="mission-vision-section">
            <h2>Our Mission</h2>
            <p>To revolutionize streetwear fashion by creating high-quality, sustainable clothing that empowers individuals to express their unique style while making a positive impact on the environment and society.</p>
        </div>

        <div class="mission-vision-section">
            <h2>Our Vision</h2>
            <p>To become the leading streetwear brand that sets new standards in sustainable fashion, innovation, and community engagement, while inspiring the next generation of fashion enthusiasts to make conscious choices.</p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <i class="fas fa-leaf value-icon"></i>
                <h3>Sustainability</h3>
                <p>Committed to eco-friendly practices and sustainable materials in our production process.</p>
            </div>

            <div class="value-card">
                <i class="fas fa-heart value-icon"></i>
                <h3>Quality</h3>
                <p>Dedicated to creating durable, high-quality products that stand the test of time.</p>
            </div>

            <div class="value-card">
                <i class="fas fa-users value-icon"></i>
                <h3>Community</h3>
                <p>Building a diverse and inclusive community of fashion enthusiasts and creators.</p>
            </div>

            <div class="value-card">
                <i class="fas fa-lightbulb value-icon"></i>
                <h3>Innovation</h3>
                <p>Continuously pushing boundaries in design, technology, and sustainable practices.</p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html> 