<div class="card shadow-sm">
    <div class="card-body py-2">
        <h6 class="mb-2">Cancelled Appointments</h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle compact-table">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Staff</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cancelRows)): ?>
                        <?php foreach ($cancelRows as $row): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['appointment_date'])) ?></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td><?= htmlspecialchars($row['service_name']) ?></td>
                                <td><?= htmlspecialchars($row['staff_name'] ?? '-') ?></td>
                                <td><?= (int)$row['cancel_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No cancelled appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
