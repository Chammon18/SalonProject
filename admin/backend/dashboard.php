    <?php
require_once(__DIR__ . '/../auth_check.php');
// session_start();

    // ---------- Dashboard Stats ----------

    // TOTAL USERS
    $userCount = $mysqli->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

    // TOTAL SERVICES
    $serviceCount = $mysqli->query("SELECT COUNT(*) AS total FROM services")->fetch_assoc()['total'];

    // TODAY APPOINTMENTS
    $todayCount = $mysqli->query("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['total'];

    // PENDING APPOINTMENTS
    $pendingCount = $mysqli->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'pending'")->fetch_assoc()['total'];

    // STAFF AVAILABILITY GRID (BY DATE)
    $staffDate = $_GET['staff_date'] ?? date('Y-m-d');
    $staffDateSafe = $mysqli->real_escape_string($staffDate);
    $todayDate = date('Y-m-d');
    $tomorrowDate = date('Y-m-d', strtotime('+1 day'));
    $staffRows = $mysqli->query("
        SELECT st.id, st.name, GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') AS specialties
        FROM staff st
        LEFT JOIN staff_categories sc ON sc.staff_id = st.id
        LEFT JOIN categories c ON c.id = sc.category_id
        GROUP BY st.id, st.name
        ORDER BY st.name ASC
    ");
    $staffList = [];
    if ($staffRows) {
        while ($st = $staffRows->fetch_assoc()) {
            $staffList[] = $st;
        }
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

    $breakMinutes = 15;
    $todayAppts = $mysqli->query("
        SELECT a.staff_id, a.appointment_time, s.duration, s.name AS service_name
        FROM appointments a
        JOIN services s ON s.id = a.service_id
        WHERE a.appointment_date = '$staffDateSafe'
        AND a.status IN ('pending','approved')
        AND a.staff_id IS NOT NULL
    ");

    $busyIntervals = [];
    if ($todayAppts && $todayAppts->num_rows > 0) {
        while ($ap = $todayAppts->fetch_assoc()) {
            $sid = (int)$ap['staff_id'];
            $start = strtotime($staffDate . ' ' . $ap['appointment_time']);
            $minutes = $durationToMinutes($ap['duration']);
            $end = $start + ($minutes * 60) + ($breakMinutes * 60);
            if (!isset($busyIntervals[$sid])) {
                $busyIntervals[$sid] = [];
            }
            $busyIntervals[$sid][] = [$start, $end, $ap['service_name']];
        }
    }

    $slotMinutes = 30;
    $minTime = strtotime($staffDate . ' 09:00');
    $maxTime = strtotime($staffDate . ' 20:00');

    $timeSlots = [];
    for ($t = $minTime; $t < $maxTime; $t += ($slotMinutes * 60)) {
        $timeSlots[] = $t;
    }


    // UNREAD BOOKING NOTIFICATIONS
    $notificationCount = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM notifications n
    JOIN appointments a ON n.appointment_id = a.id
    WHERE n.is_read = 0 AND a.status = 'pending'
")->fetch_assoc()['total'];

    // UPCOMING APPOINTMENTS (NEXT 30 MIN)
    $upcomingAlerts = [];
    $upcomingRes = $mysqli->query("
    SELECT
        a.appointment_group_id,
        MIN(a.id) AS appointment_id,
        u.name AS customer_name,
        u.phone AS customer_phone,
        a.appointment_time,
        GROUP_CONCAT(s.name SEPARATOR ', ') AS services
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN services s ON a.service_id = s.id
    WHERE a.appointment_date = CURDATE()
      AND a.status IN ('pending','approved')
      AND TIMESTAMPDIFF(MINUTE, NOW(), CONCAT(a.appointment_date, ' ', a.appointment_time)) BETWEEN 0 AND 30
    GROUP BY a.appointment_group_id, a.appointment_time, u.name, u.phone
    ORDER BY a.appointment_time ASC
");
    if ($upcomingRes && $upcomingRes->num_rows > 0) {
        while ($r = $upcomingRes->fetch_assoc()) {
            $upcomingAlerts[] = $r;
        }
    }

    // REVENUE GRID (LAST 30 DAYS - COMPLETED)
    $revenueRows = [];
    $revenueRes = $mysqli->query("
        SELECT 
            a.appointment_date,
            COUNT(*) AS service_count,
            ROUND(SUM(s.price), 2) AS revenue_total
        FROM appointments a
        JOIN services s ON s.id = a.service_id
        WHERE a.status = 'completed'
          AND a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY a.appointment_date
        ORDER BY a.appointment_date DESC
    ");
    if ($revenueRes && $revenueRes->num_rows > 0) {
        while ($r = $revenueRes->fetch_assoc()) {
            $revenueRows[] = $r;
        }
    }

    $revenueLabels = array_map(function ($r) {
        return date('d M', strtotime($r['appointment_date']));
    }, array_reverse($revenueRows));
    $revenueValues = array_map(function ($r) {
        return (float)$r['revenue_total'];
    }, array_reverse($revenueRows));

    // TODAY PAYMENTS
    $todayPaymentRows = [];
    $paymentRes = $mysqli->query("
        SELECT
            p.id,
            p.paid_at,
            p.amount,
            p.payment_method,
            u.name AS customer_name,
            GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS services,
            GROUP_CONCAT(DISTINCT st.name ORDER BY st.name SEPARATOR ', ') AS staff_names
        FROM payments p
        JOIN appointments a0 ON a0.id = p.appointment_id
        JOIN appointments a ON a.appointment_group_id = a0.appointment_group_id
        JOIN users u ON u.id = a.user_id
        JOIN services s ON s.id = a.service_id
        LEFT JOIN staff st ON st.id = a.staff_id
        WHERE DATE(p.paid_at) = CURDATE()
        GROUP BY p.id, p.paid_at, p.amount, p.payment_method, u.name
        ORDER BY p.paid_at DESC
    ");
    if ($paymentRes && $paymentRes->num_rows > 0) {
        while ($r = $paymentRes->fetch_assoc()) {
            $todayPaymentRows[] = $r;
        }
    }

    ?>


