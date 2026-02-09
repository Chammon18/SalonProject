<?php require_once("adminheader.php"); ?>


<div class="content">
    <div class="card shadow-sm">
        <div class="card-body">

            <h5 class="mb-4">Edit Appointment</h5>

            <div class="mb-2"><strong>User:</strong>
                <?= htmlspecialchars($appointment['user_name']) ?>
            </div>

            <div class="mb-2"><strong>Date:</strong>
                <?= date('d M Y', strtotime($appointment['appointment_date'])) ?>
            </div>

            <div class="mb-4"><strong>Time:</strong>
                <?= date('H:i', strtotime($appointment['appointment_time'])) ?>
            </div>

            <?php if ($isPast): ?>
                <div class="alert alert-secondary">
                    This appointment is in the past. Editing is disabled to protect records.
                </div>
            <?php endif; ?>
            <?php if (($_GET['error'] ?? '') === 'payment_required'): ?>
                <div class="alert alert-warning">
                    Payment method is required to complete this appointment. Please select a payment method.
                </div>
            <?php endif; ?>

            <form method="post">
                <?php
                $services->data_seek(0); // reset pointer
                while ($s = $services->fetch_assoc()):
                    $categoryId = (int)$s['category_id'];
                    $staffList = $mysqli->query("
                        SELECT st.id, st.name
                        FROM staff st
                        JOIN staff_categories sc ON sc.staff_id = st.id
                        WHERE sc.category_id = $categoryId
                        ORDER BY st.name ASC
                    ");
                ?>
                    <div class="mb-3">
                        <strong><?= htmlspecialchars($s['name']) ?></strong><br>
                        <select name="status[<?= $s['appointment_id'] ?>]" class="form-select" required <?= $isPast ? 'disabled' : '' ?>>
                            <?php foreach (['pending', 'approved', 'completed', 'cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $s['status'] === $st ? 'selected' : '' ?>>
                                    <?= ucfirst($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="staff[<?= $s['appointment_id'] ?>]" class="form-select mt-2" <?= $isPast ? 'disabled' : '' ?>>
                            <option value="0">Unassigned</option>
                            <?php while ($stf = $staffList->fetch_assoc()): ?>
                                <option value="<?= $stf['id'] ?>" <?= ((int)$s['staff_id'] === (int)$stf['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stf['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endwhile; ?>

                <input type="hidden" name="payment_method" id="payment_method" value="">
                <button type="submit" class="btn btn-success" <?= $isPast ? 'disabled' : '' ?>>Update Status</button>
                <a href="appointment.php" class="btn btn-secondary">Back</a>
            </form>

        </div>
    </div>
</div>

<?php
if (!$isPast) {
    require_once __DIR__ . '/../modals/payment_modal.php';
    require_once __DIR__ . '/../modals/payment_modal.js.php';
}
?>
<?php require_once('adminfooter.php'); ?>

