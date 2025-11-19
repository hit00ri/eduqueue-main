<?php

$notification = "Queue successfully processed!";

$socket = stream_socket_client("tcp://localhost:8080", $errno, $errstr);

if (!$socket) {
    echo "Failed to connect: $errstr ($errno)";
    exit;
}

$key = base64_encode(random_bytes(16));

$headers  = "GET / HTTP/1.1\r\n";
$headers .= "Host: localhost:8080\r\n";
$headers .= "Upgrade: websocket\r\n";
$headers .= "Connection: Upgrade\r\n";
$headers .= "Sec-WebSocket-Key: $key\r\n";
$headers .= "Sec-WebSocket-Version: 13\r\n\r\n";

fwrite($socket, $headers);
fread($socket, 2000);

// Send notification to WebSocket server
$frame = chr(0x81) . chr(strlen($notification)) . $notification;
fwrite($socket, $frame);

echo "Notification Sent!\n";

fclose($socket);
?>