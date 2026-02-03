<?php
session_start();
require_once("../public/dp.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['id'];
/* USER INFO - fetch for notification */
$user = $mysqli->query("SELECT name, phone FROM users WHERE id=$user_id")->fetch_assoc();

/* Get service_id from query */
if (!isset($_GET['service_id']) || !is_numeric($_GET['service_id'])) {
    header("Location:history.php");
    exit;
}

$service_id = (int)$_GET['service_id'];

/* Fetch service info */
$service = $mysqli->query("SELECT * FROM services WHERE id=$service_id AND status=1")->fetch_assoc();
if (!$service) {
    echo "Service not found.";
    exit;
}

/* Prepare variables */
$error_msg = '';
$success_msg = '';
$date = '';
$time = '';
$note = '';
$nextAvailableTime = null;
$allAvailable = false;

/* Time slots */
$timeSlots = [];
$t = strtotime("09:00");
$end = strtotime("20:00");
while ($t < $end) {
    $timeSlots[] = date("H:i", $t);
    $t = strtotime("+30 minutes", $t);
}

/* FORM SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $note = $_POST['note'] ?? '';
    $allAvailable = false;

    if (!$date || !$time) {
        $error_msg = "Please fill all required fields.";
    } else {

        $startTs = strtotime("$date $time");
        $openTs  = strtotime("$date 09:00");
        $closeTs = strtotime("$date 20:00");

        if ($startTs < $openTs || $startTs >= $closeTs) {
            $error_msg = "Booking time must be between 9:00 AM and 8:00 PM.";
        } else {

            $allAvailable = true;

            /* Prevent same user booking same service */
            $existing = $mysqli->query("
                SELECT COUNT(*) AS c
                FROM appointments a
                JOIN appointment_services aps ON aps.appointment_id = a.id
                WHERE a.user_id = $user_id
                AND aps.service_id = $service_id
                AND aps.status IN ('pending','confirmed')
            ")->fetch_assoc()['c'];

            if ($existing > 0) {
                $allAvailable = false;
                $error_msg = "You already have this service booked.";
            }

            /* Calculate total duration */
            if ($allAvailable) {
                $totalDuration = (int)strtotime($service['duration']) - strtotime('TODAY');
                $endTs = $startTs + $totalDuration;

                if ($endTs > $closeTs) {
                    $allAvailable = false;
                    $error_msg = "Service duration exceeds shop closing time. Please choose another time.";
                }
            }

            /* Conflict check: same service + overlapping time */
            if ($allAvailable) {

                $conflicts = $mysqli->query("
                    SELECT a.appointment_time, TIME_TO_SEC(s.duration) AS duration_sec
                    FROM appointments a
                    JOIN appointment_services aps ON aps.appointment_id = a.id
                    JOIN services s ON s.id = aps.service_id
                    WHERE a.appointment_date='$date'
                    AND aps.service_id=$service_id
                    AND aps.status IN ('pending','confirmed')
                ");

                $conflictFound = false;
                $nearestConflictEndTs = null;

                if ($conflicts && $conflicts->num_rows > 0) {
                    while ($rowC = $conflicts->fetch_assoc()) {
                        $conflictStart = strtotime("$date {$rowC['appointment_time']}");
                        $conflictEnd   = $conflictStart + (int)$rowC['duration_sec'];

                        if ($startTs < $conflictEnd && $endTs > $conflictStart) {
                            $conflictFound = true;
                            if ($nearestConflictEndTs === null || $conflictEnd > $nearestConflictEndTs) {
                                $nearestConflictEndTs = $conflictEnd;
                            }
                        }
                    }
                }

                if ($conflictFound) {
                    $allAvailable = false;
                    $nextAvailableTime = date("H:i", $nearestConflictEndTs);
                    $error_msg = "Selected time is FULL. Next available time: $nextAvailableTime";
                }
            }

            /* Insert booking */
            if ($allAvailable) {

                $note_safe = $mysqli->real_escape_string($note);

                /* Insert appointment */
                $mysqli->query("
        INSERT INTO appointments
        (user_id, appointment_date, appointment_time, request, status)
        VALUES
        ($user_id, '$date', '$time', '$note_safe', 'pending')
    ");

                $appointment_id = $mysqli->insert_id;

                /* Link service */
                $mysqli->query("
        INSERT INTO appointment_services
        (appointment_id, service_id, status)
        VALUES ($appointment_id, $service_id, 'pending')
    ");

                /* Insert notification AFTER appointment_id exists */
                $notifMessage = "New appointment booked by " . htmlspecialchars($user['name']) . " on $date at $time.";

                $mysqli->query("
        INSERT INTO notifications 
        (user_id, appointment_id, message, is_read, show_in_nav)
        VALUES 
        (0, $appointment_id, '" . $mysqli->real_escape_string($notifMessage) . "', 0, 1)
    ");

                $success_msg = "Your service has been successfully rebooked on $date at $time!";
            }
        }
    }
}

require_once("../include/header.php");
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 p-4">
                <h4 class="text-center mb-4 fw-bold text-primary">Re-Book: <?= htmlspecialchars($service['name']) ?></h4>

                <form method="post" class="row g-3">

                    <div class="col-12">
                        <label class="form-label fw-semibold">Select Date</label>
                        <input type="date" name="date" class="form-control form-control-lg"
                            min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Select Time</label>
                        <select name="time" class="form-control form-control-lg" required>
                            <option value="">Choose time</option>
                            <?php foreach ($timeSlots as $ts): ?>
                                <option value="<?= $ts ?>" <?= $time === $ts ? 'selected' : '' ?>>
                                    <?= $ts ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Special Request</label>
                        <textarea name="note" class="form-control" placeholder="Any special request"><?= htmlspecialchars($note) ?></textarea>
                    </div>

                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5">Confirm Re-Book</button>
                        <a href="history.php" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>

                </form>


            </div>
        </div>
    </div>
</div>

<?php require_once('../public/alert.php');
