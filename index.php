<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'submitlisting';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT id, property_name, price, images FROM properties ORDER BY id DESC LIMIT 3";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - NaturesCottage</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'Header.php'; ?>
    <?php include 'login_form.php'; ?>

<style>.hero {
                height: 100vh; 
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                text-align: center;
                padding-top: 40px; 
            }

.hero-content {
                position: absolute;
                color: white;
                text-align: center;
                max-width: 800px; 
                padding: 20px;
                transform: scale(0.8); 
                margin-top: 80px; 
            }

.hero h1 {
    font-size: 2rem; 
}

.hero p {
    font-size: 1rem; 
}

.search-filters {
    display: flex;
    gap: 10px; 
    justify-content: center;
    align-items: center;
    flex-wrap: nowrap; 
    max-width: 100%;
}

.search-filters input,
.search-filters button {
    padding: 8px;
    font-size: 14px;
    width: auto; 
    flex: 1; 
    min-width: 150px; 
}

.search-filters button {
    white-space: nowrap; 
}


.properties {
    margin-top: -210px; 
}</style>

    <main>
    <section class="hero">
    <video class="background-video" autoplay muted loop>
        <source src="2 meginajums.mp4" type="video/mp4" />
    </video>
    <div class="hero-content">
        <h1>Discover Your Dream Vacation</h1>
        <p>Explore unique properties and create unforgettable memories.</p>
        <form class="search-filters" action="listings.php" method="GET">
            <input type="text" name="location" placeholder="Where are you going?" required />
            <input type="text" name="dates" id="date-range" placeholder="Select dates" required />
            <input type="number" name="guests" placeholder="Guests" min="1" required />
            <button type="submit" class="btn primary">Search</button>
        </form>
    </div>
</section>

        
        <section class="properties">
    <h2>Featured Properties</h2>
    <p class="section-subtitle">
        Recently added properties for your next trip.
    </p>
    <div class="property-grid">
        <?php
        $sql = "SELECT id, property_name, price, images FROM properties ORDER BY id DESC LIMIT 3";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $images = explode(',', $row['images']);
                $imageSrc = !empty($images[0]) ? $images[0] : 'https://via.placeholder.com/200x150';
                ?>
                <div class="property-card">
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="Property Image">
                            <div class="property-info">
                                <h3><?php echo htmlspecialchars($row['property_name']); ?></h3>
                                <p class="price">€<?php echo htmlspecialchars($row['price']); ?> / night</p>
                                
                            </div>
                            <a href="property_details.php?id=<?php echo $row['id']; ?>" class="btn view-details">View Details</a>
                        </div>
                <?php
            }
        } else {
            echo "<p>No properties found.</p>";
        }
        ?>
    </div>
</section>

<style>
    .property-grid {
    display: flex;
    justify-content: center;
    gap: 80px;
    flex-wrap: wrap;
}

.property-card {
    width: 300px;
    padding: 15px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.property-card:hover {
    transform: scale(1.02);
}

.property-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
}

.property-card h3 {
    font-size: 18px;
    margin: 10px 0;
    color: #000; 
}

.property-card p {
    font-size: 16px;
    color: #000; 
}

.property-card .price {
    font-size: 14px;
    font-weight: bold;
    color: #222;
    margin-top: 10px;
}



</style>

    </main>

    <footer>
        <p>© 2025 NaturesCottage. Crafted with ❤️ for your perfect vacation.</p>
        <div class="social-links">
            <a href="#">Facebook</a>
            <a href="#">Instagram</a>
            <a href="#">Twitter</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#date-range", {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: {
            firstDayOfWeek: 1 
        }
    });
</script>
</body>
</html>

<?php
$conn->close();
?>
