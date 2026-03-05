<?php
require_once 'config.php';
require_once 'functions.php';

// Get featured properties
$featured_properties = getProperties(null, 6);

// Handle search
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search_results = searchProperties($_GET);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airbnb Clone - Find Vacation Rentals</title>
    
    <style>
        /* Import Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap');

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #ffffff;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            line-height: 1.3;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #FF385C;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo i {
            font-size: 28px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #FF385C;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background-color: #FF385C;
            color: white;
        }

        .btn-primary:hover {
            background-color: #E31C5F;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            padding: 60px 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            color: #222;
        }

        .hero p {
            font-size: 18px;
            color: #666;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        /* Search Form */
        .search-container {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
            margin-top: 30px;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }

        .form-control {
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #FF385C;
        }

        /* Featured Properties */
        .section-title {
            font-size: 32px;
            margin: 60px 0 30px;
            color: #222;
        }

        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .property-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .property-image {
            width: 100%;
            height: 240px;
            object-fit: cover;
        }

        .property-info {
            padding: 20px;
        }

        .property-title {
            font-size: 18px;
            margin-bottom: 8px;
            color: #222;
        }

        .property-location {
            color: #666;
            font-size: 14px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .property-price {
            font-size: 18px;
            font-weight: 600;
            color: #222;
        }

        .property-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 14px;
        }

        .rating-star {
            color: #FF385C;
        }

        /* Footer */
        footer {
            background-color: #f7f7f7;
            padding: 60px 0 30px;
            margin-top: 60px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #222;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section ul li a {
            text-decoration: none;
            color: #666;
            transition: color 0.3s;
        }

        .footer-section ul li a:hover {
            color: #FF385C;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .properties-grid {
                grid-template-columns: 1fr;
            }

            .nav-container {
                flex-direction: column;
                gap: 20px;
            }

            .nav-links {
                gap: 20px;
            }
        }

        /* Results Section */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 40px 0 20px;
        }

        .sort-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            font-family: 'Inter', sans-serif;
        }

        /* Message Styles */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Amenities */
        .amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .amenity-tag {
            background: #f0f0f0;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-airbnb"></i>Airbnb
            </a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="listings.php">Browse Listings</a>
                <a href="bookings.php">My Bookings</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Find your perfect getaway</h1>
            <p>Discover unique homes and experiences around the world</p>
            
            <!-- Search Form -->
            <div class="search-container">
                <form class="search-form" method="GET" action="index.php">
                    <div class="form-group">
                        <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                        <input type="text" id="location" name="location" class="form-control" placeholder="Where are you going?" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="check_in"><i class="fas fa-calendar-alt"></i> Check-in</label>
                        <input type="date" id="check_in" name="check_in" class="form-control" value="<?= htmlspecialchars($_GET['check_in'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="check_out"><i class="fas fa-calendar-alt"></i> Check-out</label>
                        <input type="date" id="check_out" name="check_out" class="form-control" value="<?= htmlspecialchars($_GET['check_out'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="guests"><i class="fas fa-user-friends"></i> Guests</label>
                        <select id="guests" name="guests" class="form-control">
                            <option value="1" <?= ($_GET['guests'] ?? '') == '1' ? 'selected' : '' ?>>1 Guest</option>
                            <option value="2" <?= ($_GET['guests'] ?? '') == '2' ? 'selected' : '' ?>>2 Guests</option>
                            <option value="3" <?= ($_GET['guests'] ?? '') == '3' ? 'selected' : '' ?>>3 Guests</option>
                            <option value="4" <?= ($_GET['guests'] ?? '') == '4' ? 'selected' : '' ?>>4 Guests</option>
                            <option value="5" <?= ($_GET['guests'] ?? '') == '5' ? 'selected' : '' ?>>5+ Guests</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="search" class="btn btn-primary" style="height: 48px;">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Search Results (if any) -->
    <?php if(!empty($search_results)): ?>
    <section class="container">
        <div class="results-header">
            <h2 class="section-title">Search Results</h2>
            <select class="sort-select" id="sortResults" onchange="sortProperties()">
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="rating_desc">Best Rated</option>
            </select>
        </div>
        
        <div class="properties-grid">
            <?php foreach($search_results as $property): ?>
            <div class="property-card" data-price="<?= $property['price_per_night'] ?>" data-rating="<?= $property['rating'] ?>">
                <a href="property.php?id=<?= $property['id'] ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="<?= htmlspecialchars($property['title']) ?>" class="property-image">
                    <div class="property-info">
                        <h3 class="property-title"><?= htmlspecialchars($property['title']) ?></h3>
                        <p class="property-location">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['location']) ?>
                        </p>
                        <div class="property-rating">
                            <i class="fas fa-star rating-star"></i>
                            <?= number_format($property['rating'], 1) ?> · <?= $property['property_type'] ?>
                        </div>
                        <div class="amenities">
                            <?php 
                            $amenities = explode(',', $property['amenities']);
                            foreach(array_slice($amenities, 0, 3) as $amenity):
                            ?>
                                <span class="amenity-tag"><?= htmlspecialchars(trim($amenity)) ?></span>
                            <?php endforeach; ?>
                            <?php if(count($amenities) > 3): ?>
                                <span class="amenity-tag">+<?= count($amenities) - 3 ?> more</span>
                            <?php endif; ?>
                        </div>
                        <p class="property-price">$<?= number_format($property['price_per_night'], 2) ?> <span style="font-weight: normal; color: #666;">night</span></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Properties -->
    <section class="container">
        <h2 class="section-title">Featured Properties</h2>
        <div class="properties-grid">
            <?php foreach($featured_properties as $property): ?>
            <div class="property-card">
                <a href="property.php?id=<?= $property['id'] ?>" style="text-decoration: none; color: inherit;">
                    <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="<?= htmlspecialchars($property['title']) ?>" class="property-image">
                    <div class="property-info">
                        <h3 class="property-title"><?= htmlspecialchars($property['title']) ?></h3>
                        <p class="property-location">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['location']) ?>
                        </p>
                        <div class="property-rating">
                            <i class="fas fa-star rating-star"></i>
                            <?= number_format($property['rating'], 1) ?> · <?= $property['property_type'] ?>
                        </div>
                        <div class="amenities">
                            <?php 
                            $amenities = explode(',', $property['amenities']);
                            foreach(array_slice($amenities, 0, 3) as $amenity):
                            ?>
                                <span class="amenity-tag"><?= htmlspecialchars(trim($amenity)) ?></span>
                            <?php endforeach; ?>
                            <?php if(count($amenities) > 3): ?>
                                <span class="amenity-tag">+<?= count($amenities) - 3 ?> more</span>
                            <?php endif; ?>
                        </div>
                        <p class="property-price">$<?= number_format($property['price_per_night'], 2) ?> <span style="font-weight: normal; color: #666;">night</span></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Safety Information</a></li>
                        <li><a href="#">Cancellation Options</a></li>
                        <li><a href="#">Our COVID-19 Response</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Community</h3>
                    <ul>
                        <li><a href="#">Airbnb.org: disaster relief housing</a></li>
                        <li><a href="#">Support Afghan refugees</a></li>
                        <li><a href="#">Celebrating diversity & belonging</a></li>
                        <li><a href="#">Combating discrimination</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Hosting</h3>
                    <ul>
                        <li><a href="#">Try hosting</a></li>
                        <li><a href="#">AirCover: protection for Hosts</a></li>
                        <li><a href="#">Explore hosting resources</a></li>
                        <li><a href="#">Visit our community forum</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>About</h3>
                    <ul>
                        <li><a href="#">How Airbnb works</a></li>
                        <li><a href="#">Newsroom</a></li>
                        <li><a href="#">Investors</a></li>
                        <li><a href="#">Airbnb Plus</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2024 Airbnb Clone. All rights reserved. This is a demo project.</p>
            </div>
        </div>
    </footer>

    <script>
        // Set minimum date for check-in to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check_in').min = today;
        
        // Set minimum date for check-out to check-in date
        document.getElementById('check_in').addEventListener('change', function() {
            document.getElementById('check_out').min = this.value;
        });

        // Sort functionality
        function sortProperties() {
            const sortValue = document.getElementById('sortResults').value;
            const container = document.querySelector('.properties-grid');
            const cards = Array.from(container.getElementsByClassName('property-card'));
            
            cards.sort((a, b) => {
                const priceA = parseFloat(a.dataset.price);
                const priceB = parseFloat(b.dataset.price);
                const ratingA = parseFloat(a.dataset.rating);
                const ratingB = parseFloat(b.dataset.rating);
                
                switch(sortValue) {
                    case 'price_asc':
                        return priceA - priceB;
                    case 'price_desc':
                        return priceB - priceA;
                    case 'rating_desc':
                        return ratingB - ratingA;
                    default:
                        return 0;
                }
            });
            
            // Clear and re-append sorted cards
            container.innerHTML = '';
            cards.forEach(card => container.appendChild(card));
        }
    </script>
</body>
</html>
