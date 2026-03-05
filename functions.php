<?php
require_once 'config.php';

/**
 * Get all properties with optional filters
 */
function getProperties($filters = null, $limit = null) {
    global $conn;
    
    $sql = "SELECT * FROM properties WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($filters) {
        if (!empty($filters['location'])) {
            $sql .= " AND location LIKE ?";
            $params[] = "%" . $filters['location'] . "%";
            $types .= "s";
        }
        
        if (!empty($filters['property_type'])) {
            $sql .= " AND property_type = ?";
            $params[] = $filters['property_type'];
            $types .= "s";
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND price_per_night >= ?";
            $params[] = $filters['min_price'];
            $types .= "d";
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND price_per_night <= ?";
            $params[] = $filters['max_price'];
            $types .= "d";
        }
        
        if (!empty($filters['min_bedrooms'])) {
            $sql .= " AND bedrooms >= ?";
            $params[] = $filters['min_bedrooms'];
            $types .= "i";
        }
    }
    
    $sql .= " ORDER BY rating DESC, created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
        $types .= "i";
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Search properties based on criteria
 */
function searchProperties($data) {
    global $conn;
    
    $sql = "SELECT DISTINCT p.* FROM properties p WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($data['location'])) {
        $sql .= " AND (p.location LIKE ? OR p.title LIKE ?)";
        $params[] = "%" . $data['location'] . "%";
        $params[] = "%" . $data['location'] . "%";
        $types .= "ss";
    }
    
    if (!empty($data['check_in']) && !empty($data['check_out'])) {
        $sql .= " AND p.id NOT IN (
            SELECT property_id FROM bookings 
            WHERE (
                (check_in BETWEEN ? AND ?) 
                OR (check_out BETWEEN ? AND ?)
                OR (? BETWEEN check_in AND check_out)
            ) AND status != 'cancelled'
        )";
        $params[] = $data['check_in'];
        $params[] = $data['check_out'];
        $params[] = $data['check_in'];
        $params[] = $data['check_out'];
        $params[] = $data['check_in'];
        $types .= "sssss";
    }
    
    if (!empty($data['guests'])) {
        $sql .= " AND p.max_guests >= ?";
        $params[] = $data['guests'];
        $types .= "i";
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get single property by ID
 */
function getPropertyById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Create a new booking
 */
function createBooking($data) {
    global $conn;
    
    // Calculate total price
    $property = getPropertyById($data['property_id']);
    $check_in = new DateTime($data['check_in']);
    $check_out = new DateTime($data['check_out']);
    $nights = $check_in->diff($check_out)->days;
    $total_price = $property['price_per_night'] * $nights;
    
    $stmt = $conn->prepare("
        INSERT INTO bookings (property_id, user_id, check_in, check_out, guests, total_price, status)
        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    
    $stmt->bind_param(
        "iissid",
        $data['property_id'],
        $data['user_id'],
        $data['check_in'],
        $data['check_out'],
        $data['guests'],
        $total_price
    );
    
    return $stmt->execute();
}

/**
 * Get user bookings
 */
function getUserBookings($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT b.*, p.title, p.location, p.image_url 
        FROM bookings b
        JOIN properties p ON b.property_id = p.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Check if dates are available
 */
function checkAvailability($property_id, $check_in, $check_out, $booking_id = null) {
    global $conn;
    
    $sql = "
        SELECT COUNT(*) as count FROM bookings 
        WHERE property_id = ? 
        AND status != 'cancelled'
        AND (
            (check_in BETWEEN ? AND ?) 
            OR (check_out BETWEEN ? AND ?)
            OR (? BETWEEN check_in AND check_out)
        )
    ";
    
    if ($booking_id) {
        $sql .= " AND id != ?";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($booking_id) {
        $stmt->bind_param("isssssi", $property_id, $check_in, $check_out, $check_in, $check_out, $check_in, $booking_id);
    } else {
        $stmt->bind_param("isssss", $property_id, $check_in, $check_out, $check_in, $check_out, $check_in);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] == 0;
}

/**
 * User authentication functions
 */
function registerUser($name, $email, $password) {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    
    return $stmt->execute();
}

function loginUser($email, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>
