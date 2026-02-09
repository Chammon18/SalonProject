<?php if (!$isPast): ?>
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Customer:</strong> <?= htmlspecialchars($appointment['user_name']) ?></div>
                    <div class="mb-2"><strong>Date:</strong> <?= date('d M Y', strtotime($appointment['appointment_date'])) ?></div>
                    <div class="mb-3"><strong>Time:</strong> <?= date('H:i', strtotime($appointment['appointment_time'])) ?></div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($serviceItems as $srv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($srv['name']) ?></td>
                                        <td class="text-end"><?= number_format((float)$srv['price'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end"><?= number_format((float)$groupTotal, 2) ?></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($paymentExists): ?>
                        <div class="alert alert-info mb-0">Payment has already been recorded for this appointment group.</div>
                    <?php else: ?>
                        <div class="mb-2">
                            <label for="payment-method-select" class="form-label">Payment Method</label>
                            <select id="payment-method-select" class="form-select">
                                <option value="">Select payment method</option>
                                <option value="Cash">Cash</option>
                                <option value="Kpay">Kpay</option>
                                <option value="AYApay">AYApay</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <?php if (!$paymentExists): ?>
                        <button type="button" class="btn btn-success" id="confirm-payment-btn">Confirm & Update</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>