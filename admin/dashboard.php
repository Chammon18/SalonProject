    <?php
    // session_start();
    require_once("../public/dp.php");
    require_once("adminheader.php");

    // ---------- Dashboard Stats ----------

    // TOTAL USERS
    $userCount = $mysqli->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];

    // TOTAL SERVICES
    $serviceCount = $mysqli->query("SELECT COUNT(*) AS total FROM services")->fetch_assoc()['total'];

    // TODAY APPOINTMENTS
    $todayCount = $mysqli->query("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()")->fetch_assoc()['total'];

    // PENDING APPOINTMENTS
    $pendingCount = $mysqli->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'pending'")->fetch_assoc()['total'];

    // STAFF AVAILABILITY GRID (TODAY)
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
        WHERE DATE(a.appointment_date) = CURDATE()
        AND a.status IN ('pending','approved')
        AND a.staff_id IS NOT NULL
    ");

    $busyIntervals = [];
    if ($todayAppts && $todayAppts->num_rows > 0) {
        while ($ap = $todayAppts->fetch_assoc()) {
            $sid = (int)$ap['staff_id'];
            $start = strtotime(date('Y-m-d') . ' ' . $ap['appointment_time']);
            $minutes = $durationToMinutes($ap['duration']);
            $end = $start + ($minutes * 60) + ($breakMinutes * 60);
            if (!isset($busyIntervals[$sid])) {
                $busyIntervals[$sid] = [];
            }
            $busyIntervals[$sid][] = [$start, $end, $ap['service_name']];
        }
    }

    $slotMinutes = 30;
    $minTime = strtotime(date('Y-m-d') . ' 09:00');
    $maxTime = strtotime(date('Y-m-d') . ' 20:00');

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

    ?>

    <div class="content">
        <div class="row mb-3">

            <!-- NOTIFICATIONS -->
            <div class="col-md-3">
                <a href="notification.php" class="text-decoration-none text-dark">
                    <div class="card shadow border-0 rounded-4 stat-card hover-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon bg-info position-relative">
                                <i class="fa-solid fa-bell"></i>

                                <?php if ($notificationCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $notificationCount ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="ms-3">
                                <h6 class="mb-0 text-muted">New Bookings</h6>
                                <h3 class="fw-bold"><?= $notificationCount ?></h3>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- RIGHT: Quick Stats Cards -->
            <div class="col-md-9">
                <div class="row g-3">
                    <!-- CUSTOMERS -->
                    <div class="col-md-3">
                        <a href="user_management.php?tab=user" class="text-decoration-none text-dark">
                            <div class="card shadow border-0 rounded-4 stat-card hover-card">
                                <div class="card-body d-flex align-items-center">
                                    <div class="icon bg-success">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 text-muted">Customers</h6>
                                        <h3 class="fw-bold"><?= $userCount ?></h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- SERVICES -->
                    <div class="col-md-3">
                        <a href="services.php" class="text-decoration-none text-dark">
                            <div class="card shadow border-0 rounded-4 stat-card hover-card">
                                <div class="card-body d-flex align-items-center">
                                    <div class="icon bg-primary">
                                        <i class="fa-solid fa-spa"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 text-muted">Services</h6>
                                        <h3 class="fw-bold"><?= $serviceCount ?></h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- TODAY -->
                    <div class="col-md-3">
                        <a href="appointment.php?filter=today" class="text-decoration-none text-dark">
                            <div class="card shadow border-0 rounded-4 stat-card hover-card">
                                <div class="card-body d-flex align-items-center">
                                    <div class="icon bg-warning">
                                        <i class="fa-solid fa-calendar-day"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 text-muted">Today</h6>
                                        <h3 class="fw-bold"><?= $todayCount ?></h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- PENDING -->
                    <div class="col-md-3">
                        <a href="appointment.php?status=pending" class="text-decoration-none text-dark">
                            <div class="card shadow border-0 rounded-4 stat-card hover-card">
                                <div class="card-body d-flex align-items-center">
                                    <div class="icon bg-danger">
                                        <i class="fa-solid fa-clock"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h6 class="mb-0 text-muted">Pending</h6>
                                        <h3 class="fw-bold"><?= $pendingCount ?></h3>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2.5: Staff Availability Grid -->
        <div class="card mt-3">
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">Today's Staff Availability</h5>
                    <div class="d-flex align-items-center gap-3 small flex-wrap">
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="staff-legend staff-free"></span> Free
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="staff-legend staff-busy"></span> Busy
                        </span>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table staff-grid mb-0">
                    <thead>
                        <tr>
                            <th class="sticky-col">Staff</th>
                            <?php foreach ($timeSlots as $ts): ?>
                                <th class="time-col"><?= date('H:i', $ts) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($staffList) > 0): ?>
                            <?php foreach ($staffList as $st): ?>
                                <?php
                                $sid = (int)$st['id'];
                                $intervals = $busyIntervals[$sid] ?? [];
                                ?>
                                <tr>
                                    <td class="sticky-col">
                                        <div><?= htmlspecialchars($st['name']) ?></div>
                                        <?php if (!empty($st['specialties'])): ?>
                                            <small class="text-muted staff-specialty"><?= htmlspecialchars($st['specialties']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <?php foreach ($timeSlots as $ts): ?>
                                        <?php
                                        $slotStart = $ts;
                                        $slotEnd = $ts + ($slotMinutes * 60);
                                        $isBusy = false;
                                        $busyServices = [];
                                        $labelForSlot = '';
                                        foreach ($intervals as $iv) {
                                            $ivStart = $iv[0];
                                            $ivEnd = $iv[1];
                                            if ($slotStart < $ivEnd && $slotEnd > $ivStart) {
                                                $isBusy = true;
                                                if (!empty($iv[2])) {
                                                    $busyServices[] = $iv[2];
                                                }
                                                // Only show label at the first slot of the interval
                                                if ($labelForSlot === '' && $slotStart <= $ivStart && $ivStart < $slotEnd) {
                                                    $labelForSlot = $iv[2];
                                                }
                                            }
                                        }
                                        $busyLabel = $busyServices ? implode(', ', array_unique($busyServices)) : '';
                                        ?>
                                        <td class="<?= $isBusy ? 'busy-cell' : 'free-cell' ?>" title="<?= htmlspecialchars($busyLabel) ?>">
                                            <?php if ($isBusy && $labelForSlot): ?>
                                                <small class="slot-text"><?= htmlspecialchars($labelForSlot) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= count($timeSlots) + 1 ?>" class="text-center text-muted">
                                    No staff found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Row: Revenue Grid (Last 30 Days) -->
        <div class="card mt-3">
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">Revenue (Last 30 Days)</h5>
                    <a href="reports.php?tab=revenue" class="small text-decoration-none">View full report</a>
                </div>
            </div>
            <div class="px-3 pb-3">
                <canvas id="revenueChart" height="90"></canvas>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 revenue-grid">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Services</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($revenueRows)): ?>
                            <?php foreach ($revenueRows as $row): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                    <td><?= (int)$row['service_count'] ?></td>
                                    <td><?= number_format((float)$row['revenue_total'], 0) ?> MMK</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No completed services in last 30 days.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php if (!empty($upcomingAlerts)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const items = <?= json_encode(array_map(function ($r) {
                                    return [
                                        'time' => date('h:i A', strtotime($r['appointment_time'])),
                                        'name' => $r['customer_name'],
                                        'phone' => $r['customer_phone'],
                                        'services' => $r['services'],
                                    ];
                                }, $upcomingAlerts)); ?>;

                const listHtml = items.map(i =>
                    `<div style="text-align:left;margin-bottom:8px;">
                    <strong>${i.time}</strong> - ${i.name} (${i.phone})<br>
                    <small>${i.services}</small>
                 </div>`
                ).join('');

                Swal.fire({
                    title: 'Upcoming Appointments (Next 30 Minutes)',
                    html: listHtml,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;
            const labels = <?= json_encode($revenueLabels) ?>;
            const values = <?= json_encode($revenueValues) ?>;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Revenue',
                        data: values,
                        borderColor: '#2f6f55',
                        backgroundColor: 'rgba(47, 111, 85, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' MMK';
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const v = context.parsed.y || 0;
                                    return 'Revenue: ' + v.toLocaleString() + ' MMK';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    <style>
        .staff-grid th,
        .staff-grid td {
            text-align: center;
            vertical-align: middle;
            padding: 6px 8px;
            border-color: #eee;
        }

        .staff-grid .time-col {
            font-size: 12px;
            color: #666;
            white-space: nowrap;
        }

        .staff-grid .sticky-col {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 2;
            text-align: left;
            min-width: 140px;
            box-shadow: 2px 0 0 #f2f2f2;
        }

        .staff-grid .staff-specialty {
            display: block;
            font-size: 11px;
            line-height: 1.2;
        }

        .staff-grid td {
            width: 32px;
            height: 24px;
        }

        .staff-grid .free-cell {
            background: #d9f7e8;
        }

        .staff-grid .busy-cell {
            background: red;
        }

        .staff-grid .slot-text {
            display: block;
            font-size: 10px;
            line-height: 1.1;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
        }

        .staff-legend {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            display: inline-block;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .staff-legend.staff-free {
            background: green;
        }

        .staff-legend.staff-busy {
            background: red;
        }

        .revenue-grid th,
        .revenue-grid td {
            padding: 8px 10px;
            font-size: 13px;
        }
    </style>