<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Alexandria&display=swap" rel="stylesheet">

    <title>NEOFIT - Elevate Your Streetwear Game</title>
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
            background-color:rgb(255, 250, 250);
            color: #fff;
        }

        /* Header Styles */
        header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: #FFFFFF;
            color: #000;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #000000;
            text-decoration: none;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        nav a {
            text-decoration: none;
            color: #1E1E1E;
            font-size: 14px;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #666;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            padding: 8px 12px;
            border-radius: 20px;
            border: 1px solid #eee;
            background-color: #f5f5f5;
            width: 180px;
        }

        .user-icon, .cart-icon {
            font-size: 18px;
            cursor: pointer;
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #55a39b;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 0;
            background-color: #fff;
            color: #000;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            height: 60vh; /* Controls height of visible video */
            overflow: hidden;
        }

        .hero-video {
            position: absolute;
            top: -20%; /* Moves video up to crop bottom */
            left: 0;
            width: 100%;
            height: auto;
            min-height: 100%;
            object-fit: cover;
            object-position: center top;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 20px;
            color: white;
            background: rgba(0, 0, 0, 0.4); /* Optional overlay for readability */
        }

        .hero-heading {
            font-size: 48px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 30px;
        }

        .cta-button {
            padding: 12px 40px;
            border: 2px solid #fff;
            background: transparent;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button:hover {
            background: #fff;
            color: #000;
        }

        .hero-subheading {
            font-size: 24px;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .cta-button1 {
            padding: 12px 40px;
            border: 2px solid #ffffff;
            background: transparent;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button1:hover {
            background: #ffffff;
            color: #1e1e1e;
            border: white;
        }

        /* Slider Styles */
        .slider-container {
            width: 100%;
            height: 500px;
            position: relative;
            overflow: hidden;
        }

        .slider {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 1;
            transition: opacity 0.5s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f5f5f5;
        }

        .slide-content {
            text-align: left;
            padding: 0 10%;
            max-width: 600px;
        }

        .slide-heading {
            font-size: 3rem;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slider-controls {
            position: absolute;
            bottom: 20px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2;
        }

        .prev-btn, .next-btn {
            background: transparent;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            margin: 0 10px;
            font-size: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background 0.3s;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        .prev-btn {
            left: 20px;
        }

        .next-btn {
            right: 20px;
        }

        .dots {
            display: flex;
            gap: 8px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.5);
            cursor: pointer;
            transition: background 0.3s;
        }

        .dot.active {
            background: black;
        }

        /* Product Section */
        .product-section {
            padding: 60px 20px;
            text-align: center;
            background: #FFFFFF;
        }

        .section-title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        .category-title {
            font-size: 24px;
            font-weight: bold;
            margin: 40px 0 30px;
            text-transform: uppercase;
            text-align: left;
            padding-left: 20px;
            border-left: 4px solid #000;
            color: #000000;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 40px;
        }

        .product-card {
            background-color: #fff;
            border-radius: 4px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            box-shadow: 0 1px 20px rgba(0, 0, 0, 0.2);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .product-card.selected {
            border: 2px solid #00a0a0;
        }

        .product-image {
            width: 100%;
            height: 250px;
            overflow: hidden;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-info {
            padding: 15px;
            text-align: left;
        }

        .product-name {
            font-size: 16px;
            margin-bottom: 5px;
            color: black;
        }

        .product-price {
            font-size: 16px;
            font-weight: bold;
            color: #00a0a0;
            display: inline-block;
        }

        .product-sold {
            font-size: 14px;
            color: #999;
            float: right;
        }

        .browse-all {
            display: inline-block;
            padding: 12px 30px;
            border: 1px solid #000;
            background: transparent;
            color: #000;
            font-size: 16px;
            margin-top: 40px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-transform: lowercase;
        }

        .browse-all:hover {
            background: #000;
            color: #fff;
        }

        /* Category sections */
        #trending-section, #men-section, #women-section {
            padding: 60px 20px;
            scroll-margin-top: 70px; /* Adds space for the sticky header */
        }

        /* Feature Section */
        .feature-section {
            background-color: #fff;
            padding: 80px 20px;
            text-align: center;
        }

        /* Empower section */
        .empower-section {
            width: 100%;
            height: 500px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-image: url('12.jpg'); /* Add your image */
            background-size: cover;         /* Cover the whole section */
            background-position: center;    /* Center the image */
            background-repeat: no-repeat;   /* Prevent repeat */
            padding: 0 10%;
            box-sizing: border-box;
            color: white;                   /* Optional: text color for visibility */
        }

        .empower-content {
            text-align: left;
            max-width: 600px;
        }

        .empower-heading {
            font-size: 3rem;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 0px;
            color: #ffffff
        }

        .empower-subheading {
            font-size: 3rem;
            line-height: 1.2;
            margin-bottom: 20px;
            color: #ffffff;
            width: 500px;
        }

        /* Footer */
        footer {
            padding: 40px 20px;
            background-color: #f5f5f5;
            color: #000;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .footer-column h3 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column li {
            margin-bottom: 10px;
        }

        .footer-column a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .footer-column a:hover {
            color: #000;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .social-icon:hover {
            background-color: #bbb;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 30px auto 0;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 12px;
            color: #999;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-container {
                flex-direction: column;
                height: auto;
                padding: 10px;
            }
            
            .image-container {
                max-width: 100%;
                height: auto;
                padding: 10px;
                margin-bottom: 20px;
            }
            
            .slider-container {
                height: 350px; /* Smaller height on mobile */
            }
            
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-heading {
                font-size: 32px;
            }
            
            .empower-subheading {
                width: 100%;
                font-size: 2rem;
            }

            .footer-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .footer-container {
                grid-template-columns: 1fr;
            }
        }    </style>
<header>
    <div class="header-container">
        <a class="logo" href="landing_page.php">NEOFIT</a>
        <nav>
            <ul>
                <li><a href="landing_page.php" class="nav-link">Home</a></li>
                <li><a href="browse_all_collection.php#men-section" class="nav-link">Men</a></li>
                <li><a href="browse_all_collection.php#women-section" class="nav-link">Women</a></li>
            </ul>
        </nav>
        <div class="header-right">
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Search" autocomplete="off">
            </div>
            <div class="user-icon"><a href="user-settings.php"> <img src="profile.jpg" alt="Profile Icon" width="24" height="24"></a></div>
            <div class="cart-icon">
                <a href="cart.php">
                    <img src="cart.jpg" alt="Cart Icon" width="24" height="24">
                    <span class="cart-count">0</span>
                </a>
            </div>
            <div class="shopping-bag-icon">
                <a href="orders.php">
                    <img src="shopping-bag.png" alt="Shopping Bag Icon" width="24" height="24">
                </a>
            </div>
            <div class="favorites-icon">
                <a href="favorites.php">
                    <img src="favorites.png" alt="Favorites Icon" width="24" height="24">
                </a>
            </div>
        </div>
    </div>
</header>

        <section id="men-section">
        <h2 class="category-title">Men</h2>
            <div class="product-grid">
                <?php
                    // Connect to Database
                    include './db.php';

                    // Fetch Products
                    $sql = "SELECT * FROM products";
                    $result = $conn->query($sql);

                    // If Product Exists
                    if ($result->num_rows > 0){
                        // Loop Through All Products
                        while($product = $result->fetch_assoc()){
                        $id = $product['id'];
                        $productName = $product['product_name'];
                        $productPrice = $product['product_price'];
                        $photoFront = "Admin Pages/" . $product['photoFront'];
                        $link = 'product_detail.php?id=' . $id;
                        $product_status = $product['product_status'];
                        $product_category = $product['product_category'];

                            // Display The Product Box
                            if($product_status == "live"){
                                if($product_category == "men"){
                                    echo '
                                    <div class="product-card" id="product-box-' . $id . '">
                                        <a href="' . $link . '" class="product-link">
                                            <div class="product-image">
                                                <img src="' . $photoFront . '" alt="' . $productName . '">
                                            </div>
                                            <div class="product-info">
                                                <h3 class="product-name">' . $productName . '</h3>
                                                <span class="product-price">₱ ' . $productPrice . '</span>
                                                <span class="product-sold">1.3k sold</span>
                                            </div>
                                        </a>
                                    </div>';
                                }
                            }
                        }
                    }
                ?>
            </div>
        </section>
            
        <section id="women-section">
        <h2 class="category-title">WOMEN</h2>
            <div class="product-grid">
                <?php
                    // Connect to Database
                    include './db.php';

                    // Fetch Products
                    $sql = "SELECT * FROM products";
                    $result = $conn->query($sql);

                    // If Product Exists
                    if ($result->num_rows > 0){
                        // Loop Through All Products
                        while($product = $result->fetch_assoc()){
                        $id = $product['id'];
                        $productName = $product['product_name'];
                        $productPrice = $product['product_price'];
                        $photoFront = "Admin Pages/" . $product['photoFront'];
                        $link = 'product_detail.php?id=' . $id;
                        $product_status = $product['product_status'];
                        $product_category = $product['product_category'];

                            // Display The Product Box
                            if($product_status == "live"){
                                if($product_category == "women"){
                                    echo '
                                    <div class="product-card" id="product-box-' . $id . '">
                                        <a href="' . $link . '" class="product-link">
                                            <div class="product-image">
                                                <img src="' . $photoFront . '" alt="' . $productName . '">
                                            </div>
                                            <div class="product-info">
                                                <h3 class="product-name">' . $productName . '</h3>
                                                <span class="product-price">₱ ' . $productPrice . '</span>
                                                <span class="product-sold">1.3k sold</span>
                                            </div>
                                        </a>
                                    </div>';
                                }
                            }
                        }
                    }
                ?>
            </div>
        </section>

        <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Simple slider functionality (if needed)
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        
        function showSlide(n) {
            slides.forEach(slide => slide.style.display = 'none');
            dots.forEach(dot => dot.classList.remove('active'));
            
            slides[n].style.display = 'flex';
            dots[n].classList.add('active');
            currentSlide = n;
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }
        
        // Add event listeners for previous and next buttons if they exist
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        
        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', prevSlide);
            nextBtn.addEventListener('click', nextSlide);
        }
        
        // Automatic slider (optional)
        setInterval(nextSlide, 5000);
        
        // Initialize first slide if slides exist
        if (slides.length > 0) {
            showSlide(0);
        }
    </script>

<footer>
    <div class="footer-container">
        <div class="footer-column">
            <h3>About Us</h3>
            <ul>
                <li><a href="#">Our Story</a></li>
                <li><a href="#">Mission and Vision</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>Shop</h3>
            <ul>
                <li><a href="#">Men</a></li>
                <li><a href="#">Women</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>Support</h3>
            <ul>
                <li><a href="#">FAQs</a></li>
                <li><a href="#">Contact Us</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>Legal</h3>
            <ul>
                <li><a href="#">Terms and Conditions</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
        </div>
    </div>
    
    <div class="footer-container">
        <div class="footer-column">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>© 2025 Neofit. All rights reserved.</p>
        </div>
    </div>
</footer>
    <script>
        // Perform client-side search
        function performSearch(query) {
            const allProducts = document.querySelectorAll('.product-card');
            const lowerQuery = query.toLowerCase();

            allProducts.forEach(product => {
                const productName = product.querySelector('.product-name').textContent.toLowerCase();
                if (productName.includes(lowerQuery)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        window.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById("search-input");

            // Populate from URL parameter if available
            const params = new URLSearchParams(window.location.search);
            const query = params.get("query");
            if (query && searchInput) {
                searchInput.value = query;
                performSearch(query);
            }

            // Real-time search as user types
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const query = this.value.trim();
                    performSearch(query);
                });
            }

            // Update cart count on load
            updateCartCount();
        });

        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.count;
                        cartCount.style.display = data.count > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>

    </body>
    </html>
