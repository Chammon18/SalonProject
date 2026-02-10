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
                                <span><?= !empty($m['best_service']) ? htmlspecialchars($m['best_service']) : ' E ?></span>
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

<style>
    .team-hero {
        padding: 50px 0 20px;
        background:
            radial-gradient(1000px 300px at 10% -10%, rgba(255, 180, 200, 0.35), transparent 60%),
            radial-gradient(800px 260px at 90% -20%, rgba(160, 230, 210, 0.35), transparent 60%),
            #fff;
    }

    .team-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .team-header h2 {
        font-weight: 800;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
    }

    .badge-pill {
        display: inline-block;
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 6px 12px;
        border-radius: 999px;
        background: #f2f6f4;
        color: #2f6f55;
        margin-bottom: 10px;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
    }

    .team-card {
        background: #ffffff;
        border: 1px solid #eef1f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .team-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px rgba(0, 0, 0, 0.08);
    }

    .team-photo {
        height: 180px;
        overflow: hidden;
        background: #f3f5f4;
    }

    .team-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .team-body {
        padding: 14px 14px 16px;
    }

    .team-name {
        font-weight: 700;
        margin-bottom: 4px;
        font-size: 16px;
    }

    .team-skill,
    .team-best {
        font-size: 13px;
        color: #555;
        margin-bottom: 6px;
    }

    .team-best span {
        color: #2f6f55;
        font-weight: 600;
    }
</style>