<?php require_once("adminheader.php"); ?>


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
                    <div>
                        <h5 class="mb-0">Staff Availability</h5>
                        <small class="text-muted"><?= date('d M Y', strtotime($staffDate)) ?></small>
                    </div>
                    <div class="d-flex align-items-center gap-3 small flex-wrap">
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="staff-legend staff-free"></span> Free
                        </span>
                        <span class="d-inline-flex align-items-center gap-1">
                            <span class="staff-legend staff-busy"></span> Busy
                        </span>
                    </div>
                    <form class="staff-date-search" method="get" action="dashboard.php">
                        <input id="staff-date" type="date" name="staff_date" class="form-control form-control-sm"
                            value="<?= htmlspecialchars($staffDate) ?>"
                            min="<?= htmlspecialchars($todayDate) ?>" />
                        <button class="btn btn-sm btn-primary" type="submit">Go</button>
                        <a class="btn btn-sm btn-outline-secondary" href="dashboard.php?staff_date=<?= htmlspecialchars($tomorrowDate) ?>">Tomorrow</a>
                    </form>
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

        <!-- Row: Today's Payments -->
        <div class="card mt-3">
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">Today's Payments</h5>
                    <a href="reports.php?tab=payments" class="small text-decoration-none">View full report</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 revenue-grid">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Staff</th>
                            <th>Method</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($todayPaymentRows)): ?>
                            <?php foreach ($todayPaymentRows as $row): ?>
                                <tr>
                                    <td><?= date('h:i A', strtotime($row['paid_at'])) ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($row['services']) ?></td>
                                    <td><?= htmlspecialchars($row['staff_names'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                    <td><?= number_format((float)$row['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No payments recorded today.</td>
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
<?php require_once('adminfooter.php'); ?>


