<?php
require_once 'config.php';
require_once 'functions.php';

// Get all unique property types for filter
$property_types = [];
$result = $conn->query("SELECT DISTINCT property_type FROM properties WHERE property_type IS NOT NULL");
while($row = $result->fetch_assoc()) {
    $property_types[] = $row['property_type'];
}

// Handle filters
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters = array_filter([
        'location' => $_GET['location'] ?? '',
        'property_type' => $_GET['property_type'] ?? '',
        'min_price' => $_GET['min_price'] ?? '',
        'max_price' => $_GET['max_price'] ?? '',
        'min_bedrooms' => $_GET['min_bedrooms'] ?? ''
    ]);
}

$properties = getProperties($filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Listings - Airbnb Clone</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #333; background-color: #ffffff; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
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
        
        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { text-decoration: none; color: #333; font-weight: 500; transition: color 0.3s; }
        .nav-links a:hover { color: #FF385C; }
        
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
        
        /* Listings Page */
        .listings-header {
            margin: 40px 0 30px;
        }
        
        .listings-header h1 {
            font-size: 32px;
            color: #222;
            margin-bottom: 20px;
        }
        
        .filter-container {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            margin-bottom: 40px;
        }
        
        .filter-form {
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
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
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
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 18px;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .properties-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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

    <div class="container">
        <div class="listings-header">
            <h1>Find Your Perfect Stay</h1>
            <p><?= count($properties) ?> properties available</p>
        </div>
        
        <div class="filter-container">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                    <input type="text" id="location" name="location" class="form-control" 
                           placeholder="Anywhere" value="<?= htmlspecialchars($filters['location'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="property_type"><i class="fas fa-home"></i> Property Type</label>
                    <select id="property_type" name="property_type" class="form-control">
                        <option value="">Any Type</option>
                        <?php foreach($property_types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" 
                                    <?= ($filters['property_type'] ?? '') == $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="min_price"><i class="fas fa-tag"></i> Min Price</label>
                    <input type="number" id="min_price" name="min_price" class="form-control" 
                           placeholder="Min price" min="0" value="<?= htmlspecialchars($filters['min_price'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="max_price"><i class="fas fa-tag"></i> Max Price</label>
                    <input type="number" id="max_price" name="max_price" class="form-control" 
                           placeholder="Max price" min="0" value="<?= htmlspecialchars($filters['max_price'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="min_bedrooms"><i class="fas fa-bed"></i> Min Bedrooms</label>
                    <select id="min_bedrooms" name="min_bedrooms" class="form-control">
                        <option value="">Any</option>
                        <option value="1" <?= ($filters['min_bedrooms'] ?? '') == '1' ? 'selected' : '' ?>>1+</option>
                        <option value="2" <?= ($filters['min_bedrooms'] ?? '') == '2' ? 'selected' : '' ?>>2+</option>
                        <option value="3" <?= ($filters['min_bedrooms'] ?? '') == '3' ? 'selected' : '' ?>>3+</option>
                        <option value="4" <?= ($filters['min_bedrooms'] ?? '') == '4' ? 'selected' : '' ?>>4+</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary" style="height: 46px;">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <a href="listings.php" class="btn" style="height: 46px; background: #f0f0f0; text-align: center; text-decoration: none; line-height: 46px;">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
        
        <?php if(empty($properties)): ?>
            <div class="no-results">
                <i class="fas fa-search" style="font-size: 48px; margin-bottom: 20px; color: #ddd;"></i>
                <h3>No properties found</h3>
                <p>Try adjusting your filters or search criteria</p>
            </div>
        <?php else: ?>
            <div class="properties-grid">
                <?php foreach($properties as $property): ?>
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
        <?php endif; ?>
    </div>
</body>
</html>
