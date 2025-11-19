<?php
session_start();
require_once "../db/config.php"; // Adjust path as needed

// Check if student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: student_login.php");
    exit;
}

$student = $_SESSION['student'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Slip Form - SLC College</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .student-view {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 0;
        }

        .queue-number {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
        }

        .payment-slip {
            border: 2px solid #000;
            padding: 20px;
            background: white;
            max-width: 500px;
            margin: 0 auto;
        }

        .payment-slip .header {
            text-align: center;
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .form-field {
            margin-bottom: 15px;
        }

        .form-field label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .checkbox-group {
            margin: 10px 0;
        }

        .other-specify {
            margin-left: 25px;
            margin-top: 5px;
        }

        .dashboard-card {
            transition: transform 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        /* Custom modal */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }

        .custom-modal.show {
            display: flex !important;
        }

        .custom-modal-content {
            background: white;
            border-radius: 0.3rem;
            width: 90%;
            max-width: 500px;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="student-view">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-dark bg-dark bg-opacity-50">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>SLC College Student Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($student['name']); ?></span>
                <a class="btn btn-outline-light btn-sm" href="../api/student-logout-b.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow dashboard-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">Cashier Queue System</h3>
                    </div>
                    <div class="card-body p-4">

                        <!-- Display Messages -->
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-info alert-dismissible fade show">
                                <?php
                                echo $_SESSION['message'];
                                unset($_SESSION['message']);
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Payment Slip Form -->
                        <div class="card mb-4 dashboard-card" id="paymentSlipCard">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Payment Slip</h5>
                            </div>
                            <div class="card-body">
                                <div class="payment-slip">
                                    <div class="header">
                                        <h4 class="mb-1">Saint Louis College</h4>
                                        <p class="mb-1">City of San Fernando, 2500 La Union</p>
                                        <h5 class="mb-0">PAYMENT SLIP</h5>
                                    </div>

                                    <form id="paymentForm" method="POST" action="../api/process-payment-queue.php">
                                        <input type="hidden" name="action" value="submit_payment_slip">

                                        <div class="form-field">
                                            <label for="name">NAME:</label>
                                            <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                                        </div>

                                        <div class="form-field">
                                            <label for="student_id">ID NO:</label>
                                            <input type="text" class="form-control" id="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" readonly>
                                        </div>

                                        <div class="form-field">
                                            <label for="amount">AMOUNT: <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" placeholder="0.00" required>
                                        </div>

                                        <div class="form-field">
                                            <label>IN PAYMENT OF: <span class="text-danger">*</span></label>
                                            <div class="checkbox-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="Tuition Fee" id="tuition">
                                                    <label class="form-check-label" for="tuition">Tuition Fee</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="Transcript" id="transcript">
                                                    <label class="form-check-label" for="transcript">Transcript</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="Overdue" id="overdue">
                                                    <label class="form-check-label" for="overdue">Overdue</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="payment_for[]" value="others" id="others">
                                                    <label class="form-check-label" for="others">Others (Please specify)</label>
                                                </div>
                                                <div class="other-specify">
                                                    <input type="text" class="form-control mt-1" name="other_specify" placeholder="Specify other payment" id="otherSpecify" disabled>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 mt-4">
                                            <!-- Go Back to Dashboard -->
                                            <button class="btn btn-secondary btn-lg" type="button" onclick="window.location.href='../student_dashboard.php'">
                                                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                                            </button>
                                            <!-- Submit Button -->
                                            <button class="btn btn-success btn-lg" type="button" id="confirmBtn">
                                                <i class="fas fa-ticket-alt me-2"></i>Submit Payment & Get Queue Number
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Modal -->
                        <div class="custom-modal" id="customModal">
                            <div class="custom-modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirm Payment</h5>
                                    <button type="button" class="btn-close" onclick="closeModal()"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to submit this payment slip and get your queue number?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="modalSubmitBtn">Confirm & Submit</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable "Others" text field when checkbox is checked
        const othersCheckbox = document.getElementById('others');
        const otherSpecify = document.getElementById('otherSpecify');
        const customModal = document.getElementById('customModal');
        const paymentForm = document.getElementById('paymentForm');

        if (othersCheckbox && otherSpecify) {
            othersCheckbox.addEventListener('change', () => {
                otherSpecify.disabled = !othersCheckbox.checked;
                if (!othersCheckbox.checked) {
                    otherSpecify.value = '';
                }
            });
        }

        // Simple modal functions
        function showModal() {
            customModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            customModal.classList.remove('show');
            document.body.style.overflow = '';
        }

        // Confirm button click handler
        document.getElementById('confirmBtn').addEventListener('click', function(e) {
            e.preventDefault();

            // Validate required fields
            const amount = document.getElementById('amount').value;
            const checkedPayments = document.querySelectorAll('input[name="payment_for[]"]:checked');

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            if (checkedPayments.length === 0) {
                alert('Please select at least one payment option');
                return;
            }

            // Check if "Others" is checked but not specified
            if (othersCheckbox.checked && !otherSpecify.value.trim()) {
                alert('Please specify the "Others" payment option');
                return;
            }

            // Show modal if validation passes
            showModal();
        });

        // Modal submit handler - ACTUALLY SUBMIT THE FORM
        document.getElementById('modalSubmitBtn').addEventListener('click', function() {
            // Submit the form
            paymentForm.submit();
        });

        // Close modal when clicking outside
        customModal.addEventListener('click', function(e) {
            if (e.target === customModal) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && customModal.classList.contains('show')) {
                closeModal();
            }
        });
    </script>
</body>

</html>