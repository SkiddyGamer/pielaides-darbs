<?php session_start(); ?>  
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nature's Cottage</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .fa-user {
            color: #333; 
            font-size: 18px;
            margin-right: 5px;
        }
        .account-icon {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            margin-top: 0px;
        }
        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .account-icon:hover {
            color: #ffe66d;
        }
        .logo a {
            text-decoration: none;
            color: inherit;
            font-weight: bold;
            font-size: 24px;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php">Nature's <span>Cottage</span></a>
    </div>
    <nav>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="listings.php">Browse Properties</a></li>
            <li><a href="details.php">List Your Property</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>

        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="account.php" class="account-icon">
                    <i class="fa-solid fa-user"></i> 
                </a>
            <?php else: ?>
                <button class="btn" id="signInButton">Sign In</button>
                <button class="btn primary" id="signUpButton">Sign Up</button>
            <?php endif; ?>
        </div>
    </nav>
</header>

</body>
</html>