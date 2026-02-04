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
$warning_msg = '';
$success_msg = '';
$date = '';
$time = '';
$note = '';
$nextAvailableTime = null;
$allAvailable = false;

// Cancellation policy (last 7 days)
$warnThreshold = 4;
$blockThreshold = 5;
$cancelCount = 0;
$isBlocked = false;
$cancelRes = $mysqli->query("
    SELECT COUNT(*) AS c
    FROM appointments
    WHERE user_id = $user_id
      AND status IN ('cancelled','canceled')
      AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
")->fetch_assoc();
$cancelCount = (int)($cancelRes['c'] ?? 0);
if ($cancelCount >= $blockThreshold) {
    $isBlocked = true;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $error_msg = "Your bookings are temporarily blocked due to repeated cancellations (5+ in the last 7 days). Please contact the salon.";
    }
} elseif ($cancelCount >= $warnThreshold) {
    $warning_msg = "You have cancelled $cancelCount times in the last 7 days. One more cancellation will block bookings for a week.";
}

/* Time slots */
$timeSlots = [];
$t = strtotime("09:00");
$end = strtotime("20:00");
while ($t < $end) {
    $timeSlots[] = date("H:i", $t);
    $t = strtotime("+30 minutes", $t);
}

$durationToMinutes = function ($durationRaw) {
    $durationMinutes = 0;
    if (is_numeric($durationRaw)) {
        $durationMinutes = (int)$durationRaw;
        if ($durationMinutes > 1000) {
            $durationMinutes = (int)round($durationMinutes / 60);
        }
    } elseif (strpos($durationRaw, ':') !== false) {
        $parts = explode(':', $durationRaw);
        $h = isset($parts[0]) ? (int)$parts[0] : 0;
        $m = isset($parts[1]) ? (int)$parts[1] : 0;
        $s = isset($parts[2]) ? (int)$parts[2] : 0;
        $durationMinutes = ($h * 60) + $m + (int)floor($s / 60);
    }
    return $durationMinutes;
};

