<?php
session_start();
include('db_connection.php');

// Check for booking details
if (!isset($_SESSION['booking_details'])) {
    echo "<script>alert('Booking details not found.'); window.location.href='view-details.php';</script>";
    exit();
}

$booking = $_SESSION['booking_details'];
$total_price = $booking['total_price'];

// Assuming payment is successful
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insert the booking in `booking_req`
    $insertBookingQuery = "INSERT INTO booking_req (user_id, vehicle_id, start_date, end_date, start_time, end_time, days_booked, hours_booked, total_price, status)
                           VALUES ('{$booking['user_id']}', '{$booking['vehicle_id']}', '{$booking['start_date']}', '{$booking['end_date']}', '{$booking['start_time']}', '{$booking['end_time']}', '{$booking['days_booked']}', '{$booking['hours_booked']}', '$total_price', 'Confirmed')";
    $conn->query($insertBookingQuery);
    $booking_id = $conn->insert_id;

    // Insert payment record
    $insertPaymentQuery = "INSERT INTO payments (booking_id, user_id, vehicle_id, amount, payment_date, payment_method, payment_status) 
                           VALUES ('$booking_id', '{$booking['user_id']}', '{$booking['vehicle_id']}', '$total_price', NOW(), 'Online', 'Completed')";
    $conn->query($insertPaymentQuery);

    // Redirect to booking invoice
    header("Location: booking-invoice.php?booking_id=$booking_id");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Payment</title></head>
<body>
    <h2>Complete Your Payment</h2>
    <p>Total Amount: ₹<?php echo number_format($total_price, 2); ?></p>
    <form method="POST">
        <button type="submit">Pay Now</button>
    </form>
</body>
</html>
