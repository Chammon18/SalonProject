<?php
require_once(__DIR__ . '/../auth_check.php');
$tab = $_GET['tab'] ?? 'incentive';
$safeTab = in_array($tab, ['incentive', 'cancelled', 'revenue', 'payments'], true) ? $tab : 'incentive';
$isPrint = ($_GET['print'] ?? '') === '1';
$printParams = $_GET;
$printParams['tab'] = $safeTab;
$printParams['print'] = '1';
$printUrl = '?' . http_build_query($printParams);

// Shared filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('Y-m');
$mode = $_GET['mode'] ?? 'daily';
$safeMode = in_array($mode, ['daily', 'monthly', 'monthly_staff'], true) ? $mode : 'daily';
$paymentMethod = $_GET['payment_method'] ?? '';
$allowedMethods = ['Cash', 'Kpay', 'AYApay'];
$safePaymentMethod = in_array($paymentMethod, $allowedMethods, true) ? $paymentMethod : '';

$startDate = $mysqli->real_escape_string($startDate);
$endDate = $mysqli->real_escape_string($endDate);
$month = $mysqli->real_escape_string($month);
$safePaymentMethod = $mysqli->real_escape_string($safePaymentMethod);

// Staff list for filters
$staffList = [];
$staffRes = $mysqli->query("SELECT id, name FROM staff ORDER BY name ASC");
if ($staffRes && $staffRes->num_rows > 0) {
    while ($s = $staffRes->fetch_assoc()) {
        $staffList[] = $s;
    }
}
$staffFilter = $_GET['staff_id'] ?? '';
$staffId = is_numeric($staffFilter) ? (int)$staffFilter : 0;
$staffWhere = $staffId > 0 ? " AND a.staff_id = $staffId" : "";

// Incentive report data
$dailyRows = [];
$monthlyRows = [];
$monthlyStaffRows = [];
if ($safeTab === 'incentive') {
    $statusFilter = "a.status = 'completed'";

    if ($safeMode === 'daily') {
        $dailySql = "
            SELECT 
                a.appointment_date,
                st.name AS staff_name,
                s.name AS service_name,
                s.price,
                c.incentive_percent,
                COUNT(*) AS service_count,
                ROUND(COUNT(*) * (s.price * c.incentive_percent / 100), 2) AS incentive_total
            FROM appointments a
            JOIN staff st ON st.id = a.staff_id
            JOIN services s ON s.id = a.service_id
            JOIN categories c ON c.id = s.category_id
            WHERE $statusFilter
              AND a.staff_id IS NOT NULL
              AND a.appointment_date BETWEEN '$startDate' AND '$endDate'
              $staffWhere
            GROUP BY a.appointment_date, st.name, s.name, s.price, c.incentive_percent
            ORDER BY a.appointment_date DESC, st.name ASC, s.name ASC
        ";
        $res = $mysqli->query($dailySql);
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $dailyRows[] = $r;
            }
        }
    } elseif ($safeMode === 'monthly') {
        $monthStart = $month . "-01";
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        $monthlySql = "
            SELECT 
                st.name AS staff_name,
                s.name AS service_name,
                s.price,
                c.incentive_percent,
                COUNT(*) AS service_count,
                ROUND(COUNT(*) * (s.price * c.incentive_percent / 100), 2) AS incentive_total
            FROM appointments a
            JOIN staff st ON st.id = a.staff_id
            JOIN services s ON s.id = a.service_id
            JOIN categories c ON c.id = s.category_id
            WHERE $statusFilter
              AND a.staff_id IS NOT NULL
              AND a.appointment_date BETWEEN '$monthStart' AND '$monthEnd'
              $staffWhere
            GROUP BY st.name, s.name, s.price, c.incentive_percent
            ORDER BY st.name ASC, s.name ASC
        ";
        $res = $mysqli->query($monthlySql);
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $monthlyRows[] = $r;
            }
        }
    } else {
        $monthStart = $month . "-01";
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        $monthlyStaffSql = "
            SELECT 
                st.name AS staff_name,
                COUNT(*) AS service_count,
                ROUND(SUM(s.price * c.incentive_percent / 100), 2) AS incentive_total
            FROM appointments a
            JOIN staff st ON st.id = a.staff_id
            JOIN services s ON s.id = a.service_id
            JOIN categories c ON c.id = s.category_id
            WHERE $statusFilter
              AND a.staff_id IS NOT NULL
              AND a.appointment_date BETWEEN '$monthStart' AND '$monthEnd'
              $staffWhere
            GROUP BY st.name
            ORDER BY st.name ASC
        ";
        $res = $mysqli->query($monthlyStaffSql);
        if ($res && $res->num_rows > 0) {
            while ($r = $res->fetch_assoc()) {
                $monthlyStaffRows[] = $r;
            }
        }
    }
}