/* FORM SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isBlocked) {
        $error_msg = "Your bookings are temporarily blocked due to repeated cancellations (5+ in the last 7 days). Please contact the salon.";
    } else {

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
                WHERE a.user_id = $user_id
                AND a.service_id = $service_id
                AND a.status IN ('pending','approved')
            ")->fetch_assoc()['c'];

            if ($existing > 0) {
                $allAvailable = false;
                $error_msg = "You already have this service booked.";
            }

            /* Calculate total duration */
            if ($allAvailable) {
                $durationMin = $durationToMinutes($service['duration']);
                $totalDurationSec = $durationMin * 60;
                $breakMinutes = 15;
                $endTs = $startTs + $totalDurationSec;
                $endWithBreakTs = $endTs + ($breakMinutes * 60);

                if ($endWithBreakTs > $closeTs) {
                    $allAvailable = false;
                    $error_msg = "Service duration exceeds shop closing time. Please choose another time.";
                }
            }

            /* Auto-assign staff based on availability */
            if ($allAvailable) {
                $breakMinutes = 15;
                $pickedStaff = null;
                $bestNextAvailable = null;

                $catRes = $mysqli->query("
                    SELECT category_id
                    FROM services
                    WHERE id = $service_id
                    LIMIT 1
                ")->fetch_assoc();

                if (!$catRes) {
                    $allAvailable = false;
                    $error_msg = "Service not found. Please try again.";
                } else {
                    $catId = (int)$catRes['category_id'];
                    $svcEndWithBreak = $startTs + ($durationMin * 60) + ($breakMinutes * 60);
                    $svcEnd = $startTs + ($durationMin * 60);

                    // Category capacity check (e.g., nail seats)
                    $capRow = $mysqli->query("
                        SELECT capacity
                        FROM categories
                        WHERE id = $catId
                        LIMIT 1
                    ")->fetch_assoc();
                    $cap = isset($capRow['capacity']) ? (int)$capRow['capacity'] : 0;
                    if ($cap <= 0) {
                        $staffCount = $mysqli->query("
                            SELECT COUNT(*) AS c
                            FROM staff_categories
                            WHERE category_id = $catId
                        ")->fetch_assoc();
                        $cap = (int)$staffCount['c'];
                    }

                    if ($cap <= 0) {
                        $allAvailable = false;
                        $error_msg = "Selected time is FULL for this service category. Please choose another time.";
                    } else {
                        $apptRes = $mysqli->query("
                            SELECT a.appointment_time, s.duration
                            FROM appointments a
                            JOIN services s ON s.id = a.service_id
                            WHERE a.appointment_date = '$date'
                              AND a.status IN ('pending','approved')
                              AND s.category_id = $catId
                        ");
                        $overlapCount = 0;
                        $nextSeatFreeTs = null;
                        if ($apptRes && $apptRes->num_rows > 0) {
                            while ($ar = $apptRes->fetch_assoc()) {
                                $aStart = strtotime("$date {$ar['appointment_time']}");
                                $aMin = $durationToMinutes($ar['duration']);
                                $aEnd = $aStart + ($aMin * 60);
                                $aEndWithBreak = $aEnd + ($breakMinutes * 60);
                                if ($startTs < $aEnd && $svcEnd > $aStart) {
                                    $overlapCount++;
                                    if ($nextSeatFreeTs === null || $aEndWithBreak < $nextSeatFreeTs) {
                                        $nextSeatFreeTs = $aEndWithBreak;
                                    }
                                }
                            }
                        }

                        if ($overlapCount >= $cap) {
                            $allAvailable = false;
                            if ($nextSeatFreeTs !== null) {
                                $nextAvailableTime = date("H:i", $nextSeatFreeTs);
                                $error_msg = "Selected time is FULL for this service category. Next available time: $nextAvailableTime";
                            } else {
                                $error_msg = "Selected time is FULL for this service category. Please choose another time.";
                            }
                        }
                    }

                    if ($allAvailable) {
                        $staffRes = $mysqli->query("
                            SELECT st.id, st.name
                            FROM staff st
                            JOIN staff_categories sc ON sc.staff_id = st.id
                            WHERE sc.category_id = $catId
                            ORDER BY st.name ASC
                        ");

                        if ($staffRes && $staffRes->num_rows > 0) {
                            while ($st = $staffRes->fetch_assoc()) {
                                $staffId = (int)$st['id'];

                                $busyRes = $mysqli->query("
                                    SELECT a.appointment_time, s.duration
                                    FROM appointments a
                                    JOIN services s ON s.id = a.service_id
                                    WHERE a.appointment_date = '$date'
                                      AND a.status IN ('pending','approved')
                                      AND a.staff_id = $staffId
                                ");

                                $isBusy = false;
                                $staffNextAvailable = null;
                                if ($busyRes && $busyRes->num_rows > 0) {
                                    while ($b = $busyRes->fetch_assoc()) {
                                        $bStart = strtotime("$date {$b['appointment_time']}");
                                        $bMin = $durationToMinutes($b['duration']);
                                        $bEnd = $bStart + ($bMin * 60);
                                        $bEndWithBreak = $bEnd + ($breakMinutes * 60);

                                        if ($startTs < $bEndWithBreak && $svcEndWithBreak > $bStart) {
                                            $isBusy = true;
                                            if ($staffNextAvailable === null || $bEndWithBreak > $staffNextAvailable) {
                                                $staffNextAvailable = $bEndWithBreak;
                                            }
                                            break;
                                        }
                                    }
                                }

                                if (!$isBusy) {
                                    $pickedStaff = $staffId;
                                    break;
                                } else {
                                    if ($staffNextAvailable !== null && ($bestNextAvailable === null || $staffNextAvailable < $bestNextAvailable)) {
                                        $bestNextAvailable = $staffNextAvailable;
                                    }
                                }
                            }
                        }

                        if ($pickedStaff === null) {
                            $allAvailable = false;
                            if ($bestNextAvailable !== null) {
                                $nextAvailableTime = date("H:i", $bestNextAvailable);
                                $error_msg = "Selected time is FULL for this service category. Next available time: $nextAvailableTime";
                            } else {
                                $error_msg = "Selected time is FULL for this service category. Please choose another time.";
                            }
                        }
                    }
                }
            }

            /* Insert booking */
            if ($allAvailable) {

                $note_safe = $mysqli->real_escape_string($note);

                $appointment_group_id = bin2hex(random_bytes(8));

                $staffSql = $pickedStaff !== null ? (int)$pickedStaff : "NULL";
                /* Insert appointment (single service) */
                $mysqli->query("
        INSERT INTO appointments
        (appointment_group_id, user_id, service_id, staff_id, appointment_date, appointment_time, request, status)
        VALUES
        ('$appointment_group_id', $user_id, $service_id, $staffSql, '$date', '$time', '$note_safe', 'pending')
    ");

                $appointment_id = $mysqli->insert_id;

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
}

require_once("../include/header.php");
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 p-4">
                <h4 class="text-center mb-4 fw-bold text-primary"><?= htmlspecialchars($service['name']) ?></h4>

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
                        <button type="submit" class="btn btn-primary btn-lg px-5" <?= $isBlocked ? 'disabled' : '' ?>>Confirm Re-Book</button>
                        <a href="history.php" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>

                </form>


            </div>
        </div>
    </div>
</div>

<?php require_once('../public/alert.php');
