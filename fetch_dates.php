<?php
require 'connect.php';
header('Content-Type: application/json');

if (isset($_GET['class_id'])) {
    $class_id = intval($_GET['class_id']);
    $stmt = $conn->prepare("SELECT DISTINCT date_recorded FROM attendance WHERE class_id = ? ORDER BY date_recorded DESC");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $dates = [];
    while ($row = $res->fetch_assoc()) {
        $dates[] = $row['date_recorded'];
    }
    echo json_encode($dates);
}
?>
