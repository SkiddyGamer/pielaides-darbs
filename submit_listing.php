<?php
session_start();
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'submitlisting';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : $conn->real_escape_string($_POST['email']);
    
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $street_address = $conn->real_escape_string($_POST['street_address']);
    $city = $conn->real_escape_string($_POST['city']);
    $postal_code = $conn->real_escape_string($_POST['postal_code']);
    $property_name = $conn->real_escape_string($_POST['property_name']);
    $price = (float) $_POST['price'];
    $description = $conn->real_escape_string($_POST['description']);
    $guests = (int) $_POST['guests'];
    $bedrooms = (int) $_POST['bedrooms'];
    $bathrooms = (float) $_POST['bathrooms'];
    $property_type = $conn->real_escape_string($_POST['property_type']);
    $cover_index = isset($_POST['cover_index']) ? (int) $_POST['cover_index'] : 0;
    
    
    $imagePaths = [];
    if (!empty($_FILES['image']['name'][0])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['image']['tmp_name'] as $key => $tmp_name) {
            $fileName = basename($_FILES['image']['name'][$key]);
            $targetFilePath = $uploadDir . time() . "_" . $fileName;
            
            if (move_uploaded_file($tmp_name, $targetFilePath)) {
                $imagePaths[] = $targetFilePath;
            }
        }

        if (isset($imagePaths[$cover_index])) {
            $coverImage = $imagePaths[$cover_index];
            unset($imagePaths[$cover_index]);
            array_unshift($imagePaths, $coverImage);
        }
    }
    
    $imagePathsString = implode(',', $imagePaths);

    $sql = "INSERT INTO properties 
            (first_name, last_name, phone, email, street_address, city, postal_code, property_name, price, guests, bedrooms, bathrooms, property_type, description, images) 
            VALUES 
            ('$first_name', '$last_name', '$phone', '$email', '$street_address', '$city', '$postal_code', '$property_name', '$price', '$guests', '$bedrooms', '$bathrooms', '$property_type', '$description', '$imagePathsString')";

    if ($conn->query($sql) === TRUE) {
        header("Location: listings.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
