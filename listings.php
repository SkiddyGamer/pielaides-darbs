<?php

$host = 'localhost'; 
$username = 'root'; 
$password = ''; 
$database = 'submitlisting'; 

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




$booked_properties = [];
$booking_sql = "SELECT property_id, start_date, end_date FROM bookings";
$booking_result = $conn->query($booking_sql);

if ($booking_result->num_rows > 0) {
    while ($booking_row = $booking_result->fetch_assoc()) {
        $property_id = $booking_row['property_id'];
        $start_date = $booking_row['start_date'];
        $end_date = $booking_row['end_date'];
        
        $booked_properties[] = [
            'property_id' => $property_id,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
    }
}


$booked_properties_json = json_encode($booked_properties);


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Properties - NaturesCottage</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        
        .property-listings {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        
        .property-card {
            background: #fff;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
            text-align: left;
            width: 270px;
            height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease-in-out;
            overflow: hidden;
            cursor: pointer;
        }

        .property-card:hover {
            transform: scale(1.02);
        }

        
        .property-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }


        .property-details {
            padding: 8px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .property-details h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        
        .property-details p {
            font-size: 13px;
            color: #555;
            margin: 5px 0;
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
        }

        .property-details .price {
            font-size: 14px;
            font-weight: bold;
            color: #222;
            margin-top: auto;
        }

        
        .info-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #777;
            margin-top: 2px;
        }

        .info-bar span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-bar i {
            font-size: 13px;
            color: #444;
        }

        
        @media (max-width: 1024px) {
            .property-listings {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .property-listings {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .property-listings {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> 
</head>
<body>

    <?php include 'Header.php'; ?>
    <?php include 'login_form.php'; ?>

    <main>
        <h1>Property Listings</h1>
        <p>Browse through our available vacation rentals.</p>
        <input type="text" id="searchBar" placeholder="Search" onkeyup="filterProperties()" style="width: 100%; padding: 10px; margin-bottom: 20px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px;">
        
        <input type="text" id="datePicker" placeholder="Select Dates" 
               style="width: 100%; padding: 10px; margin-bottom: 20px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px;">
        
               <select id="guestFilter" onchange="filterProperties()" style="width: 100%; padding: 10px; margin-bottom: 20px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px;">
    <option value="">Filter by Guests</option>
    <option value="1">1 Guest</option>
    <option value="2">2 Guests</option>
    <option value="3">3 Guests</option>
    <option value="4">4 Guests</option>
    <option value="5">5 Guests</option>
    <option value="6">6 Guests</option>
    <option value="7">7 Guests</option>
    <option value="8">8 Guests</option>
    <option value="9">9 Guests</option>
    <option value="10">10+ Guests</option>
</select>
        
        <?php
        
        $conn = new mysqli($host, $username, $password, $database);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $sql = "SELECT * FROM properties ORDER BY id DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<div class="property-listings">';
            while ($row = $result->fetch_assoc()) {
                $property_id = $row['id']; 
                $property_type = htmlspecialchars($row['property_type']);
                $bedrooms = (int)$row['bedrooms'];
                $bathrooms = (float)$row['bathrooms'];
                $guests = (int)$row['guests'];

                
                $propertyIcons = [
                    'house' => 'fa-home',
                    'villa' => 'fa-landmark',
                    'cabin' => 'fa-tree',
                    'apartment' => 'fa-building'
                ];
                $propertyIcon = isset($propertyIcons[$property_type]) ? $propertyIcons[$property_type] : 'fa-home';

                echo '<a href="property_details.php?id=' . $property_id . '" class="property-card" data-property-id="' . $property_id . '">';  
                $images = explode(',', $row['images']);
                if (!empty($images[0])) {
                    echo '<img src="' . htmlspecialchars($images[0]) . '" alt="Property Image">';
                }
                echo '<div class="property-details">';
                echo '<h2>' . htmlspecialchars($row['property_name']) . '</h2>';
                $maxLength = 70; 
                $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                $shortDescription = mb_substr($description, 0, $maxLength, 'UTF-8');

                if (mb_strlen($description, 'UTF-8') > $maxLength) {
                    $shortDescription .= '...'; 
                }

                echo '<p>' . $shortDescription . '</p>';
                echo '<p class="price"><strong>€' . htmlspecialchars($row['price']) . '</strong> / night</p>';

                echo '<div class="info-bar">';
                echo '<span><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($row['city']) . '</span>';
                echo '<span><i class="fas fa-bed"></i> ' . $bedrooms . '</span>';
                echo '<span><i class="fas fa-bath"></i> ' . $bathrooms . '</span>';
                echo '<span><i class="fas fa-user-group"></i> ' . $guests . '</span>';  
                echo '<span><i class="fas ' . $propertyIcon . '"></i> ' . ucfirst($property_type) . '</span>';
                echo '</div>';

                echo '</div>';
                echo '</a>';
            }
            echo '</div>';
        } else {
            echo "<p>No listings available.</p>";
        }

        $conn->close();
        ?>

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
    
    const bookedProperties = <?php echo $booked_properties_json; ?>;

    
    const datePicker = flatpickr("#datePicker", {
        mode: "range",
        dateFormat: "Y-m-d",
        minDate: "today",
        locale: { firstDayOfWeek: 1 },
        position: "auto center",
        onChange: function(selectedDates) {
            filterProperties();
        }
    });

    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    
    function filterProperties() {
    const input = document.getElementById('searchBar').value.toLowerCase();
    const guestFilter = document.getElementById('guestFilter').value;
    const propertyCards = document.querySelectorAll('.property-card');

    
    const selectedDates = datePicker.selectedDates;
    let formattedStartDate = null;
    let formattedEndDate = null;

    if (selectedDates.length === 2) {
        formattedStartDate = formatDate(selectedDates[0]);
        formattedEndDate = formatDate(selectedDates[1]);
    }

    const queryParams = new URLSearchParams();
    if (input) queryParams.set('location', input);
    if (formattedStartDate && formattedEndDate) queryParams.set('dates', `${formattedStartDate} to ${formattedEndDate}`);
    if (guestFilter) queryParams.set('guests', guestFilter);

    
    window.history.replaceState({}, '', `${window.location.pathname}?${queryParams.toString()}`);

    propertyCards.forEach(card => {
        const title = card.querySelector('h2').innerText.toLowerCase();
        const description = card.querySelector('p').innerText.toLowerCase();
        const location = card.querySelector('.info-bar span:first-child').innerText.toLowerCase();
        const guests = parseInt(card.querySelector('.info-bar span:nth-child(4)').innerText.trim());
        const propertyId = card.getAttribute('data-property-id');

        const matchesSearch = title.includes(input) || description.includes(input) || location.includes(input);

        
         
         let matchesGuestFilter = true;
        if (guestFilter !== "") {
            const selectedGuests = parseInt(guestFilter);
            if (guestFilter === "10") {
                matchesGuestFilter = guests >= 10; 
            } else {
                matchesGuestFilter = guests >= selectedGuests; 
            }
        }

        
        let isBooked = false;
        if (formattedStartDate && formattedEndDate) {
            isBooked = bookedProperties.some(booking => {
                const bookingStart = formatDate(new Date(booking.start_date));
                const bookingEnd = formatDate(new Date(booking.end_date));

                
                return booking.property_id == propertyId &&
                    !(formattedEndDate < bookingStart || formattedStartDate > bookingEnd);
            });
        }

        
        if (matchesSearch && matchesGuestFilter && !isBooked) {
            card.style.display = ""; 
        } else {
            card.style.display = "none"; 
        }
    });
}
    
    function prefillInputs() {
    const urlParams = new URLSearchParams(window.location.search);
    const location = urlParams.get('location');
    const dates = urlParams.get('dates');
    const guests = urlParams.get('guests');

    if (location) {
        document.getElementById('searchBar').value = location;
    }

    if (dates) {
        const [startDate, endDate] = dates.split(' to ');
        datePicker.setDate([startDate, endDate]);
    }

    if (guests) {
        const guestNumber = parseInt(guests);
        if (guestNumber >= 10) {
            document.getElementById('guestFilter').value = "10";
        } else {
            document.getElementById('guestFilter').value = guestNumber.toString();
        }
    }

    filterProperties();
}

    
    document.getElementById('searchBar').addEventListener('keyup', filterProperties);
    document.getElementById('guestFilter').addEventListener('change', filterProperties);
    document.getElementById('datePicker').addEventListener('change', filterProperties);

    
    window.onload = function() {
        prefillInputs();
    };
</script>
    </script>

</body>
</html>