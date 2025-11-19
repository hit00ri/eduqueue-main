<?php
require_once "../db/config.php";

// Check if student is logged in
if (!isset($_SESSION['student'])) {
    header("Location: student_login.php");
    exit;
}

$student = $_SESSION['student'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_payment_slip') {
    // Validate payment slip data
    $amount = $_POST['amount'] ?? '';
    $payment_for = $_POST['payment_for'] ?? [];
    $other_specify = $_POST['other_specify'] ?? '';
    
    // Validation
    if (empty($amount) || empty($payment_for)) {
        $_SESSION['message'] = "Please fill all required fields in the payment slip";
        header("Location: ../student_dashboard.php");
        exit();
    }
    
    // Check if student already has an active queue number today
    $existingQueue = $conn->prepare("
        SELECT queue_number, status, queue_id 
        FROM queue 
        WHERE student_id = ? AND DATE(time_in) = CURDATE() AND status IN ('waiting', 'serving')
        LIMIT 1
    ");
    $existingQueue->execute([$student['student_id']]);
    $existing = $existingQueue->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $_SESSION['message'] = "You already have an active queue number: <strong>#{$existing['queue_number']}</strong> (Status: " . ucfirst($existing['status']) . ")";
    } else {
        // Get the last queue number for today
        $last = $conn->query("
            SELECT queue_number FROM queue 
            WHERE DATE(time_in) = CURDATE() 
            ORDER BY queue_id DESC LIMIT 1
        ")->fetchColumn();

        $next = $last ? $last + 1 : 1;

        // Prepare payment_for string
        $paymentForString = implode(', ', $payment_for);
        if (!empty($other_specify) && in_array('others', $payment_for)) {
            $paymentForString .= " ($other_specify)";
        }

        // Insert new queue with payment slip data
        $stmt = $conn->prepare("
            INSERT INTO queue (student_id, queue_number, status, time_in) 
            VALUES (?, ?, 'waiting', NOW())
        ");
        
        if ($stmt->execute([$student['student_id'], $next])) {
            $_SESSION['message'] = "Payment submitted successfully! Your queue number is: <strong>#{$next}</strong>";
        } else {
            $_SESSION['message'] = "Error submitting payment and getting queue number. Please try again.";
        }
    }
    
    header("Location: ../student_dashboard.php");
    exit();
} else {
    // Invalid access
    header("Location: ../student_dashboard.php");
    exit();
}
?>