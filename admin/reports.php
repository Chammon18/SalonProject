<?php
require_once("../public/dp.php");
require_once("adminheader.php");

$tab = $_GET['tab'] ?? 'incentive';
$safeTab = in_array($tab, ['incentive', 'cancelled', 'revenue'], true) ? $tab : 'incentive';

// Shared filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('Y-m');
$mode = $_GET['mode'] ?? 'daily';
$safeMode = $mode === 'monthly' ? 'monthly' : 'daily';

$startDate = $mysqli->real_escape_string($startDate);
$endDate = $mysqli->real_escape_string($endDate);
$month = $mysqli->real_escape_string($month);

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
    } else {
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
?>

<div class="content">
    <div class="container report-compact">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h4 class="mb-0">Reports</h4>
        </div>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $safeTab === 'incentive' ? 'active' : '' ?>" href="?tab=incentive&mode=<?= urlencode($safeMode) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&month=<?= urlencode($month) ?>&staff_id=<?= urlencode((string)$staffId) ?>">Incentive</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $safeTab === 'cancelled' ? 'active' : '' ?>" href="?tab=cancelled&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">Cancelled</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $safeTab === 'revenue' ? 'active' : '' ?>" href="?tab=revenue&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">Revenue</a>
            </li>
        </ul>

        <?php if ($safeTab === 'incentive'): ?>
            <div class="card shadow-sm mb-2">
                <div class="card-body py-2">
                    <form method="get" class="row g-2 align-items-end compact-form">
                        <input type="hidden" name="tab" value="incentive">
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select name="mode" class="form-select">
                                <option value="daily" <?= $safeMode === 'daily' ? 'selected' : '' ?>>Daily</option>
                                <option value="monthly" <?= $safeMode === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Staff</label>
                            <select name="staff_id" class="form-select">
                                <option value="">All Staff</option>
                                <?php foreach ($staffList as $st): ?>
                                    <option value="<?= (int)$st['id'] ?>" <?= $staffId === (int)$st['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Month</label>
                            <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
                        </div>
                        <div class="col-12 text-end mt-2">
                            <button class="btn btn-success btn-sm">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <h6 class="mb-2"><?= $safeMode === 'daily' ? 'Daily Completed Services' : 'Monthly Completed Services' ?></h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle compact-table">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($safeMode === 'daily'): ?><th>Date</th><?php endif; ?>
                                    <th>Staff</th>
                                    <th>Service</th>
                                    <th>Count</th>
                                    <th>Price</th>
                                    <th>Incentive %</th>
                                    <th>Total Incentive</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rows = $safeMode === 'daily' ? $dailyRows : $monthlyRows; ?>
                                <?php if (!empty($rows)): ?>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <?php if ($safeMode === 'daily'): ?>
                                                <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                            <?php endif; ?>
                                            <td><?= htmlspecialchars($row['staff_name']) ?></td>
                                            <td><?= htmlspecialchars($row['service_name']) ?></td>
                                            <td><?= (int)$row['service_count'] ?></td>
                                            <td><?= number_format((float)$row['price'], 2) ?></td>
                                            <td><?= number_format((float)$row['incentive_percent'], 2) ?>%</td>
                                            <td><?= number_format((float)$row['incentive_total'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= $safeMode === 'daily' ? 7 : 6 ?>" class="text-center text-muted">No completed services found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($safeTab === 'cancelled'): ?>
            <div class="card shadow-sm mb-2">
                <div class="card-body py-2">
                    <form method="get" class="row g-2 align-items-end compact-form">
                        <input type="hidden" name="tab" value="cancelled">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        <div class="col-12 text-end mt-2">
                            <button class="btn btn-success btn-sm">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <h6 class="mb-2">Cancelled Appointments</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle compact-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Staff</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($cancelRows)): ?>
                                    <?php foreach ($cancelRows as $row): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                            <td><?= htmlspecialchars($row['service_name']) ?></td>
                                            <td><?= htmlspecialchars($row['staff_name'] ?? '-') ?></td>
                                            <td><?= (int)$row['cancel_count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No cancelled appointments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($safeTab === 'revenue'): ?>
            <div class="card shadow-sm mb-2">
                <div class="card-body py-2">
                    <form method="get" class="row g-2 align-items-end compact-form">
                        <input type="hidden" name="tab" value="revenue">
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select name="mode" class="form-select">
                                <option value="daily" <?= $safeMode === 'daily' ? 'selected' : '' ?>>Daily</option>
                                <option value="monthly" <?= $safeMode === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Month</label>
                            <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
                        </div>
                        <div class="col-12 text-end mt-2">
                            <button class="btn btn-success btn-sm">Apply Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body py-2">
                    <h6 class="mb-2"><?= $safeMode === 'monthly' ? 'Monthly Revenue Summary' : 'Daily Revenue Summary' ?></h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle compact-table">
                            <thead class="table-light">
                                <tr>
                                    <th><?= $safeMode === 'monthly' ? 'Month' : 'Date' ?></th>
                                    <th>Services</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($revenueRows)): ?>
                                    <?php foreach ($revenueRows as $row): ?>
                                        <tr>
                                            <?php if ($safeMode === 'monthly'): ?>
                                                <td><?= date('M Y', strtotime($row['month_key'] . '-01')) ?></td>
                                            <?php else: ?>
                                                <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                            <?php endif; ?>
                                            <td><?= (int)$row['service_count'] ?></td>
                                            <td><?= number_format((float)$row['revenue_total'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No revenue data found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .report-compact .card {
        border-radius: 12px;
    }

    .report-compact .form-label {
        font-size: 12px;
        margin-bottom: 4px;
        color: #555;
    }

    .report-compact .form-control,
    .report-compact .form-select {
        height: 36px;
        font-size: 13px;
        padding: 6px 10px;
    }

    .report-compact .btn {
        padding: 6px 10px;
        font-size: 12px;
    }

    .compact-table th,
    .compact-table td {
        padding: 8px 10px;
        font-size: 13px;
    }

    .compact-table thead th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
</style>