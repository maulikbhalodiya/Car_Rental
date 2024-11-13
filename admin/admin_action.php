<?php
session_start();
require 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Define function to send mail notification
function sendStatusMail($email, $name, $status) {
    $subject = "Car Rental Request " . ucfirst($status);
    $message = "Dear $name,\n\nYour car rental request has been $status.\n\nRegards,\nCar Rental Team";
    $headers = "From: carrental@example.com";
    mail($email, $subject, $message, $headers);
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['request_ids'])) {
    $action = $_POST['bulk_action'];
    $requestIds = $_POST['request_ids'];

    foreach ($requestIds as $id) {
        $id = intval($id);
        $query = "SELECT * FROM car_rent_requests WHERE id = $id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $email = $row['email'];
            $name = $row['name'];

            // Update status and send email
            if ($action === 'approve') {
                $updateQuery = "UPDATE car_rent_requests SET status = 'Approved' WHERE id = $id";
                mysqli_query($conn, $updateQuery);
                sendStatusMail($email, $name, 'approved');
            } elseif ($action === 'reject') {
                $updateQuery = "UPDATE car_rent_requests SET status = 'Rejected' WHERE id = $id";
                mysqli_query($conn, $updateQuery);
                sendStatusMail($email, $name, 'rejected');
            }
        }
    }

    header("Location: admin_view_requests.php?view=pending&message=Bulk action completed successfully.");
    exit();
}

// Handle individual approve/reject actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    $query = "SELECT * FROM car_rent_requests WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $email = $row['email'];
        $name = $row['name'];

        if ($action === 'approve') {
            $updateQuery = "UPDATE car_rent_requests SET status = 'Approved' WHERE id = $id";
            mysqli_query($conn, $updateQuery);
            sendStatusMail($email, $name, 'approved');
        } elseif ($action === 'reject') {
            $updateQuery = "UPDATE car_rent_requests SET status = 'Rejected' WHERE id = $id";
            mysqli_query($conn, $updateQuery);
            sendStatusMail($email, $name, 'rejected');
        }
    }

    header("Location: admin_view_requests.php?view=pending&message=Action completed successfully.");
    exit();
}

header("Location: admin_view_requests.php?view=pending&message=No action performed.");
exit();
?>
