<?php
session_start();

// Redirect to login page if the admin is not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: adminlogin.php");
    exit();
}

// CSRF Protection: Generate a token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection parameters
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "car_rental_db";

// // Create a new MySQLi connection
// $conn = new mysqli($servername, $username, $password, $dbname);

// // Check for connection errors
// if ($conn->connect_error) {
//     die("Connection failed: " . htmlspecialchars($conn->connect_error));
// }
require('db_connection.php');

// Handle Delete Booking via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_booking'])) {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Get the booking ID from POST data
    $delete_booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

    if ($delete_booking_id > 0) {
        // Start a transaction
        $conn->begin_transaction();

        try {
            // Fetch the vehicle_registration_id associated with the booking
            $fetchVehicleQuery = "SELECT vehicle_registration_id FROM bookings WHERE booking_id = ?";
            if ($stmt_fetch = $conn->prepare($fetchVehicleQuery)) {
                $stmt_fetch->bind_param("i", $delete_booking_id);
                $stmt_fetch->execute();
                $result_fetch = $stmt_fetch->get_result();
                if ($vehicle = $result_fetch->fetch_assoc()) {
                    $vehicle_registration_id = $vehicle['vehicle_registration_id'];
                } else {
                    throw new Exception("Booking not found.");
                }
                $stmt_fetch->close();
            } else {
                throw new Exception("Error preparing fetch vehicle statement.");
            }

            // Delete the booking
            $deleteQuery = "DELETE FROM bookings WHERE booking_id = ?";
            if ($stmt_delete = $conn->prepare($deleteQuery)) {
                $stmt_delete->bind_param("i", $delete_booking_id);
                if (!$stmt_delete->execute()) {
                    throw new Exception("Error deleting booking: " . $stmt_delete->error);
                }
                $stmt_delete->close();
            } else {
                throw new Exception("Error preparing delete statement.");
            }

            // Update vehicle status to available (1)
            $updateVehicleQuery = "UPDATE vehicles SET status = 1 WHERE Registration_id = ?";
            if ($stmt_update = $conn->prepare($updateVehicleQuery)) {
                $stmt_update->bind_param("s", $vehicle_registration_id);
                if (!$stmt_update->execute()) {
                    throw new Exception("Error updating vehicle status: " . $stmt_update->error);
                }
                $stmt_update->close();
            } else {
                throw new Exception("Error preparing vehicle update statement.");
            }

            // Commit the transaction
            $conn->commit();

            echo "<script>alert('Booking deleted successfully and vehicle status updated to available.');</script>";
            echo "<script>window.location.href='manage_bookings.php';</script>";
            exit();
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();
            echo "<script>alert('" . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid booking ID for deletion.');</script>";
    }
}

// Fetch all bookings
$bookingsQuery = "SELECT id, user_id, vehicle_id, start_date, end_date, hours_booked, days_booked, total_price, payment_way, status  FROM booking_req";
$result = $conn->query($bookingsQuery);

if (!$result) {
    die("Error fetching bookings: " . htmlspecialchars($conn->error));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System | Manage Bookings</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="manage_bookings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Additional styling for buttons and table */
        .booking-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .booking-table th, .booking-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .booking-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<!-- <div class="sidebar">
        <h1>Car Rental System | Admin Panel</h1>
        <ul>
            <li><a href="admindashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="manage-brands.php"><i class="fas fa-car"></i> Brands</a></li>
            <li><a href="manage-vehicles.php" ><i class="fas fa-car"></i> Vehicles</a></li>
            <li><a href="manage_bookings.php" class="active"><i class="fas fa-book"></i> Bookings </a></li>
            <li><a href="manage_contact_queries.php"><i class="fas fa-envelope"></i> Manage Contact Queries</a></li>
            <li><a href="registered_users.php"><i class="fas fa-users"></i> Registered Users</a></li>
            <li><a href="manage_chauffeurs.php"><i class="fas fa-user-plus"></i> Manage Chauffeurs</a></li>
        </ul>
    </div> -->
     <!-- Navbar and Sidebar -->
   <?php require('navbar.php')?>
    <!-- <div class="content">
        <div class="top-bar">
            <h2>Manage Bookings</h2>
            <div class="user-info">
                <img src="admindp.png" alt="User Avatar">
                <span>Admin</span>
                <a href="adminlogin.php" class="logout-btn">Log Out</a>
            </div>
        </div> -->

        <div class="booking-table">
        <h2>Manage Bookings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer ID</th>
                        <th>Vehicle Registration ID</th>
                        <th>Pickup Date</th>
                        <th>Return Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Sanitize output
                            $booking_id = htmlspecialchars($row['id']);
                            $customer_id = htmlspecialchars($row['user_id']);
                            $vehicle_registration_id = htmlspecialchars($row['vehicle_id']);
                            // $booking_date = htmlspecialchars($row['booking_date']);
                            $pickup_date = htmlspecialchars($row['start_date']);
                            $return_date = htmlspecialchars($row['end_date']);
                            $total_amount = htmlspecialchars($row['total_price']);
                            // $chauffeurs_id = htmlspecialchars($row['chauffeurs_id']) ?: '--';
                            $booking_status = htmlspecialchars($row['status']);
                            $payment_way = htmlspecialchars($row['payment_way']);

                            echo "<tr id='row-{$booking_id}'>
                                    <td>{$booking_id}</td>
                                    <td>{$customer_id}</td>
                                    <td>{$vehicle_registration_id}</td>
                                    <td>{$pickup_date}</td>
                                    <td>{$return_date}</td>
                                    <td>{$total_amount}</td>
                                    <td>{$booking_status}</td>
                                    <td>{$payment_way}</td>
                                    <td>
                                        <a href='edit_bookings.php?booking_id={$booking_id}' class='edit-btn' title='Edit Booking'><i class='fas fa-edit'></i></a>
                                        <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this booking?\");'>
                                            <input type='hidden' name='booking_id' value='{$booking_id}'>
                                            <input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>
                                            <button type='submit' name='delete_booking' class='delete-btn' title='Delete Booking'><i class='fas fa-trash-alt'></i></button>
                                        </form>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11'>No bookings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
