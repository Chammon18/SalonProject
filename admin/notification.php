<?php
require_once "../public/dp.php";
require_once "adminheader.php";

// Filters
$filterDate   = $_GET['date'] ?? '';
$filterStatus = 'pending'; // only show pending bookings by default
$search       = $_GET['search'] ?? ''; // define search variable

// Pagination
$limit  = 5;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page   = max(1, $page);
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = ["a.status = 'pending'"]; // default to pending only

if ($filterDate) {
    $safeDate = mysqli_real_escape_string($mysqli, $filterDate);
    $where[] = "DATE(a.appointment_date) = '$safeDate'";
}

if ($search) {
    $safeSearch = mysqli_real_escape_string($mysqli, $search);
    $where[] = "(u.name LIKE '%$safeSearch%' OR u.email LIKE '%$safeSearch%')";
}

$whereSql = "WHERE " . implode(" AND ", $where);

// Count total notifications
$countRes = $mysqli->query("
    SELECT COUNT(*) AS total
    FROM notifications n
    JOIN appointments a ON n.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    $whereSql
");
$totalNotifications = $countRes->fetch_assoc()['total'];
$totalPages = ceil($totalNotifications / $limit);

// Fetch notifications
$notifQuery = $mysqli->query("
    SELECT
        n.id AS notif_id,
        n.message,
        n.created_at,
        n.is_read,
        a.id AS appointment_id,
        a.appointment_date,
        a.status,
        u.name,
        u.email
    FROM notifications n
    JOIN appointments a ON n.appointment_id = a.id
    JOIN users u ON a.user_id = u.id
    $whereSql
    ORDER BY n.created_at DESC
    LIMIT $limit OFFSET $offset
");
?>

<div class="content">
    <!-- Filter + Select All Row -->
    <form method="GET" class="row g-2 mb-3 align-items-center">
        <!-- Date Filter -->
        <div class="col-md-3">
            <input type="text" class="form-control" name="search"
                placeholder="Search by name or email"
                value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
        </div>

        <!-- Filter Button -->
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">
                <i class="fa fa-search"></i> Filter
            </button>
        </div>

        <!-- Select All + Mark as Read (POST Form) -->
        <div class="col-md-4">
            <form method="POST" action="notification_markall.php" class="d-flex align-items-center gap-2">
                <input type="checkbox" id="selectAll">
                <label for="selectAll" class="mb-0">Select All</label>
                <button type="submit" class="btn btn-sm btn-success">Mark Selected as Read</button>
                <?php // closing tag for POST form comes after the notifications list 
                ?>
            </form>
        </div>
    </form>

    <!-- Notification List -->
    <?php if ($notifQuery && $notifQuery->num_rows > 0): ?>
        <?php while ($notif = $notifQuery->fetch_assoc()): ?>
            <div class="alert alert-info py-2 mb-2 <?= $notif['is_read'] ? 'opacity-75' : '' ?> d-flex align-items-start">
                <input type="checkbox" name="notif_ids[]" value="<?= $notif['notif_id'] ?>" class="notif-checkbox me-2">
                <div>
                    <a href="appointment.php?id=<?= $notif['appointment_id'] ?>&date=<?= $notif['appointment_date'] ?>" class="text-decoration-none">
                        <?= htmlspecialchars($notif['message']) ?><br>
                        <small class="text-muted">📅 <?= date("d M Y", strtotime($notif['appointment_date'])) ?></small><br>
                        <small class="text-muted">⏰ <?= date("d M Y H:i", strtotime($notif['created_at'])) ?></small><br>
                        <small class="text-muted">Status: <?= ucfirst($notif['status']) ?></small>
                        <?php if (!$notif['is_read']): ?>
                            <span class="badge bg-danger ms-2">NEW</span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-secondary text-center">No notifications found.</div>
    <?php endif; ?>
    </form> <!-- closes POST form -->

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center mt-3">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&date=<?= urlencode($filterDate) ?>">Prev</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&date=<?= urlencode($filterDate) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&date=<?= urlencode($filterDate) ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.notif-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });
</script>
</body>

</html>