<?php
session_start();
require_once("../public/dp.php");
require_once("../public/common_query.php");

/* VARIABLES */
$error_msg = '';
$success_msg = '';
$date = '';
$time = '';
$note = '';
$prefillService = null;
$nextAvailableTime = null;
$allAvailable = false;

/* LOGIN CHECK */
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}
$user_id = (int)$_SESSION['id'];

/* USER INFO */
$user = selectData("users", $mysqli, "id=$user_id", "name,phone")->fetch_assoc();

/* PREFILL SERVICE */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $prefillService = $mysqli->query("
        SELECT id FROM services WHERE id=" . (int)$_GET['id'] . " AND status=1
    ")->fetch_assoc();
}

/* CATEGORIES */
$categories = $mysqli->query("SELECT id,name FROM categories ORDER BY id");

/* TIME SLOTS */
$timeSlots = [];
$t = strtotime("09:00");
$end = strtotime("20:00");
while ($t < $end) {
    $timeSlots[] = date("H:i", $t);
    $t = strtotime("+30 minutes", $t);
}

/* FORM SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $services = $_POST['services'] ?? [];
    $date     = $_POST['date'] ?? '';
    $time     = $_POST['time'] ?? '';
    $note     = $_POST['note'] ?? '';
    $conflictFound = false;

    if (!$services || !$date || !$time) {
        $error_msg = "Please fill all required fields.";
    } else {

        $startTs = strtotime("$date $time");
        $openTs  = strtotime("$date 09:00");
        $closeTs = strtotime("$date 20:00");

        if ($startTs < $openTs || $startTs >= $closeTs) {
            $error_msg = "Booking time must be between 9:00 AM and 8:00 PM.";
        } else {

            $allAvailable = true;

            /* 1️⃣ Prevent same user booking same service on same day */
            foreach ($services as $sid) {
                $sid = (int)$sid;
                // check for same user booked same services in oneday
                $existing = $mysqli->query("
        SELECT COUNT(*) AS c
        FROM appointments a
        JOIN appointment_services aps ON aps.appointment_id = a.id
        WHERE a.user_id = $user_id
          AND aps.service_id = $sid
          AND aps.status IN ('pending','confirmed')
    ")->fetch_assoc()['c'];

                if ($existing > 0) {
                    $allAvailable = false;
                    $error_msg = "You already have this service booked. Cannot book again until previous booking is completed.";
                    break;
                }
            }


            /* 2️⃣ Calculate total duration */
            if ($allAvailable) {

                $serviceIds = implode(',', array_map('intval', $services));
                $durRes = $mysqli->query("
                    SELECT SUM(TIME_TO_SEC(duration)) AS total
                    FROM services WHERE id IN ($serviceIds)
                ")->fetch_assoc();

                $totalDuration = (int)$durRes['total'];
                $endTs = $startTs + $totalDuration;
                $endTime = date('H:i:s', $endTs);

                /* readable duration */
                $mins = round($totalDuration / 60);
                $durationText = $mins < 60
                    ? "$mins minutes"
                    : intdiv($mins, 60) . " hr" . ($mins % 60 ? " " . ($mins % 60) . " min" : "");

                /* 3️⃣ Close time check */
                if ($endTs > $closeTs) {
                    $allAvailable = false;
                    $error_msg = "Selected services take $durationText.
                    Our working hours are 09:00 AM to 08:00 PM. Please choose another time.";
                }
            }

            /*  CONFLICT CHECK (SAME SERVICE + TIME OVERLAP) this code is correct now cham*/
            if ($allAvailable) {

                $serviceIds = implode(',', array_map('intval', $services));

                $conflicts = $mysqli->query("
        SELECT
            a.appointment_time,
            SUM(TIME_TO_SEC(s.duration)) AS duration_sec
        FROM appointments a
        JOIN appointment_services aps ON aps.appointment_id = a.id
        JOIN services s ON s.id = aps.service_id
        WHERE a.appointment_date = '$date'
          AND aps.service_id IN ($serviceIds)
          AND aps.status IN ('pending','confirmed')
        GROUP BY a.id
        ORDER BY a.appointment_time ASC
    ");
                $conflictFound = false;
                $nearestConflictEndTs = null;

                if ($conflicts && $conflicts->num_rows > 0) {
                    while ($rowC = $conflicts->fetch_assoc()) {

                        $conflictStart = strtotime("$date {$rowC['appointment_time']}");
                        $conflictEnd   = $conflictStart + (int)$rowC['duration_sec'];

                        // 🔴 TRUE OVERLAP CHECK
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
        }
    }

    /* 5️⃣ INSERT */
    if ($allAvailable) {

        $note_safe = $mysqli->real_escape_string($note);

        $mysqli->query("
                    INSERT INTO appointments
                    (user_id, appointment_date, appointment_time, request, status)
                    VALUES
                    ($user_id, '$date', '$time', '$note_safe', 'pending')
                ");

        $appointment_id = $mysqli->insert_id;

        foreach ($services as $sid) {
            $sid = (int)$sid;
            $mysqli->query("
                        INSERT INTO appointment_services
                        (appointment_id, service_id, status)
                        VALUES ($appointment_id, $sid, 'pending')
                    ");
        }

        // Insert notification ( IN ADMIN)
        $notifMessage = "New appointment booked by " . htmlspecialchars($user['name']) . " on $date at $time.";

        $mysqli->query("
    INSERT INTO notifications 
    (user_id, appointment_id, message, is_read, show_in_nav)
    VALUES 
    (0, $appointment_id, '" . $mysqli->real_escape_string($notifMessage) . "', 0, 1)
");


        $success_msg = "Booking successful! Your appointment is on $date at $time.";
        $redirect_url = "confirmbooking.php";
    }
}
require_once("../include/header.php");
?>

<div class="container my-5">
    <div class="card p-4 shadow">
        <h4 class="text-center text-success mb-3">Book Your Visit ✨</h4>

        <form method="post">

            <input class="form-control mb-2" value="<?= htmlspecialchars($user['name']) ?>" readonly>
            <input class="form-control mb-2" value="<?= htmlspecialchars($user['phone']) ?>" readonly>

            <input type="date" name="date" class="form-control mb-2"
                min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date) ?>" required>

            <select name="time" class="form-control mb-3" required>
                <option value="">Choose time</option>
                <?php foreach ($timeSlots as $ts): ?>
                    <option value="<?= $ts ?>" <?= $time === $ts ? 'selected' : '' ?>>
                        <?= $ts ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php while ($cat = $categories->fetch_assoc()): ?>
                <b><?= htmlspecialchars($cat['name']) ?></b><br>
                <?php
                $srv = $mysqli->query("
                    SELECT id,name FROM services
                    WHERE category_id={$cat['id']} AND status=1
                ");
                while ($s = $srv->fetch_assoc()):
                ?>
                    <label>
                        <input type="checkbox" name="services[]" value="<?= $s['id'] ?>"
                            <?= ($prefillService && $prefillService['id'] == $s['id']) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </label><br>
                <?php endwhile; ?>
                <hr>
            <?php endwhile; ?>

            <textarea name="note" class="form-control mb-3"
                placeholder="Special request"><?= htmlspecialchars($note) ?></textarea>

            <button class="btn btn-success w-100">Confirm Booking</button>
        </form>
    </div>
</div>

<?php
require_once("../public/alert.php");
require_once("../include/footer.php");
?>