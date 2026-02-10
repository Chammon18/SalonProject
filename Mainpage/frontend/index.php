<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<section class="hero-min">
        <div class="hero-lux-overlay"></div>
        <div class="container hero-min-grid">
            <div class="hero-min-copy">
                <div class="lux-eyebrow">Angel's Palace</div>
                <h1>Luxury nail & beauty studio with a calm, flawless finish.</h1>
                <p>Premium care, refined detail, and a serene experience designed just for you.</p>
                <div class="hero-min-actions">
                    <a href="../Mainpage/book.php" class="btn btn-primary book-btn">Book Appointment</a>
                    <a href="services.php" class="btn btn-ghost">View Services</a>
                </div>
                <div class="lux-micro">
                    <span>Open 9:00 AM - 8:00 PM</span>
                    <span>Yankin Township</span>
                </div>
            </div>
            <div class="hero-min-media">
                <div class="hero-slider">
                    <img src="../image/design.jpg" alt="Nail Design" class="hero-slide slide-1">
                    <img src="../image/lash.jpg" alt="Lash Service" class="hero-slide slide-2">
                    <img src="../image/waxacces.jpg" alt="Waxing Service" class="hero-slide slide-3">
                </div>
            </div>
        </div>
    </section>

    <section class="services-new">
        <div class="container mt-3">
            <div class="row g-4">
                <div class="col-md-3">
                    <a href="services.php?category=nail" class="text-decoration-none">
                        <div class="service-card modern-card">
                            <div class="icon-wrap coral">
                                <i class="fas fa-hand-sparkles"></i>
                            </div>
                            <h5>Nail Care</h5>
                            <p>Manicures, gel nails, and more for perfect hands.</p>
                            <span class="card-link">View services</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="services.php?category=eyelash" class="text-decoration-none">
                        <div class="service-card modern-card">
                            <div class="icon-wrap pink">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h5>Eyelash Extensions</h5>
                            <p>Enhance your eyes with natural or dramatic lashes.</p>
                            <span class="card-link">View services</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="services.php?category=hair" class="text-decoration-none">
                        <div class="service-card modern-card">
                            <div class="icon-wrap blue">
                                <i class="fa-solid fa-shower"></i>
                            </div>
                            <h5>Hair</h5>
                            <p>Professional hair styling and color services.</p>
                            <span class="card-link">View services</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="services.php?category=waxing" class="text-decoration-none">
                        <div class="service-card modern-card">
                            <div class="icon-wrap green">
                                <i class="fas fa-spa"></i>
                            </div>
                            <h5>Waxing</h5>
                            <p>Gentle waxing services for smooth skin.</p>
                            <span class="card-link">View services</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($promoList)): ?>
        <section class="promo-mini">
            <div class="container">
                <div class="promo-head">
                    <h3>Current Promotions</h3>
                    <a href="../Mainpage/promotion.php" class="promo-link">View all</a>
                </div>
                <div class="promo-grid">
                    <?php foreach ($promoList as $p): ?>
                        <div class="promo-card">
                            <div class="promo-badge"><?= htmlspecialchars($p['discount']) ?></div>
                            <img src="/SalonProject/uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                            <div class="promo-info">
                                <h5><?= htmlspecialchars($p['title']) ?></h5>
                                <p><?= htmlspecialchars($p['description']) ?></p>
                                <a href="../Mainpage/book.php" class="btn btn-primary btn-sm">Book Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="story-min">
        <div class="container story-min-grid">
            <div class="story-min-media">
                <img src="../image/home page.png" alt="Salon Interior">
            </div>
            <div class="story-min-copy">
                <div class="lux-eyebrow">Our Promise</div>
                <h3>Elegant, hygienic, and meticulously detailed.</h3>
                <p>We deliver a luxury salon experience with high standards of hygiene and attentive care. From the moment you arrive, our team focuses on clean tools, calm surroundings, and precision techniques that protect the health of your nails while enhancing their beauty. Every service is crafted to feel unhurried and personal, whether you choose a simple refresh or a full design set. Our goal is to make each visit relaxing, consistent, and worthy of your trust.</p>
                <a href="about.php" class="btn btn-ghost">Learn more</a>
            </div>
        </div>
    </section>

    <section class="cta-min">
        <div class="container cta-min-row">
            <div>
                <h4>Ready for your next appointment?</h4>
                <p>Reserve your time and enjoy a luxury beauty experience.</p>
            </div>
            <a href="../Mainpage/book.php" class="btn btn-primary book-btn">Book Now</a>
        </div>
    </section>

    <section class="micro-contact">
        <div class="container micro-contact-row">
            <div>+95 9 23950437504</div>
            <div>34, Room 2, Shwe Ohn Pin Housing, Yankin Township</div>
            <div>Open: 9:00 AM - 8:00 PM</div>
        </div>
    </section>


    <?php
    require_once(__DIR__ . '/../../public/alert.php');
    require_once(__DIR__ . '/../../include/footer.php');
    ?>