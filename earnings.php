<?php
session_start();
include('db_connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to view earnings.'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch total earnings by date range
$filter = $_GET['filter'] ?? 'monthly';
$dateCondition = "";
if ($filter === 'weekly') {
    $dateCondition = "AND payment_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
} elseif ($filter === 'monthly') {
    $dateCondition = "AND payment_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
} elseif ($filter === 'yearly') {
    $dateCondition = "AND payment_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
}

$earningsQuery = "SELECT SUM(amount) AS total_earnings FROM payments WHERE user_id = '$user_id' $dateCondition";
$earningsResult = $conn->query($earningsQuery);
$earnings = $earningsResult->fetch_assoc()['total_earnings'];
?>

<!DOCTYPE html>
<html>
<head><title>Earnings Report</title></head>
<body>
    <h2>Earnings Report</h2>
    <form method="GET">
        <label for="filter">Select Time Frame:</label>
        <select name="filter" onchange="this.form.submit()">
            <option value="weekly" <?php if ($filter === 'weekly') echo 'selected'; ?>>This Week</option>
            <option value="monthly" <?php if ($filter === 'monthly') echo 'selected'; ?>>This Month</option>
            <option value="yearly" <?php if ($filter === 'yearly') echo 'selected'; ?>>This Year</option>
        </select>
    </form>

    <p><strong>Total Earnings:</strong> ₹<?php echo number_format($earnings, 2); ?></p>
</body>
</html>
