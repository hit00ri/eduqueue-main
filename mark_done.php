<?php
require_once "../db/config.php";

// Update queue status
$id = $_POST['id'];
$conn->query("UPDATE queue SET status='done' WHERE id='$id'");

// Send notification to WebSocket server
$data = [
    "type" => "notification",
    "message" => "Your queue is done",
    "queue_id" => $id
];

$ch = curl_init("http://localhost:8080/notify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

echo "Queue marked as done.";
?>
