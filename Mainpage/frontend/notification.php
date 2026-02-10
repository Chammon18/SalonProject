<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">
            <i class="fa fa-bell me-2 text-warning"></i>
            Notifications
        </h4>
    </div>

    <?php if ($all_notif_res->num_rows > 0): ?>
        <?php while ($n = $all_notif_res->fetch_assoc()): ?>
            <div class="card notification-card mb-3 <?= $n['is_read'] == 0 ? 'unread' : '' ?>">
                <div class="card-body d-flex gap-3">
                    <div class="icon-box">
                        <i class="fa fa-calendar-check"></i>
                    </div>

                    <div class="flex-grow-1">
                        <p class="mb-1 fw-semibold">
                            <?= htmlspecialchars($n['message']) ?>
                        </p>
                        <small class="text-muted">
                            <?= date('d M Y · h:i A', strtotime($n['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="fa fa-bell-slash fa-2x mb-2"></i>
            <p>No notifications yet</p>
        </div>
    <?php endif; ?>
</div>