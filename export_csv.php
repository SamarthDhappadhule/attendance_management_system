<?php
require 'connect.php';

if (!isset($_GET['class_id']) || empty($_GET['class_id'])) {
    die("Class ID is required.");
}

$class_id = intval($_GET['class_id']);

// Get class info
session_start();
$admin_id = $_SESSION["admin_id"];
$class_stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ? AND admin_id = ?");
$class_stmt->bind_param("ii", $class_id, $admin_id);

$class_stmt->execute();
$class_result = $class_stmt->get_result();
$class = $class_result->fetch_assoc();

if (!$class) {
    die("Class not found.");
}

// Get attendance data
$stmt = $conn->prepare("SELECT roll_no, status, date_recorded FROM attendance WHERE class_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

$filename = "Attendance_" . $class['class_name'] . "_"  . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen("php://output", "w");
fputcsv($output, ['Roll No.', 'Status', 'Date Recorded']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['roll_no'], $row['status'], $row['date_recorded']]);
}

fclose($output);
exit;
