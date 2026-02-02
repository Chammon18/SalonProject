<?php
session_start();
require_once("../public/dp.php");
require_once("../public/common_query.php");
require_once("../include/header.php");

?>

<!-- Home Section -->
<section class="hero-section">
    <img src="../image/home page.png" alt="Salon Background" class="bg-img">
    <div class="content">
        <h1>Welcome to Angel's Palace</h1>
        <p>Treat Yourself with Our Expert Care</p>
        <a href="../Mainpage/book.php" class="btn btn-login book-btn">Book Appointment</a>
        <a href="services.php" class="btn btn-home">View Services</a>
    </div>
</section>
<!-- Home End -->


<!-- About us  -->
<section class="about-section py-5" style="background: #fff;">
    <div class="container">

        <!-- Section Title -->
        <div class="text-center mb-5">
            <h2 class="section-title" style="font-weight: 700; font-size: 2.8rem; background:black; -webkit-background-clip: text; color: transparent;">
                Our Services
            </h2>
            <p class="section-subtitle" style="font-size: 1.1rem; color: black;">
                We provide a range of beauty treatments to make you feel pampered and beautiful.
            </p>
        </div>

        <!-- Services Cards -->
        <div class="row g-4">
            <div class="col-md-3">
                <a href="services.php?category=nail" class="text-decoration-none">
                    <div class="about-card shadow-lg p-4 rounded-4 text-center hover-scale" style="transition: 0.3s; background: #fff;">
                        <i class="fas fa-hand-sparkles fa-3x mb-3" style="color: #ff7f50;"></i>
                        <h5 class="mb-2">Nail Care</h5>
                        <p class="mb-0" style="color: #777;">Manicures, gel nails, and more for perfect hands.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="services.php?category=eyelash" class="text-decoration-none">
                    <div class="about-card shadow-lg p-4 rounded-4 text-center hover-scale" style="transition: 0.3s; background: #fff;">
                        <i class="fas fa-eye fa-3x mb-3" style="color: #ff69b4;"></i>
                        <h5 class="mb-2">Eyelash Extensions</h5>
                        <p class="mb-0" style="color: #777;">Enhance your eyes with natural or dramatic lashes.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="services.php?category=hair" class="text-decoration-none">
                    <div class="about-card shadow-lg p-4 rounded-4 text-center hover-scale" style="transition: 0.3s; background: #fff;">
                        <i class="fa-solid fa-shower fa-3x mb-3" style="color: #1e90ff;"></i>
                        <h5 class="mb-2">Hair</h5>
                        <p class="mb-0" style="color: #777;">Professional hair styling and color services.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="services.php?category=waxing" class="text-decoration-none">
                    <div class="about-card shadow-lg p-4 rounded-4 text-center hover-scale" style="transition: 0.3s; background: #fff;">
                        <i class="fas fa-spa fa-3x mb-3" style="color: #32cd32;"></i>
                        <h5 class="mb-2">Waxing</h5>
                        <p class="mb-0" style="color: #777;">Gentle waxing services for smooth skin.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Our Story -->
        <div class="story-section row align-items-center mt-5">
            <div class="col-md-6 mb-4 mb-md-0 reveal-left">
                <div class="story-img-wrapper" style="overflow: hidden; border-radius: 20px;">
                    <img src="../image/close-up-nail-care-treatment.jpg" alt="Salon Interior" class="story-img w-100" style="transition: transform 0.5s;">
                </div>
            </div>

            <div class="col-md-6 story-text reveal-right" style="padding-left: 30px;">
                <h3 style="font-weight: 700; font-size: 2rem; color: #333;">Our Story</h3>
                <p style="color: #555;">Welcome to Angel's Palace, where beauty and relaxation come together.</p>
                <p style="color: #555;">Founded over a decade ago, our salon has been dedicated to providing top-notch services in a luxurious and welcoming environment.</p>
                <p style="color: #555;">Our mission is to make every client feel special and rejuvenated. <em style="color: #ff7f50;">Come experience the difference with us!</em></p>
            </div>
        </div>

    </div>
</section>
<!-- about end -->


<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">

            <!-- ABOUT -->
            <div class="col-md-4 mb-4">
                <h5>Angle’s Palace</h5>
                <p>
                    A modern beauty salon dedicated to enhancing your natural beauty
                    with expert care and premium treatments.
                </p>
            </div>

            <!-- CONTACT INFO -->
            <div class="col-md-4 mb-4">
                <h5>Contact Info</h5>
                <p>📞 +123 456 7890</p>
                <p>✉️ info@anglespalace.com</p>
                <p>📍 123 Beauty Street, Cityville</p>
            </div>

            <!-- OPENING HOURS -->
            <div class="col-md-4 mb-4">
                <h5>Opening Hours</h5>
                <p>Monday – Sunday</p>
                <p>9:00 AM – 8:00 PM</p>
                <a href="../Mainpage/book.php" class="btn btn-login book-btn">Book Now</a>

            </div>

        </div>
    </div>
</footer>
<!-- Footer END -->


<?php
require_once("../public/alert.php");
require_once("../include/footer.php")
?>