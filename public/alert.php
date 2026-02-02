<?php
// for book.php alert
if (!empty($error_msg) || !empty($success_msg)) : ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (!empty($error_msg)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Booking Error',
                text: <?= json_encode($error_msg) ?>,
                confirmButtonColor: '#28a745'
            });
        <?php endif; ?>

        <?php if (!empty($success_msg)) : ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: <?= json_encode($success_msg) ?>,
                confirmButtonColor: '#28a745'
            }).then(() => {
                <?php if (!empty($redirect_url)) : ?>
                    window.location.href = <?= json_encode($redirect_url) ?>;
                <?php endif; ?>
            });
        <?php endif; ?>
    </script>
<?php endif; ?>


<!-- Login user check -->
<script>
    document.querySelectorAll('.book-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!isLoggedIn) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Login Required',
                    text: 'Please log in first to book our services.',
                    showCancelButton: true,
                    confirmButtonText: 'Login Now',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    background: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../Mainpage/login.php';
                    }
                });
            }
        });
    });

    // for index->about reveal left - RIGHT

    document.addEventListener("DOMContentLoaded", function() {
        const reveals = document.querySelectorAll('.reveal-left, .reveal-right');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, {
            threshold: 0.2
        });

        reveals.forEach(el => observer.observe(el));
    });
</script>

<!-- about page -->
<script>
    const reveals = document.querySelectorAll('.reveal-left, .reveal-right');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, {
        threshold: 0.3
    });

    reveals.forEach(el => observer.observe(el));
</script>