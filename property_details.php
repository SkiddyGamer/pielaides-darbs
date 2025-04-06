<?php

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

$property_id = isset($_GET['id']) ? $_GET['id'] : 0;
$sql = "SELECT * FROM properties WHERE id = ?";
$stmt = $conn_booking->prepare($sql);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_dates = [];
$booking_sql = "SELECT start_date, end_date FROM bookings WHERE property_id = ?";
$booking_stmt = $conn_booking->prepare($booking_sql);
$booking_stmt->bind_param("i", $property_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
while ($row = $booking_result->fetch_assoc()) {
    $start = new DateTime($row['start_date']);
    $end = new DateTime($row['end_date']);
    while ($start <= $end) {
        $booked_dates[] = $start->format('Y-m-d');
        $start->modify('+1 day');
    }
}
$booked_dates_json = json_encode($booked_dates);


if ($result->num_rows > 0) {
    $property = $result->fetch_assoc();
    $images = explode(',', $property['images']); 

    function censorPhoneNumber($phone) {
        return '+371 ' . substr($phone, 0, 2) . str_repeat('*', strlen($phone) - 4) . substr($phone, -2);
    }

    function censorEmail($email) {
        $parts = explode('@', $email);
        return substr($parts[0], 0, 2) . str_repeat('*', max(0, strlen($parts[0]) - 2)) . '@' . $parts[1];
    }
    
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($property['property_name']); ?> - Property Details</title>
        <link rel="stylesheet" href="styles.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
        
        <style>
            .description-container {
                max-width: 713.3px;
                margin-top: 15px;
            }
            .total-price-container { display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; margin-top: 10px; }
 
        </style>
        <script>
            function revealContact(id) {
                let element = document.getElementById(id);
                element.innerHTML = element.dataset.full;
            }
        </script>
        <style>
            .property-detail { width: 80%; margin: auto; padding: 20px; }
            .gallery-map-container { display: flex; justify-content: space-between; align-items: flex-start; }
            .image-gallery { position: relative; max-width: 60%; }
            .image-gallery img { width: 713.3px; height: 470px; object-fit: contain; border-radius: 10px; display: none; }
            .image-gallery img.active { display: block; }
            .image-gallery button { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 10px; cursor: pointer; }
            .image-gallery .prev { left: 10px; }
            .image-gallery .next { right: 10px; }
            .map-container { width: 400px; height: 300px; margin: auto; border: 1px solid black; }
            #map { width: 100%; height: 100%; border-radius: 10px; }
            .booking-container { text-align: center; margin-top: 10px; }
            #date-range { padding: 8px; font-size: 14px; width: 100%; margin-top: 10px; }
            .book-now { background-color: #5c7f4a; color: white; border: none; padding: 12px 18px; cursor: pointer; font-size: 16px; border-radius: 25px; display: block; width: 100%; margin-top: 15px; font-weight: bold; }
        </style>
    </head>
    <body>
        <?php include 'Header.php'; ?>
        <?php include 'login_form.php'; ?>
        
        <main>
            <div class="property-detail">
                <h1><?php echo htmlspecialchars($property['property_name']); ?></h1>
                
                <p class="price"><strong><i class="fas fa-map-marker-alt"></i></strong> <?php echo htmlspecialchars($property['street_address']) . ", " . htmlspecialchars($property['city']) . ", " . htmlspecialchars($property['postal_code']); ?></p>                
                <div class="gallery-map-container">
                    <div class="image-gallery">
                        <?php foreach ($images as $index => $image) {
                            $trimmedImage = trim($image);
                            if (!empty($trimmedImage)) {
                                echo '<img class="slide ' . ($index == 0 ? 'active' : '') . '" src="' . htmlspecialchars($trimmedImage) . '" alt="Property Image">';
                            }
                        } ?>
                        <button class="prev" onclick="prevSlide()">&#10094;</button>
                        <button class="next" onclick="nextSlide()">&#10095;</button>
                    </div>
                    
                    <div>
                        <div class="map-container">
                            <div id="map"></div>
                        </div>
                        <div class="booking-container">
                        <?php if (isset($_SESSION['user_id'])): ?>  
    
    <form action="booking.php" method="POST">
        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
        <input type="text" id="date-range" name="date_range" placeholder="Select dates" required>

        
        <div class="total-price-container">
            <span>Total Price:</span>
            <span id="total-price">€0</span>
        </div>

        <input type="hidden" id="start_date" name="start_date">
        <input type="hidden" id="end_date" name="end_date">
        <input type="hidden" id="total_price" name="total_price">

        <button type="submit" class="book-now">Book Now</button>
    </form>
    <?php else: ?>  
    
    <div style="text-align: center; margin-top: 10px;">
        <p style ="color: #6c757d;">Please sign in to make a booking!</p>
        <?php include 'login_form.php'; ?>  
    </div>
<?php endif; ?>
</div>

                    </div>
                </div>
                
                <div class="description-container">
                    <p><strong></strong> <?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
                <p class="price" style="margin-top: 10px;"><font size='5'><strong>Price:</strong></font><font size='6'> €<?php echo htmlspecialchars($property['price']); ?></font><strong> per night</strong> </p>
                
                
                <h3><strong>Contact:</strong></h3>
                <p><strong>Phone:</strong> <span id="phone" data-full="+371 <?php echo htmlspecialchars($property['phone']); ?>" onclick="revealContact('phone')" style="cursor: pointer; color: black;"> <?php echo censorPhoneNumber($property['phone']); ?> (Reveal Number)</span></p>
                <p><strong>Email:</strong> <span id="email" data-full="<?php echo htmlspecialchars($property['email']); ?>" onclick="revealContact('email')" style="cursor: pointer; color: black;"> <?php echo censorEmail($property['email']); ?> (Reveal Email)</span></p>
            </div>
        </main>
        <footer>
        <p>© 2025 NaturesCottage. Crafted with ❤️ for your perfect vacation.</p>
        <div class="social-links">
            <a href="#">Facebook</a>
            <a href="#">Instagram</a>
            <a href="#">Twitter</a>
        </div>
    </footer>
         
    
    </div>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script>
            let map = L.map('map').setView([56.8796, 24.6032], 6);
            let marker = L.marker([56.8796, 24.6032]).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            function updateMap(query) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            let lat = parseFloat(data[0].lat);
                            let lon = parseFloat(data[0].lon);
                            map.setView([lat, lon], 13);
                            marker.setLatLng([lat, lon]);
                        }
                    })
                    .catch(error => console.error('Error fetching location:', error));
            }
            document.addEventListener('DOMContentLoaded', () => {
                updateMap("<?php echo htmlspecialchars($property['street_address'] . ', ' . $property['city']); ?>");
                
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
    const bookedDates = <?php echo $booked_dates_json; ?>;
    const dateRangeInput = document.getElementById("date-range");
    const startDateInput = document.getElementById("start_date");
    const endDateInput = document.getElementById("end_date");
    const totalPriceInput = document.getElementById("total_price");
    const pricePerNight = <?php echo htmlspecialchars($property['price']); ?>;

    flatpickr(dateRangeInput, {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: { firstDayOfWeek: 1 },
        disable: bookedDates.map(date => new Date(date)),
        onClose: function(selectedDates) {
            if (selectedDates.length === 2) {
                const startDate = selectedDates[0];
                const endDate = selectedDates[1];

                const timeDiff = endDate - startDate;
                const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                const totalPrice = nights * pricePerNight;

                document.getElementById("total-price").innerHTML = `<strong></strong> €${totalPrice}`;
                
                startDateInput.value = dateRangeInput._flatpickr.formatDate(startDate, "Y-m-d");
                endDateInput.value = dateRangeInput._flatpickr.formatDate(endDate, "Y-m-d");
                totalPriceInput.value = totalPrice;
            } else {
                document.getElementById("total-price").innerHTML = "";
            }
        }
    });
});

        </script>
        <script></script>
        <script>
    let currentSlide = 0;
    const slides = document.querySelectorAll(".image-gallery .slide");

    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.classList.remove("active");
            if (i === index) {
                slide.classList.add("active");
            }
        });
    }

    function prevSlide() {
        currentSlide = (currentSlide > 0) ? currentSlide - 1 : slides.length - 1;
        showSlide(currentSlide);
    }

    function nextSlide() {
        currentSlide = (currentSlide < slides.length - 1) ? currentSlide + 1 : 0;
        showSlide(currentSlide);
    }

    document.addEventListener("DOMContentLoaded", () => {
        showSlide(currentSlide);
    });
    
</script>

    </body>
    </html>
    <?php
} else {
    echo "<p>Property not found.</p>";
}

$stmt->close();
$conn_booking->close();
$conn_users->close();
?>
