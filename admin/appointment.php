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
    TIME_TO_SEC(s.duration) AS duration_sec,
    aps.status AS service_status
FROM appointments a
JOIN users u ON u.id = a.user_id
JOIN appointment_services aps ON aps.appointment_id = a.id
JOIN services s ON s.id = aps.service_id
WHERE a.appointment_date = '$selectedDate'
" . ($statusSafe ? " AND a.status = '$statusSafe'" : "") . "
 ORDER BY 
    FIELD(aps.status, 'pending', 'confirmed', 'completed', 'cancelled'),
    a.appointment_time ASC

    ";


$result = $mysqli->query($sql);

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
                foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s) {
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
                        <?php while ($row = $result->fetch_assoc()): ?>

                            <?php
                            // Start time per appointment
                            $startTime = strtotime($row['appointment_time']);
                            $endTime   = strtotime("+{$row['duration_sec']} seconds", $startTime);

                            // Staff & availability (example)
                            if (in_array($row['service_status'], ['pending', 'confirmed'])) {
                                $staffText = "1 / 1 booked";
                                $availability = "<span class='badge bg-danger'>FULL</span>";
                            } else {
                                $staffText = "0 / 0 booked";
                                $availability = "<span class='badge bg-success'>Available</span>";
                            }
                            ?>

                            <tr>
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
                    <?= $row['service_status'] === 'confirmed' ? 'bg-success'
                                : ($row['service_status'] === 'cancelled' ? 'bg-danger'
                                    : ($row['service_status'] === 'completed' ? 'bg-secondary'
                                        : 'bg-warning')) ?>">
                                        <?= ucfirst($row['service_status']) ?>
                                    </span>
                                </td>

                                <td><small class="text-muted"><?= $staffText ?></small></td>
                                <td><?= $availability ?></td>

                                <td class="text-center">
                                    <a href="appointment_edit.php?id=<?= $row['appointment_id'] ?>"
                                        class="btn btn-sm btn-primary">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
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