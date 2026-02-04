<?php
require_once("../public/dp.php");
require_once("adminheader.php");

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capacity'])) {
    $updated = 0;
    foreach ($_POST['capacity'] as $catId => $capVal) {
        $catId = (int)$catId;
        $capVal = trim($capVal);
        if ($catId <= 0) {
            continue;
        }

        if ($capVal === '') {
            $ok = $mysqli->query("UPDATE categories SET capacity = NULL WHERE id = $catId");
        } else {
            $capInt = max(0, (int)$capVal);
            $ok = $mysqli->query("UPDATE categories SET capacity = $capInt WHERE id = $catId");
        }

        if ($ok) {
            $updated++;
        } else {
            $error_msg = "Failed to update some categories. Please try again.";
        }
    }

    if (!$error_msg) {
        $success_msg = "Updated $updated categories.";
    }
}

$categories = $mysqli->query("
    SELECT id, name, incentive_percent, capacity
    FROM categories
    ORDER BY name ASC
");
?>

<div class="content">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">Category Capacity</h3>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <form method="post">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Incentive %</th>
                                    <th style="width: 160px;">Capacity (Seats)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($categories && $categories->num_rows > 0): ?>
                                    <?php while ($cat = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cat['name']) ?></td>
                                            <td><?= htmlspecialchars($cat['incentive_percent']) ?></td>
                                            <td>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    name="capacity[<?= (int)$cat['id'] ?>]"
                                                    class="form-control"
                                                    value="<?= htmlspecialchars($cat['capacity'] ?? '') ?>"
                                                    placeholder="Auto">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No categories found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <button class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>