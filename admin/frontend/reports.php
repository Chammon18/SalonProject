<?php require_once("adminheader.php"); ?>


<div class="content">
    <div class="container report-compact">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h4 class="mb-0">Reports</h4>
            <?php if (!$isPrint): ?>
                <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($printUrl) ?>" target="_blank">Print</a>
            <?php endif; ?>
        </div>

        <?php require __DIR__ . '/../reports/partials/nav.php'; ?>

        <?php if ($safeTab === 'incentive'): ?>
            <?php require __DIR__ . '/../reports/partials/filters_incentive.php'; ?>
            <?php require __DIR__ . '/../reports/partials/table_incentive.php'; ?>
        <?php endif; ?>

        <?php if ($safeTab === 'cancelled'): ?>
            <?php require __DIR__ . '/../reports/partials/filters_cancelled.php'; ?>
            <?php require __DIR__ . '/../reports/partials/table_cancelled.php'; ?>
        <?php endif; ?>

        <?php if ($safeTab === 'revenue'): ?>
            <?php require __DIR__ . '/../reports/partials/filters_revenue.php'; ?>
            <?php require __DIR__ . '/../reports/partials/table_revenue.php'; ?>
        <?php endif; ?>

        <?php if ($safeTab === 'payments'): ?>
            <?php require __DIR__ . '/../reports/partials/filters_payments.php'; ?>
            <?php require __DIR__ . '/../reports/partials/table_payments.php'; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($isPrint): ?>
    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
<?php endif; ?>
<?php require_once('adminfooter.php'); ?>

