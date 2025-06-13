<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
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

<style>
    header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
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
        margin: 0;
        padding: 0;
    }

    nav a {
        text-decoration: none;
        color: #1E1E1E;
        font-size: 14px;
        transition: color 0.3s;
        text-transform: uppercase;
    }

    nav a:hover {
        color: #55a39b;
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
        font-size: 14px;
    }

    .search-input:focus {
        outline: none;
        border-color: #55a39b;
        background-color: #fff;
    }

    .user-icon, .cart-icon, .shopping-bag-icon, .favorites-icon {
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

    @media (max-width: 768px) {
        .search-input {
            width: 150px;
        }
        
        .header-right {
            gap: 15px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const allProducts = document.querySelectorAll('.product-card');
    const productSections = document.querySelectorAll('.product-section, #men-section, #women-section');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let hasResults = false;

            // Show all sections initially
            productSections.forEach(section => {
                if (section) section.style.display = 'block';
            });

            if (searchTerm === '') {
                // If search is empty, show all products
                allProducts.forEach(product => {
                    if (product) product.style.display = 'block';
                });
                return;
            }

            allProducts.forEach(product => {
                if (product) {
                    const productName = product.querySelector('.product-name')?.textContent.toLowerCase() || '';
                    if (productName.includes(searchTerm)) {
                        product.style.display = 'block';
                        hasResults = true;
                    } else {
                        product.style.display = 'none';
                    }
                }
            });

            // Hide sections that have no visible products
            productSections.forEach(section => {
                if (section) {
                    const visibleProducts = section.querySelectorAll('.product-card[style="display: block;"]');
                    section.style.display = visibleProducts.length === 0 ? 'none' : 'block';
                }
            });

            // Scroll to first visible section if there are results
            if (hasResults) {
                const firstVisibleSection = document.querySelector('.product-section[style="display: block;"], #men-section[style="display: block;"], #women-section[style="display: block;"]');
                if (firstVisibleSection) {
                    firstVisibleSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    }
});

function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            document.querySelector('.cart-count').textContent = data.count;
        })
        .catch(error => console.error('Error:', error));
}

// Update cart count on page load
document.addEventListener('DOMContentLoaded', updateCartCount);

// Update cart count every 5 seconds
setInterval(updateCartCount, 5000);
</script>