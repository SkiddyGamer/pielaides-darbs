<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Session error: User not logged in. Session ID: " . session_id());
}


$host = 'localhost';
$username = 'root';
$password = '';


$conn_booking = new mysqli($host, $username, $password, 'submitlisting');
if ($conn_booking->connect_error) {
    die("Booking DB Connection failed: " . $conn_booking->connect_error);
}


$conn_users = new mysqli($host, $username, $password, 'login');
if ($conn_users->connect_error) {
    die("Users DB Connection failed: " . $conn_users->connect_error);
}


$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($property_id <= 0) {
    die("Invalid property ID");
}


$property_query = $conn_booking->prepare("SELECT property_name FROM properties WHERE id = ?");
$property_query->bind_param("i", $property_id);
$property_query->execute();
$property_result = $property_query->get_result();

if ($property_result->num_rows === 0) {
    die("Property not found");
}
$property = $property_result->fetch_assoc();


$user_email_query = $conn_users->prepare("SELECT email FROM users WHERE id = ?");
$user_email_query->bind_param("i", $_SESSION['user_id']);
$user_email_query->execute();
$user_email_result = $user_email_query->get_result();
$user_email = $user_email_result->fetch_assoc()['email'];


$ownership_query = $conn_booking->prepare("SELECT id FROM properties WHERE id = ? AND email = ?");
$ownership_query->bind_param("is", $property_id, $user_email);
$ownership_query->execute();
$ownership_result = $ownership_query->get_result();

if ($ownership_result->num_rows === 0) {
    die("You don't have permission to view bookers for this property");
}


$bookings_query = $conn_booking->prepare("
    SELECT b.*, u.firstName, u.lastName, u.email, u.phoneNumber 
    FROM bookings b
    JOIN login.users u ON b.user_id = u.id
    WHERE b.property_id = ?
    ORDER BY b.start_date DESC
");
$bookings_query->bind_param("i", $property_id);
$bookings_query->execute();
$bookings_result = $bookings_query->get_result();
$bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookers - <?php echo htmlspecialchars($property['property_name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #5c7f4a;
            padding-bottom: 10px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #5c7f4a;
            text-decoration: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #5c7f4a;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .completed { background-color: #98FB98; }
        .upcoming { background-color: #A12C68; }
        .active { background-color: #5c7f4a; }
        .debug-info {
            background: #ffecec;
            padding: 10px;
            border-left: 3px solid red;
            margin: 10px 0;
            font-family: monospace;
        }
        
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 35px;
            background: linear-gradient(45deg, #45523e, #5c7f4a);
            color: white;
            position: relative;
            top: 0;
            z-index: 1000;
            width: 100%;
            box-sizing: border-box;
        }
        
        header .logo {
            font-size: 24px;
            font-weight: bold;
            color: #98FB98;
            margin-right: auto; 
            padding-left: -8px; 
            transform: translateX(-20px); 
        }
        
        header .logo span {
            color: #ffffff;
        }
        
        nav {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 50px;
        }
        
        .nav-links {
            display: flex;
            gap: 45px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-links li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
        }
        
        .nav-links li a:hover {
            color: #ffe66d;
        }
        
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 25px;
            background-color: #5c7f4a;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
        }
        
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        
        footer {
	text-align: center;
	padding: 100px;
	background-color: #333;
	color: white;
}

footer .social-links a {
	color: #ffa69e;
	margin: 0 10px;
	font-size: 1rem;
}
    </style>
</head>
<body>
    <header>
        <div class="logo">Nature's<span> Cottage</span></div>
        <nav>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="listings.php">Browse properties</a></li>
                <li><a href="details.php">List your property</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="logout.php" class="btn">Log out</a>
            </div>
        </nav>
    </header>
<body>
    <div class="container">
        <a href="account.php" class="back-link">← Back to Your Account</a>
        <h1>Bookers for: <?php echo htmlspecialchars($property['property_name']); ?></h1>
        
        <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Booker Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Dates</th>
                        <th>Total Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): 
                        $today = date("Y-m-d");
                        if ($booking['start_date'] > $today) {
                            $status = 'upcoming';
                        } elseif ($booking['end_date'] < $today) {
                            $status = 'completed';
                        } else {
                            $status = 'active';
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['firstName'] . ' ' . $booking['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($booking['email']); ?></td>
                        <td><?php echo htmlspecialchars($booking['phoneNumber']); ?></td>
                        <td>
                            <?php echo date("M j, Y", strtotime($booking['start_date'])); ?> - 
                            <?php echo date("M j, Y", strtotime($booking['end_date'])); ?>
                        </td>
                        <td>€<?php echo number_format($booking['total_price'], 2); ?></td>
                        <td><span class="status <?php echo $status; ?>"><?php echo ucfirst($status); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No bookings found for this property.</p>
        <?php endif; ?>
        
        
        
    </div>
    <main><footer>
        <p>© 2025 NaturesCottage. Crafted with ❤️ for your perfect vacation.</p>
        <div class="social-links">
            <a href="#">Facebook</a>
            <a href="#">Instagram</a>
            <a href="#">Twitter</a>
        </div>
    </footer></main>
</body>
</html>

<?php
$conn_booking->close();
$conn_users->close();
?>