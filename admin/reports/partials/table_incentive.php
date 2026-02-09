<div class="card shadow-sm">
    <div class="card-body py-2">
        <h6 class="mb-2">
            <?php if ($safeMode === 'daily'): ?>
                Daily Completed Services
            <?php elseif ($safeMode === 'monthly'): ?>
                Monthly Completed Services
            <?php else: ?>
                Monthly Summary by Staff
            <?php endif; ?>
        </h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle compact-table">
                <thead class="table-light">
                    <tr>
                        <?php if ($safeMode === 'daily'): ?><th>Date</th><?php endif; ?>
                        <?php if ($safeMode === 'monthly_staff'): ?>
                            <th>Staff</th>
                            <th>Total Services</th>
                            <th>Total Incentive</th>
                        <?php else: ?>
                            <th>Staff</th>
                            <th>Service</th>
                            <th>Count</th>
                            <th>Price</th>
                            <th>Incentive %</th>
                            <th>Total Incentive</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($safeMode === 'daily') {
                        $rows = $dailyRows;
                    } elseif ($safeMode === 'monthly') {
                        $rows = $monthlyRows;
                    } else {
                        $rows = $monthlyStaffRows;
                    }
                    ?>
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php if ($safeMode === 'daily'): ?>
                                    <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                <?php endif; ?>
                                <?php if ($safeMode === 'monthly_staff'): ?>
                                    <td><?= htmlspecialchars($row['staff_name']) ?></td>
                                    <td><?= (int)$row['service_count'] ?></td>
                                    <td><?= number_format((float)$row['incentive_total'], 2) ?></td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($row['staff_name']) ?></td>
                                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                                    <td><?= (int)$row['service_count'] ?></td>
                                    <td><?= number_format((float)$row['price'], 2) ?></td>
                                    <td><?= number_format((float)$row['incentive_percent'], 2) ?>%</td>
                                    <td><?= number_format((float)$row['incentive_total'], 2) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $safeMode === 'daily' ? 7 : ($safeMode === 'monthly_staff' ? 3 : 6) ?>" class="text-center text-muted">No completed services found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
