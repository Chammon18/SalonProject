<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<!-- ===== SERVICE DETAILS ===== -->
<div class="container my-5 viewdetails-page">
    <div class="row g-5 align-items-center">

        <!-- IMAGE -->
        <div class="col-md-6">
            <div class="service-img-box position-relative overflow-hidden rounded shadow-sm">
                <img src="../admin/uploads/<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="img-fluid img-zoom w-100">
            </div>
        </div>

        <!-- DETAILS -->
        <div class="col-md-6">
            <div class="service-info">
                <h1 class="service-name mb-3">
                    <?= htmlspecialchars($service['name']) ?>
                </h1>

                <!-- FEATURES -->
                <div class="d-flex gap-3 mb-3 flex-wrap">
                    <div class="feature d-flex align-items-center gap-1">
                        <i class="bi bi-clock"></i> <?= substr($service['duration'], 0, 5) ?> hrs
                    </div>
                    <div class="feature d-flex align-items-center gap-1">
                        <i class="bi bi-currency-exchange"></i> Kyats <?= number_format($service['price']) ?>
                    </div>
                    <div class="feature d-flex align-items-center gap-1">
                        <i class="bi bi-tags"></i> <?= ucfirst($service['category_name']) ?>
                    </div>
                </div>

                <!-- DESCRIPTION TABS -->
                <ul class="nav nav-tabs mt-4" id="serviceTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#faq">FAQ</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews">Reviews</button>
                    </li>
                </ul>
                <div class="tab-content p-3 border border-top-0 rounded-bottom shadow-sm">
                    <div class="tab-pane fade show active" id="desc">
                        <p><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                    </div>
                    <div class="tab-pane fade" id="faq">
                        <p>Booking & Policies:How do I book an appointment? Bookings can be made online via our website or by calling our front desk.</p>
                    </div>
                    <div class="tab-pane fade" id="reviews">
                        <p>. Always include what made the experience special, such as friendliness or expertise. </p>
                    </div>
                </div>

                <!-- BOOK BUTTON -->
                <a href="book.php?id=<?= $service['id'] ?>" class="btn btn-book book-btn mt-4">
                    Book Appointment
                </a>

            </div>
        </div>
    </div>

    <!-- RELATED SERVICES -->
    <?php if (count($related_services) > 0): ?>
        <h3 class="mt-5 mb-3">Related Services</h3>
        <div class="row g-4">
            <?php foreach ($related_services as $rel): ?>
                <div class="col-md-3">
                    <div class="card h-100 shadow-sm related-card"
                        data-id="<?= $rel['id'] ?>"
                        style="cursor:pointer;">
                        <img src="../admin/uploads/<?= htmlspecialchars($rel['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($rel['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($rel['name']) ?></h5>
                            <p class="card-text">Kyats <?= number_format($rel['price']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once(__DIR__ . '/../../public/alert.php'); ?>
