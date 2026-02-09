<?php if (!$isPast): ?>
    <script>
        window.addEventListener('load', function() {
            function ensureBootstrap(cb) {
                if (window.bootstrap && window.bootstrap.Modal) {
                    cb();
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js';
                script.onload = cb;
                document.head.appendChild(script);
            }

            function initModal() {
                const form = document.querySelector('form[method="post"]');
                const paymentMethodInput = document.getElementById('payment_method');
                const paymentModalEl = document.getElementById('paymentModal');
                const paymentSelect = document.getElementById('payment-method-select');
                const confirmBtn = document.getElementById('confirm-payment-btn');
                const hasPayment = <?= $paymentExists ? 'true' : 'false' ?>;
                const showOnLoad = <?= (($_GET['error'] ?? '') === 'payment_required') ? 'true' : 'false' ?>;

                if (!form || !paymentModalEl) return;

                if (showOnLoad && !hasPayment) {
                    const modal = new bootstrap.Modal(paymentModalEl);
                    modal.show();
                }

                const statusSelects = form.querySelectorAll('select[name^="status["]');
                statusSelects.forEach(function(sel) {
                    sel.addEventListener('change', function() {
                        if (hasPayment) return;
                        if (sel.value === 'completed' && !paymentMethodInput.value) {
                            const modal = new bootstrap.Modal(paymentModalEl);
                            modal.show();
                        }
                    });
                });

                form.addEventListener('submit', function(e) {
                    if (hasPayment) return;
                    let needsPayment = false;
                    statusSelects.forEach(function(sel) {
                        if (sel.value === 'completed') needsPayment = true;
                    });
                    if (needsPayment && !paymentMethodInput.value) {
                        e.preventDefault();
                        const modal = new bootstrap.Modal(paymentModalEl);
                        modal.show();
                    }
                });

                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function() {
                        if (!paymentSelect || paymentSelect.value === '') {
                            paymentSelect.classList.add('is-invalid');
                            return;
                        }
                        paymentSelect.classList.remove('is-invalid');
                        paymentMethodInput.value = paymentSelect.value;
                        form.submit();
                    });
                }
            }

            ensureBootstrap(initModal);
        });
    </script>
<?php endif; ?>