<div class="card shadow-sm">
    <div class="card-body py-2">
        <h6 class="mb-2"><?= $safeMode === 'monthly' ? 'Monthly Payments' : 'Daily Payments' ?></h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle compact-table">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Staff</th>
                        <th>Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($paymentRows)): ?>
                        <?php foreach ($paymentRows as $row): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['appointment_date'] ?? $row['paid_at'])) ?></td>
                                <td><?= date('h:i A', strtotime($row['appointment_time'] ?? $row['paid_at'])) ?></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td><?= htmlspecialchars($row['services']) ?></td>
                                <td><?= htmlspecialchars($row['staff_names'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                                <td><?= number_format((float)$row['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No payments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
