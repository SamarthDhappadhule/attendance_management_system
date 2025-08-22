<?php
session_start();
require 'connect.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class_name = trim($_POST["class_name"]);
    
    $start_roll = (int) $_POST["start_roll"];
    $end_roll = (int) $_POST["end_roll"];

    // Validation
    if (empty($class_name)  || $start_roll <= 0 || $end_roll <= 0) {
        $error = "All fields are required and must be valid.";
    } elseif ($start_roll > $end_roll) {
        $error = "Starting roll number must be less than or equal to ending roll number.";
    } else {
        // Prepare and insert into DB
       $stmt = $conn->prepare("INSERT INTO classes (class_name, start_roll, end_roll, admin_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siii", $class_name, $start_roll, $end_roll, $_SESSION['admin_id']);


        if ($stmt->execute()) {
            $success = "Class created successfully!";
        } else {
            $error = "Failed to create class: " . $conn->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Class</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef;
            background:linear-gradient(90deg, #2c3e50, #3498db);
            padding: 50px;
            margin: 0;
        }

        .container {
            max-width: 500px;
            background: white;
            margin: auto;
            margin-top:80px;
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

        input[type="text"],
        input[type="date"],
        input[type="number"] {
            width: 95%;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
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
            transition:0.3s ease-in-out;
            transform:scale(1.1);
        }
         @media (max-width: 768px) {
            body{
                padding:140px 10px;
            }
            .back-link{
                cursor:none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create New Class</h2>

        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="class_name">Class Name</label>
                <input type="text" name="class_name" id="class_name" required>
            </div>

            <div class="form-group">
                <label for="start_roll">Starting Roll Number</label>
                <input type="number" name="start_roll" id="start_roll" min="1" required>
            </div>

            <div class="form-group">
                <label for="end_roll">Ending Roll Number</label>
                <input type="number" name="end_roll" id="end_roll" min="1" required>
            </div>

            <button type="submit" class="btn">Create Class</button>
        </form>

        <a href="dashboard.php" class="back-link">< Back to Dashboard</a>
    </div>
</body>
</html>
