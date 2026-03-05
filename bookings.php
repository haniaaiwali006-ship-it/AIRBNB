<?php
require_once 'config.php';
require_once 'functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$bookings = getUserBookings($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Airbnb Clone</title>
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
        
        /* Bookings Page */
        .bookings-header {
            margin: 40px 0 30px;
        }
        
        .bookings-header h1 {
            font-size: 32px;
            color: #222;
            margin-bottom: 10px;
        }
        
        .bookings-grid {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .booking-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            display: grid;
            grid-template-columns: 300px 1fr;
            transition: transform 0.3s;
        }
        
        .booking-card:hover {
            transform: translateY(-4px);
        }
        
        .booking-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .booking-info {
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .booking-header {
            margin-bottom: 20px;
        }
        
        .booking-title {
            font-size: 20px;
            color: #222;
            margin-bottom: 10px;
        }
        
        .booking-location {
            color: #666;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }
        
        .booking-dates {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .date-group {
            display: flex;
            flex-direction: column;
        }
        
        .date-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .date-value {
            font-weight: 500;
            color: #222;
        }
        
        .booking-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .booking-price {
            font-size: 24px;
            font-weight: 600;
            color: #222;
        }
        
        .booking-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .no-bookings {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }
        
        .no-bookings i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .booking-card {
                grid-template-columns: 1fr;
            }
            
            .booking-image {
                height: 200px;
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
                <a href="logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="bookings-header">
            <h1>My Bookings</h1>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
        </div>
        
        <?php if(empty($bookings)): ?>
            <div class="no-bookings">
                <i class="fas fa-calendar-alt"></i>
                <h3>No bookings yet</h3>
                <p>Start planning your next trip!</p>
                <a href="listings.php" class="btn btn-primary" style="margin-top: 20px;">Browse Properties</a>
            </div>
        <?php else: ?>
            <div class="bookings-grid">
                <?php foreach($bookings as $booking): ?>
                <div class="booking-card">
                    <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="Property image" class="booking-image">
                    <div class="booking-info">
                        <div>
                            <div class="booking-header">
                                <h3 class="booking-title"><?= htmlspecialchars($booking['title']) ?></h3>
                                <p class="booking-location">
                                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['location']) ?>
                                </p>
                                <div class="booking-dates">
                                    <div class="date-group">
                                        <span class="date-label">CHECK-IN</span>
                                        <span class="date-value"><?= date('M j, Y', strtotime($booking['check_in'])) ?></span>
                                    </div>
                                    <div class="date-group">
                                        <span class="date-label">CHECKOUT</span>
                                        <span class="date-value"><?= date('M j, Y', strtotime($booking['check_out'])) ?></span>
                                    </div>
                                    <div class="date-group">
                                        <span class="date-label">GUESTS</span>
                                        <span class="date-value"><?= $booking['guests'] ?> guest<?= $booking['guests'] > 1 ? 's' : '' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="booking-footer">
                            <div class="booking-price">
                                $<?= number_format($booking['total_price'], 2) ?>
                                <span style="font-size: 14px; font-weight: normal; color: #666;">total</span>
                            </div>
                            <div class="booking-status status-confirmed">
                                <?= ucfirst($booking['status']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
