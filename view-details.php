<?php
session_start(); // Start the session to check if the user is logged in

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

include('db_connection.php');

// Initialize variables
$photos = []; // Initialize the photos array
$brand = '';
$vehicleName = '';
$price = 0;
$hprice = 0;
$fuelType = '';
$seatingCapacity = '';
$engine = '';
$transmission = '';
$power = '';
$torque = '';
$fuelTankCapacity = '';
$mileage = '';
$year = '';

// Fetch car details from the database
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT v.*, b.brand_name FROM vehicles v JOIN brands b ON v.brand_id = b.id WHERE v.id = '$id'";
    $result = $conn->query($query);
    $car = $result->fetch_assoc();

    // Extract car data (make sure columns match your DB)
    $vehicleName = $car['vehicle_name'];
    $brand = $car['brand_name'];
    $price = $car['price_per_day'];
    $hprice = $car['rent_per_hour'];
    $fuelType = $car['fuel_type'];
    $seatingCapacity = $car['seating_capacity'];
    $engine = $car['engine'];
    $transmission = $car['transmission'];
    $power = $car['power'];
    $torque = $car['torque'];
    $fuelTankCapacity = $car['fuel_tank_capacity'];
    $mileage = $car['mileage'];
    $year = $car['model_year'];
    $photos = array($car['photos'], $car['photos1'], $car['photos2'], $car['photos3']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Details</title>
    <!-- Include Bootstrap CSS for Carousel -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="home.css"> <!-- For header and footer -->
    <link rel="stylesheet" href="car-listing.css"> <!-- For car listing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <style>
         body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        } 
        .details {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center child elements */
            width: 100%; /* Full width */
        }

        .car-info {
            width: 100%;
            max-width: 1200px; /* Limit maximum width */
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            margin-left: auto; /* Center align */
            margin-right: auto; /* Center align */
        }


        .car-info-left {
            width: 65%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .car-info-right {
            width: 30%;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .price {
            font-size: 20px;
            color: #ff6600;
        }

        .book-now-form {
            margin-top: 20px;
        }

        .book-now-form input, .book-now-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .book-now-form button {
            width: 100%;
            padding: 15px;
            background-color: #0ef;
            border: none;
            color: white;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .book-now-form button:hover {
            background-color: #008fbb;
        }
        .car-specs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .car-specs div {
            text-align: center;
            padding: 10px;
            background-color: #f4f4f4;
            border-radius: 5px;
            width: 24%;
        }

        .car-specs i {
            font-size: 24px;
            color: #0ef;
        }

        .car-specs div span {
            display: block;
            margin-top: 5px;
        }

        .description, .accessories {
            margin-top: 20px;
            background-color: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
        }

        .description h3, .accessories h3 {
            font-size: 22px;
        }
/* 
        .carousel-inner img {
            width: 800;
            height: 560;
        } 
        .carousel-inner .row {
            display: flex;
        }
        .carousel-inner .col {
            padding: 0;
        } */


        .carousel-inner .row {
            display: flex;
        }

        .carousel-inner .col-md-3 {
            padding: 0 5px;
        }

        .carousel-inner img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: orange; /* Change the control color as per your preference */
        }

    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <?php include('header.php')?>

<div class="details">
    <div id="carPhotosCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
        <div class="carousel-inner">
            <!-- Carousel items -->
            <div class="carousel-item active">
                <div class="row">
                    <?php if (!empty($photos)) : ?>
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div class="col-md-4">
                                <img class="d-block w-100" src="images/vehicles/<?php echo htmlspecialchars($photos[$i]); ?>" alt="Car Image <?php echo $i + 1; ?>" style="height:300px;">
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $totalPhotos = count($photos);
            for ($i = 3; $i < $totalPhotos; $i += 1): ?>
                <div class="carousel-item">
                    <div class="row">
                        <?php for ($j = 0; $j < 3; $j++): ?>
                            <div class="col-md-4">
                                <?php
                                $photoIndex = ($i + $j) % $totalPhotos; // Loop through the images
                                ?>
                                <img class="d-block w-100" src="images/vehicles/<?php echo htmlspecialchars($photos[$photoIndex]); ?>" alt="Car Image <?php echo $photoIndex + 1; ?>" style="height:300px;">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <!-- Carousel controls -->
        <a class="carousel-control-prev" href="#carPhotosCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carPhotosCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>



        <div class="car-info">
            <div class="car-info-left">
                <h1><?php echo htmlspecialchars($brand) . ', ' . htmlspecialchars($vehicleName); ?></h1>
                
                <div class="car-specs">
                    <div>
                        <i class="fas fa-calendar-alt"></i>
                        <span><?php echo htmlspecialchars($year); ?></span>
                    </div>
                    <div>
                        <i class="fas fa-gas-pump"></i>
                        <span><?php echo htmlspecialchars($fuelType); ?></span>
                    </div>
                    <div>
                        <i class="fas fa-users"></i>
                        <span><?php echo htmlspecialchars($seatingCapacity); ?> Seater</span>
                    </div>
                </div>

                <div class="description">
                    <h3>Vehicle Details</h3>
                    <p><strong>Engine:</strong> <?php echo htmlspecialchars($engine); ?></p>
                    <p><strong>Transmission:</strong> <?php echo htmlspecialchars($transmission); ?></p>
                    <p><strong>Power:</strong> <?php echo htmlspecialchars($power); ?> bhp</p>
                    <p><strong>Torque:</strong> <?php echo htmlspecialchars($torque); ?> Nm</p>
                    <p><strong>Fuel Tank Capacity:</strong> <?php echo htmlspecialchars($fuelTankCapacity); ?> L</p>
                    <p><strong>Mileage:</strong> <?php echo htmlspecialchars($mileage); ?> Kmpl</p>
                </div>
            </div>

            <div class="car-info-right">
                <h2>Price</h2>
                <div class="price">₹<?php echo htmlspecialchars($price); ?> / Day   ||   ₹<?php echo htmlspecialchars($hprice); ?> / Hour</div>
                
                     <!-- Buttons to toggle between Day and Hour booking -->
                  <div class="toggle-buttons">
                     <button class="btn btn-primary" id="day-btn" type="button">Book by Day</button>
                     <button class="btn btn-primary" id="hour-btn" type="button">Book by Hour</button>
                  </div>

                  <!-- Form for Day Selection (shown by default) -->
                  <form action="book-now.php?id=<?php echo $id; ?>" method="POST" class="book-now-form" id="day-form">
                     <input type="date" name="start_date" id="day-start-date" placeholder="Start Date" required>
                     <input type="date" name="end_date" id="day-end-date" placeholder="End Date" required>
                     <input type="hidden" name="booking_type" value="day">
                     <input type="hidden" name="vehicle_id" value="<?php echo $id; ?>">
                     <textarea placeholder="Message"></textarea>
                     <button type="submit">Book Now</button>
                  </form>

                  <!-- Form for Hour Selection (hidden by default) -->
                  <form action="book-now.php?id=<?php echo $id; ?>" method="POST" class="book-now-form" id="hour-form" style="display: none;">
                     <input type="date" name="date" id="hour-date" placeholder="Select Date" required>
                     <input type="time" name="start_time" id="hour-start-time" placeholder="Start Time" required>
                     <input type="time" name="end_time" id="hour-end-time" placeholder="End Time" required>
                     <input type="hidden" name="booking_type" value="hour">
                     <input type="hidden" name="vehicle_id" value="<?php echo $id; ?>">
                     <textarea placeholder="Message"></textarea>
                     <button  type="submit">Book Now</button>
                  </form>

                  <script>
                     // Get today's date in the format yyyy-MM-dd
                     function getTodayDate() {
                        const today = new Date();
                        const year = today.getFullYear();
                        const month = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-based, so add 1
                        const day = String(today.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                     }

                     // Set the min date for the Day form to today
                     const today = getTodayDate();
                     document.getElementById('day-start-date').min = today;
                     document.getElementById('day-end-date').min = today;

                     // Event listeners for toggling between Day and Hour forms
                     document.getElementById('day-btn').addEventListener('click', function() {
                        document.getElementById('day-form').style.display = 'block';
                        document.getElementById('hour-form').style.display = 'none';
                     });

                     document.getElementById('hour-btn').addEventListener('click', function() {
                        const hourDateInput = document.getElementById('hour-date');
                        hourDateInput.min = today; // No past date allowed, start from today
                        hourDateInput.value = ''; // Reset if previously filled

                        document.getElementById('day-form').style.display = 'none';
                        document.getElementById('hour-form').style.display = 'block';
                     });

                     // Disable end time selection if it's earlier than start time
                     document.getElementById('hour-start-time').addEventListener('change', function() {
                        const startTime = this.value;
                        const endTimeInput = document.getElementById('hour-end-time');
                        endTimeInput.min = startTime; // Set the end time min to start time
                     });

                     // Validate end time on change
                    //  document.getElementById('hour-end-time').addEventListener('change', function(event) {
                    //     const startTime = document.getElementById('hour-start-time').value;
                    //     const endTime = this.value;

                    //     // Check if end time is earlier than or not equal to start time
                    //     if (endTime < startTime) {
                    //        alert("End Time cannot be earlier than Start Time.");
                    //        this.value = ''; // Reset the end time
                    //        event.preventDefault(); // Prevent form submission
                    //     } else if (startTime && endTime && startTime !== endTime) {
                    //        alert("For hourly rentals, start and end times must be the same.");
                    //        this.value = startTime; // Reset to start time
                    //        event.preventDefault(); // Prevent form submission
                    //     }
                    //  });
                     
                    document.getElementById('hour-end-time').addEventListener('change', function(event) {
                    const startTime = document.getElementById('hour-start-time').value;
                    const endTime = this.value;

                    // Check if end time is earlier than start time
                    if (endTime < startTime) {
                        alert("End Time cannot be earlier than Start Time.");
                        this.value = ''; // Reset the end time
                        event.preventDefault(); // Prevent form submission
                    }
                });


                     // Disable end date selection if it's earlier than start date for day booking
                     document.getElementById('day-start-date').addEventListener('change', function() {
                        document.getElementById('day-end-date').min = this.value;
                     });
                  </script>
            </div>
            
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
