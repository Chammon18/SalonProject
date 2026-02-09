<?php require_once("adminheader.php"); ?>


<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="newservices.php" class="btn btn-primary">Add New Service</a>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
    </div>

    <!-- FILTERS -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php while ($cat = $catRes->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($cat['name']) ?>"
                        <?= $categoryFilter === $cat['name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>

        <div class="col-md-3">
            <button class="btn btn-success w-100">
                <i class="fa fa-filter"></i> Filter
            </button>
        </div>
    </form>

    <!-- SERVICES TABLE -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="60">ID</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th width="80">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res->num_rows > 0): ?>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr class="<?= !$row['status'] ? 'inactive-row' : '' ?>">
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['category_name']) ?></td>
                                <td><?= htmlspecialchars($row['service_name']) ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($row['image']) ?>"
                                            style="width:100px;border-radius:6px;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= $row['price'] ?></td>
                                <td><?= $row['duration'] ?></td>
                                <td>
                                    <span class="status-badge <?= $row['status'] ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $row['status'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="serviceupdate.php?id=<?= $row['id'] ?>" class="text-primary">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No services found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- PAGINATION UI (ADDED)-->
            <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center mt-3">

                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?page=<?= $page - 1 ?>&category=<?= urlencode($categoryFilter) ?>&status=<?= urlencode($statusFilter) ?>">
                                Prev
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $i ?>&category=<?= urlencode($categoryFilter) ?>&status=<?= urlencode($statusFilter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?page=<?= $page + 1 ?>&category=<?= urlencode($categoryFilter) ?>&status=<?= urlencode($statusFilter) ?>">
                                Next
                            </a>
                        </li>

                    </ul>
                </nav>
            <?php endif; ?>


        </div>
    </div>
</div>

<?php require_once('adminfooter.php'); ?>


