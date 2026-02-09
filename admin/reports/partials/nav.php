<?php if (!$isPrint): ?>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?= $safeTab === 'incentive' ? 'active' : '' ?>" href="?tab=incentive&mode=<?= urlencode($safeMode) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&month=<?= urlencode($month) ?>&staff_id=<?= urlencode((string)$staffId) ?>">Incentive</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $safeTab === 'cancelled' ? 'active' : '' ?>" href="?tab=cancelled&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">Cancelled</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $safeTab === 'revenue' ? 'active' : '' ?>" href="?tab=revenue&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">Revenue</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $safeTab === 'payments' ? 'active' : '' ?>" href="?tab=payments&mode=<?= urlencode($safeMode) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&month=<?= urlencode($month) ?>">Payments</a>
        </li>
    </ul>
<?php endif; ?>