// Cancelled report data
$cancelRows = [];
if ($safeTab === 'cancelled') {
    $cancelSql = "
        SELECT 
            a.appointment_date,
            u.name AS customer_name,
            s.name AS service_name,
            st.name AS staff_name,
            COUNT(*) AS cancel_count
        FROM appointments a
        JOIN users u ON u.id = a.user_id
        JOIN services s ON s.id = a.service_id
        LEFT JOIN staff st ON st.id = a.staff_id
        WHERE a.status IN ('cancelled','canceled')
          AND a.appointment_date BETWEEN '$startDate' AND '$endDate'
        GROUP BY a.appointment_date, u.name, s.name, st.name
        ORDER BY a.appointment_date DESC, u.name ASC
    ";
    $res = $mysqli->query($cancelSql);
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $cancelRows[] = $r;
        }
    }
}

// Revenue report data
$revenueRows = [];
if ($safeTab === 'revenue') {
    if ($safeMode === 'monthly') {
        $monthStart = $month . "-01";
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        $revenueSql = "
            SELECT 
                DATE_FORMAT(a.appointment_date, '%Y-%m') AS month_key,
                COUNT(*) AS service_count,
                ROUND(SUM(s.price), 2) AS revenue_total
            FROM appointments a
            JOIN services s ON s.id = a.service_id
            WHERE a.status = 'completed'
              AND a.appointment_date BETWEEN '$monthStart' AND '$monthEnd'
            GROUP BY month_key
            ORDER BY month_key DESC
        ";
    } else {
        $revenueSql = "
            SELECT 
                a.appointment_date,
                COUNT(*) AS service_count,
                ROUND(SUM(s.price), 2) AS revenue_total
            FROM appointments a
            JOIN services s ON s.id = a.service_id
            WHERE a.status = 'completed'
              AND a.appointment_date BETWEEN '$startDate' AND '$endDate'
            GROUP BY a.appointment_date
            ORDER BY a.appointment_date DESC
        ";
    }
    $res = $mysqli->query($revenueSql);
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $revenueRows[] = $r;
        }
    }
}

// Payments report data
$paymentRows = [];
if ($safeTab === 'payments') {
    $methodWhere = $safePaymentMethod !== '' ? " AND p.payment_method = '$safePaymentMethod'" : "";
    if ($safeMode === 'monthly') {
        $monthStart = $month . "-01";
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        $paymentSql = "
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
            WHERE DATE(p.paid_at) BETWEEN '$monthStart' AND '$monthEnd'
            $methodWhere
            GROUP BY p.id, p.paid_at, p.amount, p.payment_method, u.name
            ORDER BY p.paid_at DESC
        ";
    } else {
        $paymentSql = "
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
            WHERE DATE(p.paid_at) BETWEEN '$startDate' AND '$endDate'
            $methodWhere
            GROUP BY p.id, p.paid_at, p.amount, p.payment_method, u.name
            ORDER BY p.paid_at DESC
        ";
    }
    $res = $mysqli->query($paymentSql);
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $paymentRows[] = $r;
        }
    }
}
?>

