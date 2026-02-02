<?php
session_start();
require_once("../public/dp.php");
require_once("../include/header.php");
require_once("../public/common_query.php");

// Validate service ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: services.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch service info
$res = $mysqli->query("
    SELECT s.*, c.name AS category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = $id AND s.status = 1
");

if (!$res || $res->num_rows === 0) {
    echo "<h3 class='text-center mt-5'>Service not found</h3>";
    exit;
}

$service = $res->fetch_assoc();

// Fetch related services only active ones
$related_res = $mysqli->query("
    SELECT *
    FROM services
    WHERE category_id = {$service['category_id']}
      AND id != {$service['id']}
      AND status = 1
    LIMIT 4
");

$related_services = [];
while ($r = $related_res->fetch_assoc()) {
    $related_services[] = $r;
}
?>

<!-- ===== SERVICE DETAILS ===== -->
<div class="container my-5">
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

<style>
    /* Image Zoom Effect */
    .img-zoom {
        transition: transform 0.3s ease;
    }

    .img-zoom:hover {
        transform: scale(1.1);
    }

    .nav-link {
        color: black
    }

    .nav-tabs .nav-link {
        color: #ff7e5f;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        margin-right: 5px;
        border-radius: 10px 10px 0 0;
        padding: 10px 20px;
    }

    .nav-tabs .nav-link:hover {
        background: linear-gradient(90deg, #ff7e5f, #feb47b);
        color: #010000;
    }

    .nav-tabs .nav-link.active {
        background: linear-gradient(90deg, #ff7e5f, #feb47b);
        color: #010000;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    /* Tab Content Background & Shadow */
    .tab-content {
        background: #fff5f0;
        /* soft peach color */
        border-radius: 0 0 10px 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    /* Book Button Gradient */
    .btn-book {
        background: linear-gradient(90deg, #ff7e5f, #feb47b);
        color: #fff;
        font-weight: 600;
        padding: 10px 25px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Feature Icons */
    .feature i {
        color: #ff7e5f;
    }

    /* Card hover effect */
    .card:hover {
        transform: translateY(-3px);
        transition: transform 0.2s;
    }
</style>
<?php require_once '../public/alert.php'; ?>