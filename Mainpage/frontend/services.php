<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<!-- BANNER -->
<section class="services-banner text-center">
    <h1 class="display-5 fw-bold">Our Services</h1>
    <p class="lead">Treat Yourself with Our Expert Care</p>
</section>

<!-- CATEGORIES -->
<section class="py-2 text-center category-buttons m-3">
    <a href="services.php?category=all"
        class="category-btn <?= ($cat == 'all') ? 'active' : '' ?>">All</a>

    <?php foreach ($categories as $name => $id): ?>
        <a href="services.php?category=<?= $name ?>"
            class="category-btn <?= ($cat == $name) ? 'active' : '' ?>">
            <?= ucfirst($name) ?>
        </a>
    <?php endforeach; ?>
</section>



<!-- available service -->
<div class="container">
    <div class="row">
        <?php if ($res && $res->num_rows > 0): ?>
            <?php while ($row = $res->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="service-card h-100">
                        <div class="service-img">
                            <img src="../admin/uploads/<?= htmlspecialchars($row['image']) ?>"
                                class="service-detail"
                                data-id="<?= $row['id'] ?>"
                                style="cursor:pointer;">
                        </div>

                        <div class="service-body">
                            <h5><?= htmlspecialchars($row['name']) ?></h5>
                            <p><?= htmlspecialchars($row['description']) ?></p>

                            <div class="d-flex justify-content-between">
                                <span>Kyats <?= number_format($row['price']) ?></span>
                                <span>⏱ <?= substr($row['duration'], 0, 5) ?></span>
                            </div>
                            <a href="viewdetails.php?id=<?= $row['id'] ?>"
                                class="btn btn-success w-100 mt-3 service-detail"
                                data-id="<?= $row['id'] ?>">
                                View Details
                            </a>


                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <h4 class="text-center">No services available</h4>
        <?php endif; ?>
    </div>
</div>


<?php require_once(__DIR__ . '/../../include/footer.php'); ?>