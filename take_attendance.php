<?php
session_start();
require 'connect.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION["admin_id"];
$classes = [];
$success = "";
$error = "";
$attendance_exists = false;
$existing_attendance = [];

// Fetch classes
$stmt = $conn->prepare("SELECT * FROM classes WHERE admin_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}
$stmt->close();

// Fetch existing attendance if class & date are selected
if (isset($_POST["class_id"], $_POST["date"])) {
    $class_id = $_POST["class_id"];
    $attendance_date = $_POST["date"];

    $check = $conn->prepare("SELECT roll_no, status FROM attendance WHERE class_id = ? AND date_recorded = ?");
    $check->bind_param("is", $class_id, $attendance_date);
    $check->execute();
    $result = $check->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing_attendance[$row['roll_no']] = $row['status'];
    }
    $check->close();

    $attendance_exists = count($existing_attendance) > 0;
}

// Handle save/update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["attendance"])) {
    $class_id = $_POST["class_id"];
    $attendance_date = $_POST["date"];
    $new_attendance = $_POST["attendance"];

    foreach ($new_attendance as $roll_no => $status) {
        $roll_no = (int)$roll_no;

        if (isset($existing_attendance[$roll_no])) {
            // Update only if status changed
            if ($existing_attendance[$roll_no] !== $status) {
                $stmt = $conn->prepare("UPDATE attendance SET status = ? WHERE class_id = ? AND roll_no = ? AND date_recorded = ?");
                $stmt->bind_param("siis", $status, $class_id, $roll_no, $attendance_date);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // New record (not previously marked)
            $stmt = $conn->prepare("INSERT INTO attendance (class_id, roll_no, status, date_recorded) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $class_id, $roll_no, $status, $attendance_date);
            $stmt->execute();
            $stmt->close();
        }
    }

    $success = $attendance_exists ? "Attendance updated for $attendance_date." : "Attendance saved for $attendance_date.";

    // Refresh existing_attendance after saving
    $existing_attendance = $new_attendance;
    $attendance_exists = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Take Attendance</title>
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
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        select, button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }
        input[type="date"]{
             width: 97%;
            padding: 10px;
            font-size: 16px;
        }

        table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
         .back-link:hover {
            transition:0.3s ease-in-out;
            transform:scale(1.1);
        }
        @media (max-width:480px) {
            body{
                padding:0px;
            }
              .container{
            border-radius:0px;
        }
        .back-link{
                cursor:none;
            }
            
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Take Attendance</h2>

    <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="class_id">Select Class</label>
            <select name="class_id" id="class_id" required onchange="this.form.submit()">
                <option value="">-- Select a class --</option>
                <?php foreach ($classes as $cls): ?>
                    <option value="<?php echo $cls['id']; ?>"
                        <?php if (isset($_POST['class_id']) && $_POST['class_id'] == $cls['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cls['class_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (isset($_POST["class_id"])): ?>
            <div class="form-group">
                <label for="date">Select Date</label>
                <input type="date" name="date" id="date" value="<?php echo $_POST['date'] ?? ''; ?>" required onchange="this.form.submit()">
            </div>
        <?php endif; ?>
    </form>

    <?php
    if (isset($_POST["class_id"], $_POST["date"])):
        $class_id = $_POST["class_id"];
        $date = $_POST["date"];
        $selected_class = null;

        foreach ($classes as $cls) {
            if ($cls['id'] == $class_id) {
                $selected_class = $cls;
                break;
            }
        }

        if ($selected_class):
    ?>
    <form method="POST" action="">
        <input type="hidden" name="class_id" value="<?php echo $selected_class['id']; ?>">
        <input type="hidden" name="date" value="<?php echo $date; ?>">

        <table>
            <thead>
                <tr>
                    <th>Roll No.</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = $selected_class['start_roll']; $i <= $selected_class['end_roll']; $i++): 
                    $status = $existing_attendance[$i] ?? 'Present';
                ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><input type="radio" name="attendance[<?php echo $i; ?>]" value="Present" <?php if ($status === 'Present') echo 'checked'; ?>></td>
                        <td><input type="radio" name="attendance[<?php echo $i; ?>]" value="Absent" <?php if ($status === 'Absent') echo 'checked'; ?>></td>
                        <td><input type="radio" name="attendance[<?php echo $i; ?>]" value="Late" <?php if ($status === 'Late') echo 'checked'; ?>></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <br>
        <button type="submit"><?php echo $attendance_exists ? "Update Attendance" : "Save Attendance"; ?></button>
    </form>
    <?php endif; endif; ?>

    <a href="dashboard.php" class="back-link">< Back to Dashboard</a>
</div>
</body>
</html>
