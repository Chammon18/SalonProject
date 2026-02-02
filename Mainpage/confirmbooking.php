<?php
session_start();
require_once("../public/dp.php");
require_once("../include/header.php");

// LOGIN CHECK
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}

$user_id = (int)$_SESSION['id'];

// Get appointment_id from GET (or latest if none)
$appointment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$appointment_id) {
    // fallback to latest booking
    $res = $mysqli->query("
        SELECT id FROM appointments 
        WHERE user_id = $user_id 
        ORDER BY id DESC 
        LIMIT 1
    ");
    if ($res->num_rows === 0) {
        echo "<div class='container my-5'><div class='alert alert-warning'>No booking found.</div></div>";
        exit;
    }
    $appointment_id = $res->fetch_assoc()['id'];
}

// Fetch appointment
$appointment_res = $mysqli->query("
    SELECT *
    FROM appointments
    WHERE id = $appointment_id AND user_id = $user_id
");
if ($appointment_res->num_rows === 0) {
    echo "<div class='container my-5'><div class='alert alert-warning'>Booking not found.</div></div>";
    exit;
}
$appointment = $appointment_res->fetch_assoc();

// Fetch user info
$user_res = $mysqli->query("SELECT name, phone FROM users WHERE id = $user_id");
$user = $user_res->fetch_assoc();

// Fetch all services for this appointment
$services_res = $mysqli->query("
    SELECT s.name AS service_name, c.name AS category_name, s.description, s.price, s.duration
    FROM appointment_services aps
    JOIN services s ON aps.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE aps.appointment_id = $appointment_id
    ORDER BY c.name, s.name
");

// Group services by category
$services_by_category = [];
$service_details = [];
while ($row = $services_res->fetch_assoc()) {
    $services_by_category[$row['category_name']][] = $row['service_name'];
    $service_details[] = $row;
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg rounded-4 p-4 text-center">
                <div class="mb-3">
                    <span style="font-size:50px;">✅</span>
                </div>
                <h3 class="text-success mb-4">Booking Details</h3>

                <!-- Services -->
                <?php foreach ($services_by_category as $category => $services): ?>
                    <div class="text-start mb-2">
                        <strong><?= htmlspecialchars($category) ?>:</strong>
                        <?= implode(', ', array_map('htmlspecialchars', $services)) ?>
                    </div>
                <?php endforeach; ?>

                <!-- Optional: show each service details -->
                <?php foreach ($service_details as $srv): ?>
                    <div class="text-start mb-2 bg-light rounded p-2">
                        <strong><?= htmlspecialchars($srv['service_name']) ?></strong><br>
                        Price: Kyats <?= number_format($srv['price']) ?> <br> Duration: <?= htmlspecialchars($srv['duration']) ?>
                    </div>
                <?php endforeach; ?>

                <!-- Date & Time -->
                <div class="text-start mb-2">
                    <strong>Date & Time:</strong>
                    <?= date('d M Y', strtotime($appointment['appointment_date'])) ?>
                    , <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                </div>

                <!-- Status -->
                <div class="text-start mb-2">
                    <strong></strong>
                    <span class="badge bg-warning text-dark"><?= ucfirst($appointment['status']) ?></span>
                </div>

                <!-- Customer Info -->
                <div class="bg-light rounded p-3 text-start mb-2">
                    <strong>Customer:</strong> <?= htmlspecialchars($user['name']) ?><br>
                    <strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?>
                </div>

                <!-- Special Request -->
                <?php if (!empty($appointment['request'])): ?>
                    <div class="bg-warning bg-opacity-10 rounded p-3 mb-3 text-start">
                        <strong>Special Request:</strong><br>
                        <?= nl2br(htmlspecialchars($appointment['request'])) ?>
                    </div>
                <?php endif; ?>

                <a href="index.php" class="btn btn-success w-100 py-2 fw-semibold">Back to Home Page</a>
            </div>
        </div>
    </div>
</div>

<?php require_once("../include/footer.php"); ?>