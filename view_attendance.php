<?php
session_start();
require 'connect.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$classes = [];
$attendance_data = [];
$selected_class = null;
 $attendance_date = date('Y-m-d'); // default date today

// Fetch all classes for this admin
$admin_id = $_SESSION["admin_id"];
$stmt = $conn->prepare("SELECT * FROM classes WHERE admin_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["class_id"]) && isset($_POST["attendance_date"])) {
    $class_id = $_POST["class_id"];
    $attendance_date = $_POST["attendance_date"];

    // Get class info
    $stmt1 = $conn->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt1->bind_param("i", $class_id);
    $stmt1->execute();
    $selected_class_result = $stmt1->get_result();
    $selected_class = $selected_class_result->fetch_assoc();
    $stmt1->close();

    // Get attendance records for selected class and date
    $stmt2 = $conn->prepare("SELECT * FROM attendance WHERE class_id = ? AND date_recorded = ? ORDER BY roll_no ASC");
    $stmt2->bind_param("is", $class_id, $attendance_date);
    $stmt2->execute();
    $attendance_result = $stmt2->get_result();

    $attendance_data = [];
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    $stmt2->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Attendance</title>
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
            max-width: 900px;
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

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        select, input[type="date"], button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            box-sizing: border-box;
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
        .no-data {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }

        .status-present {
            background-color: #2ecc71;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-absent {
            background-color: #e74c3c;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-late {
            background-color: #fff;
            color: #333;
            border: 1px solid #ccc;
            padding: 5px 12px;
            border-radius: 5px;
            font-weight: bold;
        }
        .download{
            display: inline-block;
    background-color: #000000;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    margin-bottom: 20px;
    margin-top: 20px;
    font-weight: bold;
    margin-left: 40%;
        }
        @media (max-width:480px) {
            
        body{
            padding:0px;
        }
        .download{
      margin-left: 28%;
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
    <h2>View Attendance Records</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label for="class_id">Select Class</label>
            <select name="class_id" id="class_id" required>
                <option value="">-- Select a class --</option>
                <?php foreach ($classes as $cls): ?>
                    <option value="<?php echo $cls['id']; ?>" <?php if (isset($_POST['class_id']) && $_POST['class_id'] == $cls['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cls['class_name'])  ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="attendance_date">Select Date</label>
            <input type="date" id="attendance_date" name="attendance_date" 
                   value="<?php echo htmlspecialchars($attendance_date); ?>" required>
        </div>

        <button type="submit">View Attendance</button>
    </form>

    <?php if ($selected_class): ?>
        <h3 style="text-align: center; margin-top: 30px;">
            Class: <?php echo htmlspecialchars($selected_class["class_name"]); ?> |
            Date: <?php echo htmlspecialchars($attendance_date); ?>
        </h3>

        <?php if (count($attendance_data) > 0): ?>
            <table>
                <thead>
                <tr>
                    <th>Roll No.</th>
                    <th>Status</th>
                    <th>Date Recorded</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($attendance_data as $record): ?>
                    <tr>
                        <td><?php echo $record["roll_no"]; ?></td>
                        <td>
                            <?php
                            $status = $record["status"];
                            $statusClass = "";
                            if ($status === "Present") $statusClass = "status-present";
                            elseif ($status === "Absent") $statusClass = "status-absent";
                            elseif ($status === "Late") $statusClass = "status-late";
                            ?>
                            <span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span>
                        </td>
                        <td><?php echo $record["date_recorded"]; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <a href="export_csv.php?class_id=<?php echo $selected_class['id']; ?>&date=<?php echo $attendance_date; ?>" class="download">
                ⬇ Download CSV
            </a>

        <?php else: ?>
            <p class="no-data">No attendance records found for this class on the selected date.</p>
        <?php endif; ?>
    <?php endif; ?>

    <a href="dashboard.php" class="back-link">< Back to Dashboard</a>
</div>
</body>
</html>
