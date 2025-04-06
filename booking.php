<?php
session_start(); 


$host = 'localhost';
$username = 'root';
$password = '';
$database_booking = 'submitlisting';
$conn_booking = new mysqli($host, $username, $password, $database_booking);


$database_users = 'login';
$conn_users = new mysqli($host, $username, $password, $database_users);


if ($conn_booking->connect_error || $conn_users->connect_error) {
    die("Connection failed: " . $conn_booking->connect_error . " / " . $conn_users->connect_error);
}


if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to make a booking.'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id']; 
$property_id = $_POST['property_id'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$total_price = $_POST['total_price'];


$user_query = $conn_users->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();

if ($user_result->num_rows == 0) {
    echo "<script>alert('Invalid user. Please log in again.'); window.location.href='login.php';</script>";
    exit();
}

$user = $user_result->fetch_assoc(); 
$user_query->close();

if (!empty($property_id) && !empty($start_date) && !empty($end_date) && !empty($total_price)) {
    
    $stmt = $conn_booking->prepare("INSERT INTO bookings (property_id, user_id, start_date, end_date, total_price, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisss", $property_id, $user_id, $start_date, $end_date, $total_price);

    if ($stmt->execute()) {
        echo "<script>alert('Booking successful!'); window.location.href='listings.php?id=$property_id';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('Please provide all booking details.'); window.history.back();</script>";
}


$conn_booking->close();
$conn_users->close();
?>
