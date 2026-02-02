<?php
require_once __DIR__ . '/../../config/config.php';

// Set header to indicate response is not a webpage
header('Content-Type: application/json');
http_response_code(204); // No Content

// 1. Get data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 2. Validate data
if (!$data || !isset($data['visitor_id']) || !isset($data['event_type'])) {
    // Silently exit if data is invalid
    exit;
}

// 3. Prepare data for insertion
$visitor_id = $data['visitor_id'];
$event_type = $data['event_type'];
$target_feature = $data['target_feature'] ?? null;
// For 'stay' events, value is stored in target_feature
if ($event_type === 'stay' && isset($data['value'])) {
    $target_feature = $data['value'];
}


// 4. Database connection
$db = get_db_connection();

// 5. Insert into database
$stmt = $db->prepare(
    "INSERT INTO interactions (visitor_id, event_type, target_feature, created_at) 
     VALUES (:visitor_id, :event_type, :target_feature, :created_at)"
);

$stmt->execute([
    ':visitor_id' => $visitor_id,
    ':event_type' => $event_type,
    ':target_feature' => $target_feature,
    ':created_at' => date('Y-m-d H:i:s')
]);

exit;