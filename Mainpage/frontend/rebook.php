<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 p-4">
                <h4 class="text-center mb-4 fw-bold text-primary"><?= htmlspecialchars($service['name']) ?></h4>

                <form method="post" class="row g-3">

                    <div class="col-12">
                        <label class="form-label fw-semibold">Select Date</label>
                        <input type="date" name="date" class="form-control form-control-lg"
                            min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($date) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Select Time</label>
                        <select name="time" class="form-control form-control-lg" required>
                            <option value="">Choose time</option>
                            <?php foreach ($timeSlots as $ts): ?>
                                <option value="<?= $ts ?>" <?= $time === $ts ? 'selected' : '' ?>>
                                    <?= $ts ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Special Request</label>
                        <textarea name="note" class="form-control" placeholder="Any special request"><?= htmlspecialchars($note) ?></textarea>
                    </div>

                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5" <?= $isBlocked ? 'disabled' : '' ?>>Confirm Re-Book</button>
                        <a href="history.php" class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
                    </div>

                </form>


            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../../public/alert.php');
