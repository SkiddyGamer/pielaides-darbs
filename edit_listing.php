<?php
session_start();
include 'connect.php';

$host = 'localhost';
$username = 'root';
$password = '';
$database_listing = 'submitlisting';
$conn_listing = new mysqli($host, $username, $password, $database_listing);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = '';

$user_query = "SELECT email FROM users WHERE Id = $user_id";
$user_result = $conn->query($user_query);

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
    $user_email = $user['email'];
    $_SESSION['user_email'] = $user_email; 
} else {
    header("Location: index.php");
    exit();
}

$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$listing = [];
$images = [];
if ($listing_id > 0) {
    $query = "SELECT * FROM properties WHERE id = $listing_id AND email = '$user_email'";
    $result = $conn_listing->query($query);
    
    if ($result->num_rows > 0) {
        $listing = $result->fetch_assoc();
        if (!empty($listing['images'])) {
            $images = explode(',', $listing['images']);
        }
    } 
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $property_name = $conn_listing->real_escape_string($_POST['property_name']);
    $price = (float)$_POST['price'];
    $description = $conn_listing->real_escape_string($_POST['description']);
    $guests = (int)$_POST['guests'];
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (float)$_POST['bathrooms'];
    $property_type = $conn_listing->real_escape_string($_POST['property_type']);
    
    
    $existing_images = isset($_POST['existing_images']) ? $_POST['existing_images'] : [];
    $image_paths = [];
    
   
    foreach ($images as $index => $image) {
        if (in_array($index, $existing_images)) {
            $image_paths[] = $image;
        }
    }
    
    
    if (!empty($_FILES['new_images']['name'][0])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = basename($_FILES['new_images']['name'][$key]);
                $targetFilePath = $uploadDir . time() . "_" . $fileName;
                
                if (move_uploaded_file($tmp_name, $targetFilePath)) {
                    $image_paths[] = $targetFilePath;
                }
            }
        }
    }
    
    $image_paths_string = implode(',', $image_paths);
    
    
    $update_query = "UPDATE properties SET 
                    property_name = '$property_name',
                    price = $price,
                    description = '$description',
                    guests = $guests,
                    bedrooms = $bedrooms,
                    bathrooms = $bathrooms,
                    property_type = '$property_type',
                    images = '$image_paths_string'
                    WHERE id = $listing_id AND email = '$user_email'";
    
    if ($conn_listing->query($update_query) === TRUE) {
        header("Location: account.php?success=1");
        exit();
    } else {
        $error = "Error updating listing: " . $conn_listing->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .image-preview-item {
            position: relative;
            width: 150px;
            height: 150px;
        }
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #5c7f4a;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, 
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            min-height: 100px;
        }
        .btn {
            padding: 10px 20px;
	border: none;
	cursor: pointer;
	border-radius: 25px;
	background-color: #5c7f4a;
	color: white;
	font-size: 16px;
	font-weight: bold;
	transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-danger {
            background-color: #A12C68;
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

<main>
    <section class="edit-listing-form">
        <h2>Edit Your Listing</h2>
        
        <?php if (isset($error)): ?>
            <div class="error" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="edit_listing.php?id=<?php echo $listing_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="property_name">Property Name</label>
                <input type="text" id="property_name" name="property_name" 
                       value="<?php echo htmlspecialchars($listing['property_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price per Night (€)</label>
                <input type="number" id="price" name="price" step="0.01" 
                       value="<?php echo htmlspecialchars($listing['price'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php 
                    echo htmlspecialchars($listing['description'] ?? ''); 
                ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="guests">Maximum Guests</label>
                <input type="number" id="guests" name="guests" 
                       value="<?php echo htmlspecialchars($listing['guests'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="bedrooms">Bedrooms</label>
                <input type="number" id="bedrooms" name="bedrooms" 
                       value="<?php echo htmlspecialchars($listing['bedrooms'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="bathrooms">Bathrooms</label>
                <input type="number" id="bathrooms" name="bathrooms" step="0.5" 
                       value="<?php echo htmlspecialchars($listing['bathrooms'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="property_type">Property Type</label>
                <select id="property_type" name="property_type" required>
                    
                    <option value="Villa" <?php echo (isset($listing['property_type']) && $listing['property_type'] == 'Villa') ? 'selected' : ''; ?>>Villa</option>
                    <option value="Apartment" <?php echo (isset($listing['property_type']) && $listing['property_type'] == 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                    <option value="House" <?php echo (isset($listing['property_type']) && $listing['property_type'] == 'House') ? 'selected' : ''; ?>>House</option>
                    <option value="Cabin" <?php echo (isset($listing['property_type']) && $listing['property_type'] == 'Cabin') ? 'selected' : ''; ?>>Cabin</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Current Images</label>
                <div class="image-preview">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="image-preview-item">
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Property Image">
                            <input type="checkbox" name="existing_images[]" value="<?php echo $index; ?>" checked style="display: none;">
                            <button type="button" class="remove-image" onclick="this.parentElement.remove()">x</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="new_images">Add New Images</label>
                <input type="file" id="new_images" name="new_images[]" multiple accept="image/*">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Update Listing</button>
                <a href="account.php" class="btn btn-danger">Cancel</a>
            </div>
        </form>
    </section>
</main>

<footer>
    <p>© 2025 NaturesCottage. Crafted with ❤️ for your perfect vacation.</p>
    <div class="social-links">
            <a href="#">Facebook</a>
            <a href="#">Instagram</a>
            <a href="#">Twitter</a>
</footer>

<script>
    
    document.querySelectorAll('.remove-image').forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.querySelector('input[type="checkbox"]').removeAttribute('checked');
        });
    });
</script>
</body>
</html>

<?php
$conn_listing->close();
?>