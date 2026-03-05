<?php
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$property_id = intval($_GET['id']);
$property = getPropertyById($property_id);

if (!$property) {
    header('Location: index.php');
    exit();
}

// Handle booking form submission
$booking_success = false;
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    if (!isLoggedIn()) {
        header('Location: login.php?redirect=property.php?id=' . $property_id);
        exit();
    }
    
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = intval($_POST['guests']);
    
    // Validate dates
    if (strtotime($check_in) < strtotime('today')) {
        $booking_error = 'Check-in date cannot be in the past';
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $booking_error = 'Check-out date must be after check-in date';
    } elseif ($guests > $property['max_guests']) {
        $booking_error = 'Number of guests exceeds property maximum';
    } elseif (!checkAvailability($property_id, $check_in, $check_out)) {
        $booking_error = 'Property is not available for the selected dates';
    } else {
        // Create booking
        $booking_data = [
            'property_id' => $property_id,
            'user_id' => $_SESSION['user_id'],
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests
        ];
        
        if (createBooking($booking_data)) {
            $booking_success = true;
        } else {
            $booking_error = 'Failed to create booking. Please try again.';
        }
    }
}

// Calculate price details
$check_in = $_POST['check_in'] ?? date('Y-m-d', strtotime('+2 days'));
$check_out = $_POST['check_out'] ?? date('Y-m-d', strtotime('+5 days'));
$nights = 1;
if ($check_in && $check_out) {
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date1->diff($date2)->days;
}
$total_price = $property['price_per_night'] * $nights;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title']) ?> - Airbnb Clone</title>
    <style>
        /* Reuse styles from index.php with additions */
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
        
        /* Property Details */
        .property-header {
            margin-top: 40px;
        }
        
        .property-title {
            font-size: 32px;
            margin-bottom: 10px;
            color: #222;
        }
        
        .property-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            color: #666;
            font-size: 14px;
        }
        
        .property-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .property-gallery {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 10px;
            height: 500px;
            margin-bottom: 40px;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .main-image {
            grid-row: 1 / span 2;
            grid-column: 1;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .property-details-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin: 40px 0;
        }
        
        .property-info-section {
            border-bottom: 1px solid #eee;
            padding: 40px 0;
        }
        
        .property-info-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #222;
        }
        
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .amenity-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .booking-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 100px;
        }
        
        .price-per-night {
            font-size: 28px;
            font-weight: 600;
            color: #222;
        }
        
        .price-period {
            font-size: 16px;
            font-weight: normal;
            color: #666;
        }
        
        .booking-form {
            margin-top: 20px;
        }
        
        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .date-group {
            display: flex;
            flex-direction: column;
        }
        
        .date-group label {
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 5px;
            color: #555;
        }
        
        .date-group input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
        }
        
        .guests-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            margin-bottom: 20px;
        }
        
        .price-breakdown {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }
        
        .price-row.total {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 10px;
            font-weight: 600;
            color: #222;
            font-size: 18px;
        }
        
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
        
        @media (max-width: 768px) {
            .property-details-container {
                grid-template-columns: 1fr;
            }
            
            .property-gallery {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .main-image {
                grid-row: 1;
                grid-column: 1;
                height: 300px;
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
        <?php if($booking_success): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> Booking confirmed! Check your email for details.
            </div>
        <?php elseif($booking_error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($booking_error) ?>
            </div>
        <?php endif; ?>
        
        <div class="property-header">
            <h1 class="property-title"><?= htmlspecialchars($property['title']) ?></h1>
            <div class="property-meta">
                <span><i class="fas fa-star"></i> <?= number_format($property['rating'], 1) ?></span>
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['location']) ?></span>
                <span><i class="fas fa-home"></i> <?= htmlspecialchars($property['property_type']) ?></span>
                <span><i class="fas fa-user-friends"></i> Up to <?= $property['max_guests'] ?> guests</span>
                <span><i class="fas fa-bed"></i> <?= $property['bedrooms'] ?> bedrooms</span>
                <span><i class="fas fa-bath"></i> <?= $property['bathrooms'] ?> bathrooms</span>
            </div>
        </div>
        
        <div class="property-gallery">
            <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="Main property image" class="main-image">
            <!-- Additional images would go here -->
            <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="Property image 2" style="width: 100%; height: 245px; object-fit: cover;">
            <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="Property image 3" style="width: 100%; height: 245px; object-fit: cover;">
            <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="Property image 4" style="width: 100%; height: 245px; object-fit: cover;">
            <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="Property image 5" style="width: 100%; height: 245px; object-fit: cover;">
        </div>
        
        <div class="property-details-container">
            <div>
                <div class="property-info-section">
                    <h2>About this place</h2>
                    <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
                </div>
                
                <div class="property-info-section">
                    <h2>Amenities</h2>
                    <div class="amenities-grid">
                        <?php 
                        $amenities = explode(',', $property['amenities']);
                        foreach($amenities as $amenity):
                            $trimmed = trim($amenity);
                            if($trimmed):
                        ?>
                            <div class="amenity-item">
                                <i class="fas fa-check"></i>
                                <span><?= htmlspecialchars($trimmed) ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="booking-card">
                <div class="price-per-night">
                    $<?= number_format($property['price_per_night'], 2) ?>
                    <span class="price-period"> / night</span>
                </div>
                
                <form method="POST" class="booking-form">
                    <div class="date-inputs">
                        <div class="date-group">
                            <label>CHECK-IN</label>
                            <input type="date" name="check_in" id="check_in" 
                                   value="<?= htmlspecialchars($_POST['check_in'] ?? date('Y-m-d', strtotime('+2 days'))) ?>"
                                   required>
                        </div>
                        <div class="date-group">
                            <label>CHECKOUT</label>
                            <input type="date" name="check_out" id="check_out" 
                                   value="<?= htmlspecialchars($_POST['check_out'] ?? date('Y-m-d', strtotime('+5 days'))) ?>"
                                   required>
                        </div>
                    </div>
                    
                    <select name="guests" class="guests-select" required>
                        <option value="">Guests</option>
                        <?php for($i = 1; $i <= $property['max_guests']; $i++): ?>
                            <option value="<?= $i ?>" <?= ($_POST['guests'] ?? '') == $i ? 'selected' : '' ?>>
                                <?= $i ?> guest<?= $i > 1 ? 's' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    
                    <?php if(isLoggedIn()): ?>
                        <button type="submit" name="book" class="btn btn-primary" style="width: 100%; padding: 16px;">
                            Book Now
                        </button>
                    <?php else: ?>
                        <a href="login.php?redirect=property.php?id=<?= $property_id ?>" class="btn btn-primary" style="display: block; text-align: center; padding: 16px; text-decoration: none;">
                            Login to Book
                        </a>
                    <?php endif; ?>
                    
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>$<?= number_format($property['price_per_night'], 2) ?> × <?= $nights ?> nights</span>
                            <span>$<?= number_format($property['price_per_night'] * $nights, 2) ?></span>
                        </div>
                        <div class="price-row">
                            <span>Service fee</span>
                            <span>$<?= number_format($property['price_per_night'] * $nights * 0.14, 2) ?></span>
                        </div>
                        <div class="price-row total">
                            <span>Total</span>
                            <span>$<?= number_format($total_price + ($total_price * 0.14), 2) ?></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Set minimum dates
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check_in').min = today;
        
        // Update checkout min date when checkin changes
        document.getElementById('check_in').addEventListener('change', function() {
            document.getElementById('check_out').min = this.value;
            
            // Update price if both dates are set
            updatePrice();
        });
        
        document.getElementById('check_out').addEventListener('change', function() {
            updatePrice();
        });
        
        function updatePrice() {
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            
            if (checkIn && checkOut) {
                const date1 = new Date(checkIn);
                const date2 = new Date(checkOut);
                const nights = Math.ceil((date2 - date1) / (1000 * 60 * 60 * 24));
                
                if (nights > 0) {
                    // Update price display
                    const pricePerNight = <?= $property['price_per_night'] ?>;
                    const total = pricePerNight * nights;
                    const serviceFee = total * 0.14;
                    const grandTotal = total + serviceFee;
                    
                    // You could update price elements here if needed
                }
            }
        }
    </script>
</body>
</html>
