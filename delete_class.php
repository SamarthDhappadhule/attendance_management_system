


<?php
session_start();
require 'connect.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION["admin_id"];
$success = "";
$error = "";

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class_id = intval($_POST["class_id"] ?? 0);
    $delete_type = $_POST["delete_type"] ?? '';

    if ($delete_type === "date_only" && isset($_POST["attendance_date"])) {
        // Delete attendance for specific date
        $date = $_POST["attendance_date"];
        $stmt = $conn->prepare("DELETE FROM attendance WHERE class_id = ? AND date_recorded = ?");
        $stmt->bind_param("is", $class_id, $date);
        $stmt->execute();
        $success = ($stmt->affected_rows > 0)
            ? "Attendance for $date deleted successfully."
            : "No attendance found for $date.";
        $stmt->close();
    } elseif ($delete_type === "full_class") {
        // Verify admin permission
        $stmt = $conn->prepare("SELECT id FROM classes WHERE id = ? AND admin_id = ?");
        $stmt->bind_param("ii", $class_id, $admin_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
$stmt_attendance = $conn->prepare("DELETE FROM attendance WHERE class_id = ?");
if (!$stmt_attendance) {
    die("Prepare failed (attendance): " . $conn->error);
}
$stmt_attendance->bind_param("i", $class_id);
$stmt_attendance->execute();
$stmt_attendance->close();

$stmt_class = $conn->prepare("DELETE FROM classes WHERE id = ?");
if (!$stmt_class) {
    die("Prepare failed (class): " . $conn->error);
}
$stmt_class->bind_param("i", $class_id);
$stmt_class->execute();
$stmt_class->close();

        $success = "Class and all attendance deleted successfully.";
        } else {
            $error = "Invalid class or unauthorized action.";
        }
        $stmt->close();
    }
}

// Fetch admin's classes
$classes = [];
$stmt = $conn->prepare("SELECT id, class_name FROM classes WHERE admin_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Class / Attendance</title>
      <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
     font-family: Arial;
     background:linear-gradient(90deg, #2c3e50, #3498db);
     padding: 50px;
     margin: 0;
 }

 .container {
     max-width: 700px;
     margin: auto;
     background: white;
     padding: 30px;
     border-radius: 10px;
     box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
 }

 h2 {
     text-align: center;
     color: #c0392b;
 }

 .form-group {
     margin-bottom: 20px;
 }

 label {
     display: block;
     margin-bottom: 10px;
     font-weight: bold;
 }

 select,
 button {
     width: 100%;
     padding: 12px;
     font-size: 16px;
 }

 button {
     background-color: #e74c3c;
     color: white;
     border: none;
     border-radius: 5px;
     cursor: pointer;
 }

 .message {
     padding: 12px;
     margin-bottom: 15px;
     border-radius: 5px;
 }

 .success {
     background-color: #d4edda;
     color: #155724;
 }

 .error {
     background-color: #f8d7da;
     color: #721c24;
 }

 .back-link {
     display: block;
     text-align: center;
     margin-top: 20px;
     color: #3498db;
     text-decoration: none;
 }

 .back-link:hover {
     transition: 0.3s ease-in-out;
     transform: scale(1.1);
 }

 .divider {
     text-align: center;
     margin: 25px 0;
     font-weight: bold;
     color: #aaa;
 }
  @media (max-width:480px) {
            
        body{
            padding: 150px 10px;
        }
        .back-link{
                cursor:none;
            }
    }

    </style>
</head>
<body>
<div class="container">
    <h2>Delete Class / Specific Attendance</h2>

    <?php if ($success): ?><div class="message success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="message error"><?= $error ?></div><?php endif; ?>

    <!-- Delete attendance for a specific date -->
    <form method="POST">
        <input type="hidden" name="delete_type" value="date_only">
        <div class="form-group">
            <label for="class-select">Select Class</label>
            <select name="class_id" id="class-select" required>
                <option value="">-- Select a class --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="date-select">Select Date to Delete</label>
            <select name="attendance_date" id="date-select" required>
                <option value="">-- Select a date --</option>
            </select>
        </div>
        <button type="submit" onclick="return confirm('Delete attendance for selected date?');">Delete Attendance for Date</button>
    </form>

    <div class="divider">OR</div>

    <!-- Delete entire class -->
    <form method="POST">
        <input type="hidden" name="delete_type" value="full_class">
        <div class="form-group">
            <label for="class-id-full">Delete Entire Class</label>
            <select name="class_id" id="class-id-full" required>
                <option value="">-- Select a class --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" onclick="return confirm('Delete entire class and all attendance?');">Delete Entire Class</button>
    </form>

    <a href="dashboard.php" class="back-link">< Back to Dashboard</a>
</div>

<script>
document.getElementById('class-select').addEventListener('change', function() {
    const classId = this.value;
    const dateSelect = document.getElementById('date-select');
    dateSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`fetch_dates.php?class_id=${classId}`)
        .then(res => res.json())
        .then(data => {
            dateSelect.innerHTML = '<option value="">-- Select a date --</option>';
            if (data.length) {
                data.forEach(date => {
                    const opt = document.createElement('option');
                    opt.value = date;
                    opt.textContent = date;
                    dateSelect.appendChild(opt);
                });
            } else {
                dateSelect.innerHTML = '<option value="">No attendance found</option>';
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            dateSelect.innerHTML = '<option value="">Error loading dates</option>';
        });
});
</script>
</body>
</html>
