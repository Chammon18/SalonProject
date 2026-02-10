<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg rounded-4 p-4 text-center">
                <div class="mb-3">
                    <span style="font-size:50px;">✁E/span>
                </div>
                <h3 class="text-success mb-4">Booking Details</h3>

                <!-- Services -->
                <?php foreach ($services_by_category as $category => $services): ?>
                    <div class="text-start mb-2">
                        <strong><?= htmlspecialchars($category) ?>:</strong>
                        <?= implode(', ', array_map('htmlspecialchars', $services)) ?>
                    </div>
                <?php endforeach; ?>

                <!-- Optional: show each service details -->
                <?php foreach ($service_details as $srv): ?>
                    <div class="text-start mb-2 bg-light rounded p-2">
                        <strong><?= htmlspecialchars($srv['service_name']) ?></strong><br>
                        Price: Kyats <?= number_format($srv['price']) ?> <br> Duration: <?= htmlspecialchars($srv['duration']) ?>
                    </div>
                <?php endforeach; ?>

                <!-- Date & Time -->
                <div class="text-start mb-2">
                    <strong>Date & Time:</strong>
                    <?= date('d M Y', strtotime($appointment['appointment_date'])) ?>
                    , <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                </div>

                <!-- Status -->
                <div class="text-start mb-2">
                    <strong></strong>
                    <span class="badge bg-warning text-dark"><?= ucfirst($appointment['status']) ?></span>
                </div>

                <!-- Customer Info -->
                <div class="bg-light rounded p-3 text-start mb-2">
                    <strong>Customer:</strong> <?= htmlspecialchars($user['name']) ?><br>
                    <strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?>
                </div>

                <!-- Special Request -->
                <?php if (!empty($appointment['request'])): ?>
                    <div class="bg-warning bg-opacity-10 rounded p-3 mb-3 text-start">
                        <strong>Special Request:</strong><br>
                        <?= nl2br(htmlspecialchars($appointment['request'])) ?>
                    </div>
                <?php endif; ?>

                <a href="index.php" class="btn btn-success w-100 py-2 fw-semibold">Back to Home Page</a>
            </div>
        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/../../include/footer.php'); ?>
