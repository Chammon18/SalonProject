<?php if (!$isPrint): ?>
    <div class="card shadow-sm mb-2">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end compact-form">
                <input type="hidden" name="tab" value="incentive">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="mode" class="form-select">
                        <option value="daily" <?= $safeMode === 'daily' ? 'selected' : '' ?>>Daily</option>
                        <option value="monthly" <?= $safeMode === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        <option value="monthly_staff" <?= $safeMode === 'monthly_staff' ? 'selected' : '' ?>>Monthly (By Staff)</option>
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
                    <label class="form-label">Staff</label>
                    <select name="staff_id" class="form-select">
                        <option value="">All Staff</option>
                        <?php foreach ($staffList as $st): ?>
                            <option value="<?= (int)$st['id'] ?>" <?= $staffId === (int)$st['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>">
                </div>
                <div class="col-12 text-end mt-2">
                    <button class="btn btn-success btn-sm">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>