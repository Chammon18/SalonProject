<?php if (!$isPrint): ?>
    <div class="card shadow-sm mb-2">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end compact-form">
                <input type="hidden" name="tab" value="payments">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="mode" class="form-select">
                        <option value="daily" <?= $safeMode === 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $safeMode === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="">All Methods</option>
                        <?php foreach ($allowedMethods as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= $safePaymentMethod === $m ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end mt-2">
                    <button class="btn btn-success btn-sm">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
