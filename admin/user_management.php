<?php
require_once "auth_check.php";
require_once "../public/dp.php";
require_once "adminheader.php";

// Tab
$tab = $_GET['tab'] ?? 'user';

// Filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// 
// PAGINATION (ADDED)
// 
$limit = 5; // users per page
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max(1, $page);
$offset = ($page - 1) * $limit;
// 

// Build WHERE clause
$where = [];
$where[] = $tab === 'user' ? "role='user'" : "role='admin'";

// Search by name or email
if ($search) {
    $safeSearch = mysqli_real_escape_string($mysqli, $search);
    $where[] = "(name LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%')";
}

// Filter by status
if ($statusFilter === 'active') {
    $where[] = "status = 1";
} elseif ($statusFilter === 'inactive') {
    $where[] = "status = 0";
}

$whereSql = "WHERE " . implode(" AND ", $where);

// 
// COUNT QUERY (ADDED)
// 
$countSql = "SELECT COUNT(*) AS total FROM users $whereSql";
$countRes = mysqli_query($mysqli, $countSql);
$countRow = mysqli_fetch_assoc($countRes);
$totalUsers = $countRow['total'];
$totalPages = ceil($totalUsers / $limit);
// 

// Fetch users (ACTIVE FIRST, INACTIVE LAST)
$result = mysqli_query(
    $mysqli,
    "SELECT * FROM users
     $whereSql
     ORDER BY status DESC, id ASC
     LIMIT $limit OFFSET $offset"
);
?>

<div class="content">
    <div class="d-flex text-white justify-content-end mb-3">
        <a href="admin_create.php" class="btn btn-dark">
            <i class="fa fa-plus"></i> Create Admin
        </a>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link user-tab <?= $tab === 'user' ? 'active' : '' ?>"
                href="?tab=user">
                <i class="fa fa-users me-2"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link admin-tab <?= $tab === 'admin' ? 'active' : '' ?>"
                href="?tab=admin">
                <i class="fa fa-user-shield me-2"></i> Admin Users
            </a>
        </li>
    </ul>


    <!-- Filters -->
    <form class="row g-2 mb-3" method="GET">
        <input type="hidden" name="tab" value="<?= $tab ?>">
        <div class="col-md-6">
            <input type="text" class="form-control" name="search"
                placeholder="Search by name or email"
                value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status">
                <option value="">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-success w-100">
                <i class="fa fa-search"></i> Filter
            </button>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="<?= !$row['status'] ? 'inactive-row' : '' ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                            <td>
                                <span class="status-badge <?= $row['status'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $row['status'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="useredit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fa fa-pen"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            No <?= $tab === 'admin' ? 'admins' : 'users' ?> found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 
             PAGINATION UI (ADDED)
              -->
        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center mt-3">

                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?tab=<?= $tab ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $page - 1 ?>">
                            Prev
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link"
                                href="?tab=<?= $tab ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                            href="?tab=<?= $tab ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $page + 1 ?>">
                            Next
                        </a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>


    </div>
</div>
</div>

</body>

</html>