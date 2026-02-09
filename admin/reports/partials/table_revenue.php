<div class="card shadow-sm">
    <div class="card-body py-2">
        <h6 class="mb-2"><?= $safeMode === 'monthly' ? 'Monthly Revenue Summary' : 'Daily Revenue Summary' ?></h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle compact-table">
                <thead class="table-light">
                    <tr>
                        <th><?= $safeMode === 'monthly' ? 'Month' : 'Date' ?></th>
                        <th>Services</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($revenueRows)): ?>
                        <?php foreach ($revenueRows as $row): ?>
                            <tr>
                                <?php if ($safeMode === 'monthly'): ?>
                                    <td><?= date('M Y', strtotime($row['month_key'] . '-01')) ?></td>
                                <?php else: ?>
                                    <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                <?php endif; ?>
                                <td><?= (int)$row['service_count'] ?></td>
                                <td><?= number_format((float)$row['revenue_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">No revenue data found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
