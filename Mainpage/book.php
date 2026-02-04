<?php
session_start();
require_once("../public/dp.php");
require_once("../public/common_query.php");

/* VARIABLES */
$error_msg = '';
$warning_msg = '';
$success_msg = '';
$date = '';
$time = '';
$note = '';
$prefillService = null;
$nextAvailableTime = null;
$allAvailable = false;
$endTimeDisplay = '';
$startTimeDisplay = '';

/* LOGIN CHECK */
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Please login first");
    exit;
}
$user_id = (int)$_SESSION['id'];

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

/* USER INFO */
$user = selectData("users", $mysqli, "id=$user_id", "name,phone")->fetch_assoc();

/* PREFILL SERVICE */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $prefillService = $mysqli->query("
        SELECT id FROM services WHERE id=" . (int)$_GET['id'] . " AND status=1
    ")->fetch_assoc();
}

/* CATEGORIES */
$categories = $mysqli->query("SELECT id, name, capacity FROM categories ORDER BY id");

/* TIME SLOTS */
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

$categoryStaffCounts = [];
$catStaffRes = $mysqli->query("
    SELECT category_id, COUNT(*) AS c
    FROM staff_categories
    GROUP BY category_id
");
if ($catStaffRes && $catStaffRes->num_rows > 0) {
    while ($r = $catStaffRes->fetch_assoc()) {
        $categoryStaffCounts[(int)$r['category_id']] = (int)$r['c'];
    }
}

$seatStatus = [];
$selectedStartTs = null;
if (!empty($date) && !empty($time)) {
    $selectedStartTs = strtotime("$date $time");
}
if ($selectedStartTs !== null) {
    $apptRes = $mysqli->query("
        SELECT s.category_id, a.appointment_time, s.duration
        FROM appointments a
        JOIN services s ON s.id = a.service_id
        WHERE a.appointment_date = '$date'
          AND a.status IN ('pending','approved')
    ");
    if ($apptRes && $apptRes->num_rows > 0) {
        while ($ar = $apptRes->fetch_assoc()) {
            $catId = (int)$ar['category_id'];
            $aStart = strtotime("$date {$ar['appointment_time']}");
            $aMin = $durationToMinutes($ar['duration']);
            $aEnd = $aStart + ($aMin * 60);
            if ($selectedStartTs >= $aStart && $selectedStartTs < $aEnd) {
                if (!isset($seatStatus[$catId])) {
                    $seatStatus[$catId] = 0;
                }
                $seatStatus[$catId]++;
            }
        }
    }
}

/* FORM SUBMIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isBlocked) {
        $error_msg = "Your bookings are temporarily blocked due to repeated cancellations (5+ in the last 7 days). Please contact the salon.";
    } else {

    $services = $_POST['services'] ?? [];
    $date     = $_POST['date'] ?? '';
    $time     = $_POST['time'] ?? '';
    $note     = $_POST['note'] ?? '';
    $conflictFound = false;
    $assignedStaff = [];

    if (!$services || !$date || !$time) {
        $error_msg = "Please fill all required fields.";
    } else {

        $startTs = strtotime("$date $time");
        $openTs  = strtotime("$date 09:00");
        $closeTs = strtotime("$date 20:00");
        if ($startTs !== false) {
            $startTimeDisplay = date('h:i A', $startTs);
        }

        if ($startTs < $openTs || $startTs >= $closeTs) {
            $error_msg = "Booking time must be between 9:00 AM and 8:00 PM.";
        } else {

            $allAvailable = true;

            /* 1️⃣ Prevent same user booking same service on same day */
            foreach ($services as $sid) {
                $sid = (int)$sid;
                $existing = $mysqli->query("
                            SELECT COUNT(*) AS c
                            FROM appointments a
                            WHERE a.user_id = $user_id
                            AND a.appointment_date = '$date'
                            AND a.service_id = $sid
                            AND a.status IN ('pending','approved')
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
                    SELECT duration
                    FROM services
                    WHERE id IN ($serviceIds)
                ");

                $totalMinutes = 0;
                if ($durRes && $durRes->num_rows > 0) {
                    while ($dr = $durRes->fetch_assoc()) {
                        $totalMinutes += $durationToMinutes($dr['duration']);
                    }
                }
                if ($totalMinutes <= 0) {
                    $allAvailable = false;
                    $error_msg = "Service duration is not set. Please contact admin.";
                }
                $totalDurationSec = $totalMinutes * 60;
                $breakMinutes = 15;
                $endTs = $startTs + $totalDurationSec;
                $endWithBreakTs = $endTs + ($breakMinutes * 60);
                $endTime = date('H:i:s', $endTs);
                $endTimeDisplay = date('h:i A', $endTs);

                /* readable duration */
                $mins = $totalMinutes;
                $durationText = $mins < 60
                    ? "$mins minutes"
                    : intdiv($mins, 60) . " hr" . ($mins % 60 ? " " . ($mins % 60) . " min" : "");

                /* 3️⃣ Close time check */
                if ($endWithBreakTs > $closeTs) {
                    $allAvailable = false;
                    $error_msg = "Selected services take $durationText.
                    Our working hours are 09:00 AM to 08:00 PM. Please choose another time.";
                }
            }

            /* 4) AUTO-ASSIGN STAFF (BEST SOLUTION) */
            if ($allAvailable) {
                $assignedStaff = [];
                $breakMinutes = 15;
                $checkedCategories = [];
                $categoryCapacity = [];
                $categoryAppointments = [];
                foreach ($services as $sid) {
                    $sid = (int)$sid;

                    // Find category + duration for the service
                    $svc = $mysqli->query("
                        SELECT category_id, duration
                        FROM services
                        WHERE id = $sid
                        LIMIT 1
                    ")->fetch_assoc();

                    if (!$svc) {
                        $allAvailable = false;
                        $error_msg = "Service not found. Please try again.";
                        break;
                    }

                    $catId = (int)$svc['category_id'];
                    $svcMinutes = $durationToMinutes($svc['duration']);
                    if ($svcMinutes <= 0) {
                        $allAvailable = false;
                        $error_msg = "Service duration is not set. Please contact admin.";
                        break;
                    }
                    $svcEndWithBreak = $startTs + ($svcMinutes * 60) + ($breakMinutes * 60);
                    $svcEnd = $startTs + ($svcMinutes * 60);

                    // Category capacity check (e.g., nail seats)
                    if (!isset($checkedCategories[$catId])) {
                        if (!isset($categoryCapacity[$catId])) {
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
                            $categoryCapacity[$catId] = $cap;
                        }

                        if (!isset($categoryAppointments[$catId])) {
                            $apptRes = $mysqli->query("
                                SELECT a.appointment_time, s.duration
                                FROM appointments a
                                JOIN services s ON s.id = a.service_id
                                WHERE a.appointment_date = '$date'
                                  AND a.status IN ('pending','approved')
                                  AND s.category_id = $catId
                            ");
                            $categoryAppointments[$catId] = [];
                            if ($apptRes && $apptRes->num_rows > 0) {
                                while ($ar = $apptRes->fetch_assoc()) {
                                    $categoryAppointments[$catId][] = $ar;
                                }
                            }
                        }

                        $capacity = $categoryCapacity[$catId];
                        if ($capacity <= 0) {
                            $allAvailable = false;
                            $error_msg = "Selected time is FULL for this service category. Please choose another time.";
                            break;
                        }

                        $overlapCount = 0;
                        $nextSeatFreeTs = null;
                        foreach ($categoryAppointments[$catId] as $ar) {
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

                        if ($overlapCount >= $capacity) {
                            $allAvailable = false;
                            if ($nextSeatFreeTs !== null) {
                                $nextAvailableTime = date("H:i", $nextSeatFreeTs);
                                $error_msg = "Selected time is FULL for this service category. Next available time: $nextAvailableTime";
                            } else {
                                $error_msg = "Selected time is FULL for this service category. Please choose another time.";
                            }
                            break;
                        }

                        $checkedCategories[$catId] = true;
                    }

                    // Staff linked to this category
                    $staffRes = $mysqli->query("
                        SELECT st.id, st.name
                        FROM staff st
                        JOIN staff_categories sc ON sc.staff_id = st.id
                        WHERE sc.category_id = $catId
                        ORDER BY st.name ASC
                    ");

                    $pickedStaff = null;
                    $bestNextAvailable = null;
                    if ($staffRes && $staffRes->num_rows > 0) {
                        while ($st = $staffRes->fetch_assoc()) {
                            $staffId = (int)$st['id'];

                            // Check if this staff is busy during the requested window
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
                        break;
                    }

                    $assignedStaff[$sid] = $pickedStaff;
                }
            }
        }
    }

    /* 5️⃣ INSERT */
    if ($allAvailable) {

        $note_safe = $mysqli->real_escape_string($note);
        $appointment_group_id = bin2hex(random_bytes(8));
        $first_appointment_id = null;

        foreach ($services as $sid) {
            $sid = (int)$sid;
            $staffSql = isset($assignedStaff[$sid]) ? (int)$assignedStaff[$sid] : "NULL";
            $mysqli->query("
                        INSERT INTO appointments
                        (appointment_group_id, user_id, service_id, staff_id, appointment_date, appointment_time, request, status)
                        VALUES
                        ('$appointment_group_id', $user_id, $sid, $staffSql, '$date', '$time', '$note_safe', 'pending')
                    ");
            if ($first_appointment_id === null) {
                $first_appointment_id = $mysqli->insert_id;
            }
        }

        // Insert notification ( IN ADMIN)
        $notifMessage = "New appointment booked by " . htmlspecialchars($user['name']) . " on $date at $time.";

        if ($first_appointment_id !== null) {
            $mysqli->query("
                INSERT INTO notifications 
                (user_id, appointment_id, message, is_read, show_in_nav)
                VALUES 
                (0, $first_appointment_id, '" . $mysqli->real_escape_string($notifMessage) . "', 0, 1)
            ");
        }
        $success_msg = "Booking successful! We received your booking for $date at $time.";
        $redirect_url = "confirmbooking.php";
    }
    }
}
require_once("../include/header.php");
?>

<div class="container my-3">
    <div class="card p-4 shadow">
        <h4 class="text-center text-success mb-3">Book Your Visit </h4>

        <form method="post">
            <div class="row g-2">
                <div class="col-md-6">
                    <input class="form-control" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                </div>
                <div class="col-md-6">
                    <input class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" readonly>
                </div>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <input type="date" name="date" class="form-control"
                        min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date) ?>" required>
                </div>
                <div class="col-md-6">
                    <select name="time" class="form-control" required>
                        <option value="">Choose time</option>
                        <?php foreach ($timeSlots as $ts): ?>
                            <option value="<?= $ts ?>" <?= $time === $ts ? 'selected' : '' ?>>
                                <?= $ts ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <?php
                    $catId = (int)$cat['id'];
                    $cap = isset($cat['capacity']) ? (int)$cat['capacity'] : 0;
                    if ($cap <= 0) {
                        $cap = $categoryStaffCounts[$catId] ?? 0;
                    }
                    $busyNow = $seatStatus[$catId] ?? 0;
                    $remaining = $cap > 0 ? max(0, $cap - $busyNow) : 0;
                    ?>
                    <div class="col-md-6">
                        <div class="border rounded p-2 h-100">
                            <b><?= htmlspecialchars($cat['name']) ?></b>
                            <div class="mt-2">
                                <?php
                                $srv = $mysqli->query("
                                    SELECT id,name FROM services
                                    WHERE category_id={$cat['id']} AND status=1
                                ");
                                while ($s = $srv->fetch_assoc()):
                                ?>
                                    <label class="d-block">
                                        <input type="checkbox" name="services[]" value="<?= $s['id'] ?>"
                                            <?= ($prefillService && $prefillService['id'] == $s['id']) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?>
                                    </label>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <textarea name="note" class="form-control mt-3"
                placeholder="Special request"><?= htmlspecialchars($note) ?></textarea>

            <button class="btn btn-success w-100 mt-3" <?= $isBlocked ? 'disabled' : '' ?>>Confirm Booking</button>
        </form>
    </div>
</div>

<?php
require_once("../public/alert.php");
require_once("../include/footer.php");
?>
