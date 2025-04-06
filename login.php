<?php
session_start();
include 'connect.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['Id'];
            $_SESSION['user_name'] = $user['firstName'];
            $_SESSION['user_email'] = $user['email'];

             
             $redirect = isset($_POST['redirect']) && !empty($_POST['redirect']) ? $_POST['redirect'] : 'index.php';
             header("Location: " . $redirect);
             exit();
         } else {
             echo "Incorrect password!";
        }
    } else {
        echo "No user found with that email!";
    }
}

?>
