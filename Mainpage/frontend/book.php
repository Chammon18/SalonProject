<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<div class="container my-3">
    <div class="card p-4 shadow">
        <h4 class="text-center text-success mb-3">Book Your Visit </h4>

        <form method="post">
            <div class="row g-2">
                <div class="col-md-6">
                    <input class="form-control" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                </div>
                <div class="col-md-6">
                    <input class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" readonly>
                </div>
            </div>

            <div class="row g-2 mt-2">
                <div class="col-md-6">
                    <input type="date" name="date" class="form-control"
                        min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date) ?>" required>
                </div>
                <div class="col-md-6">
                    <select name="time" class="form-control" required>
                        <option value="">Choose time</option>
                        <?php foreach ($timeSlots as $ts): ?>
                            <option value="<?= $ts ?>" <?= $time === $ts ? 'selected' : '' ?>>
                                <?= $ts ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <?php
                    $catId = (int)$cat['id'];
                    $cap = isset($cat['capacity']) ? (int)$cat['capacity'] : 0;
                    if ($cap <= 0) {
                        $cap = $categoryStaffCounts[$catId] ?? 0;
                    }
                    $busyNow = $seatStatus[$catId] ?? 0;
                    $remaining = $cap > 0 ? max(0, $cap - $busyNow) : 0;
                    ?>
                    <div class="col-md-6">
                        <div class="border rounded p-2 h-100">
                            <b><?= htmlspecialchars($cat['name']) ?></b>
                            <div class="mt-2">
                                <?php
                                $srv = $mysqli->query("
                                    SELECT id,name FROM services
                                    WHERE category_id={$cat['id']} AND status=1
                                ");
                                while ($s = $srv->fetch_assoc()):
                                ?>
                                    <label class="d-block">
                                        <input type="checkbox" name="services[]" value="<?= $s['id'] ?>"
                                            <?= ($prefillService && $prefillService['id'] == $s['id']) ? 'checked' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?>
                                    </label>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <textarea name="note" class="form-control mt-3"
                placeholder="Special request"><?= htmlspecialchars($note) ?></textarea>

            <button class="btn btn-success w-100 mt-3" <?= $isBlocked ? 'disabled' : '' ?>>Confirm Booking</button>
        </form>
    </div>
</div>

<?php
require_once(__DIR__ . '/../../public/alert.php');
require_once(__DIR__ . '/../../include/footer.php');
?>
