<?php
require_once("../public/dp.php");
require_once("adminheader.php");

//filter
$selectedDate  = $_GET['date'] ?? date('Y-m-d');
$statusFilter  = $_GET['status'] ?? '';
$statusSafe    = $mysqli->real_escape_string($statusFilter);



$sql = "
SELECT
    a.id AS appointment_id,
    a.appointment_date,
    a.appointment_time,
    a.request,
    a.status AS appointment_status,
    u.name AS user_name,
    u.phone AS user_phone,
    s.id AS service_id,
    s.name AS service_name,
    s.duration AS duration_raw,
    s.category_id AS category_id,
    st.name AS staff_name
FROM appointments a
JOIN users u ON u.id = a.user_id
JOIN services s ON s.id = a.service_id
LEFT JOIN staff st ON st.id = a.staff_id
WHERE a.appointment_date = '$selectedDate'
" . ($statusSafe ? " AND a.status = '$statusSafe'" : "") . "
 ORDER BY 
    FIELD(a.status, 'pending', 'approved', 'completed', 'cancelled'),
    a.appointment_time ASC

    ";


$result = $mysqli->query($sql);
$isPastSelected = strtotime($selectedDate) < strtotime(date('Y-m-d'));

?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- <a href="appointment.php" class="btn btn-primary">New Appointment</a> -->

        <form method="GET" class="d-flex gap-2">
            <input type="date" name="date" class="form-control"
                value="<?= htmlspecialchars($selectedDate) ?>">

            <select name="status" class="form-select">
                <option value="">All Status</option>
                <?php
                foreach (['pending', 'approved', 'completed', 'cancelled'] as $s) {
                    $sel = $statusFilter === $s ? 'selected' : '';
                    echo "<option value='$s' $sel>" . ucfirst($s) . "</option>";
                }
                ?>
            </select>

            <button class="btn btn-success">Filter</button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Phone</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Request</th>
                        <th>Status</th>
                        <th>Staff</th>
                        <th>Availability</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php
                        $breakMinutes = 15;
                        $categoryStaffCount = [];
                        $categoryAppointments = [];
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
                        ?>
                        <?php while ($row = $result->fetch_assoc()): ?>

                            <?php
                            $categoryId = (int)$row['category_id'];
                            // Start time per appointment
                            $startTime = strtotime($row['appointment_time']);
                            $durationMinutes = $durationToMinutes($row['duration_raw']);
                            $endTime   = strtotime("+{$durationMinutes} minutes", $startTime);

                            if (!isset($categoryStaffCount[$categoryId])) {
                                $countRes = $mysqli->query("
                                    SELECT COUNT(*) AS c
                                    FROM staff_categories
                                    WHERE category_id = $categoryId
                                ")->fetch_assoc();
                                $categoryStaffCount[$categoryId] = (int)$countRes['c'];
                            }

                            if (!isset($categoryAppointments[$categoryId])) {
                                $apptRes = $mysqli->query("
                                    SELECT a.staff_id, a.appointment_time, s.duration
                                    FROM appointments a
                                    JOIN services s ON s.id = a.service_id
                                    WHERE a.appointment_date = '$selectedDate'
                                      AND a.status IN ('pending','approved')
                                      AND s.category_id = $categoryId
                                      AND a.staff_id IS NOT NULL
                                ");
                                $categoryAppointments[$categoryId] = [];
                                if ($apptRes && $apptRes->num_rows > 0) {
                                    while ($ar = $apptRes->fetch_assoc()) {
                                        $categoryAppointments[$categoryId][] = $ar;
                                    }
                                }
                            }

                            $busyStaff = [];
                            $windowStart = $startTime;
                            $windowEnd = $endTime;
                            foreach ($categoryAppointments[$categoryId] as $ar) {
                                $aStart = strtotime($ar['appointment_time']);
                                $aMinutes = $durationToMinutes($ar['duration']);
                                $aEnd = $aStart + ($aMinutes * 60);
                                $aEndWithBreak = $aEnd + ($breakMinutes * 60);
                                if ($windowStart < $aEndWithBreak && $windowEnd > $aStart) {
                                    $busyStaff[(int)$ar['staff_id']] = true;
                                }
                            }

                            $totalStaff = $categoryStaffCount[$categoryId];
                            $bookedCount = count($busyStaff);
                            $staffText = $bookedCount . " / " . $totalStaff . " booked";
                            if ($totalStaff > 0 && $bookedCount >= $totalStaff) {
                                $availability = "<span class='badge bg-danger'>FULL</span>";
                            } else {
                                $availability = "<span class='badge bg-success'>Available</span>";
                            }
                            ?>

                            <?php $isPastRow = strtotime($row['appointment_date']) < strtotime(date('Y-m-d')); ?>
                            <tr class="<?= $isPastRow ? 'row-muted' : '' ?>">
                                <td><?= $row['appointment_id'] ?></td>
                                <td><?= htmlspecialchars($row['user_name']) ?></td>
                                <td><?= htmlspecialchars($row['user_phone']) ?></td>
                                <td><?= htmlspecialchars($row['service_name']) ?></td>
                                <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                <td><?= date('H:i', $startTime) ?></td>
                                <td><?= date('H:i', $endTime) ?></td>
                                <td><?= htmlspecialchars($row['request']) ?></td>

                                <td>
                                    <span class="badge
                    <?= $row['appointment_status'] === 'approved' ? 'bg-success'
                                : ($row['appointment_status'] === 'cancelled' ? 'bg-danger'
                                    : ($row['appointment_status'] === 'completed' ? 'bg-secondary'
                                        : 'bg-warning')) ?>">
                                        <?= ucfirst($row['appointment_status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <div><?= htmlspecialchars($row['staff_name'] ?? 'Unassigned') ?></div>
                                    <small class="text-muted"><?= $staffText ?></small>
                                </td>
                                <td><?= $availability ?></td>

                                <td class="text-center">
                                    <?php if ($isPastRow): ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="Past appointment is locked">
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="appointment_edit.php?id=<?= $row['appointment_id'] ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted">
                                No appointments found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>