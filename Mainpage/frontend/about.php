<?php require_once(__DIR__ . '/../../include/header.php'); ?>

<!-- Team -->
<section class="team-hero">
    <div class="container">
        <div class="team-header">
            <div class="badge-pill">Our Specialists</div>
            <h2>Meet Our Team</h2>
            <p>Professional skills and signature services from our best staff.</p>
        </div>

        <div class="team-grid">
            <?php if (!empty($team)): ?>
                <?php foreach ($team as $i => $m): ?>
                    <?php $img = $imagePool[$i % count($imagePool)]; ?>
                    <div class="team-card">
                        <div class="team-photo">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($m['name']) ?>">
                        </div>
                        <div class="team-body">
                            <div class="team-name"><?= htmlspecialchars($m['name']) ?></div>
                            <div class="team-skill">
                                <?= !empty($m['skills']) ? htmlspecialchars($m['skills']) : 'General' ?>
                            </div>
                            <div class="team-best">
                                Best Service:
                                <span><?= !empty($m['best_service']) ? htmlspecialchars($m['best_service']) : '-' ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No staff found.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About us -->
<section id="contact" class="contact-modern">
    <div class="container">
        <div class="row align-items-center gy-5">

            <!-- LEFT -->
            <div class="col-lg-6 contact-left reveal-left">
                <h2>
                    Let's Make You <span>Glow</span>
                </h2>
                <p>
                    Book your appointment today and experience beauty,
                    care, and confidence at <b>Angel's Palace</b>.
                </p>

                <div class="contact-actions">
                    <a
                        href="https://maps.app.goo.gl/o1J1tAtf6tUD1jV56"
                        target="_blank"
                        class="btn btn-book">
                        View Map
                    </a>

                    <a href="book.php" class="btn btn-book book-btn">Book Online</a>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="col-lg-6 contact-right reveal-right">
                <div class="contact-card">
                    <h4>Visit Us</h4>
                    <p>34, Room 2, Shwe Ohn Pin Housing, Yankin Township</p>
                    <p>+95 9 23950437504</p>
                </div>

                <div class="contact-card">
                    <h4>Open Hours</h4>
                    <p>Tuesday - Sunday<br>09:00 AM - 08:00 PM</p>
                </div>

                <div class="contact-card">
                    <h4>Email</h4>
                    <p>info@angelspalace.com</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- about end -->

<?php
require_once(__DIR__ . '/../../public/alert.php');
require_once(__DIR__ . '/../../include/footer.php'); ?>