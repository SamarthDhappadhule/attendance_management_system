<?php
session_start();
require 'connect.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Analytics</title>
        <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
       
              body {
            font-family: Arial, sans-serif;
           background: linear-gradient(90deg, #2c3e50, #3498db);   
            padding: 20px;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        p{
            display:none;
        }
        b{
            margin-left:60px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }

        h3 {
            margin-top: 22px;
            color: #34495e;
            text-align: center;
        }

        form {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            justify-content: center;
        }

        label {
            font-weight: bold;
        }

        select {
            padding: 10px;
            font-size: 16px;
            width: 250px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }
        .back-link {
            display: block;
            text-align: right;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }

        .back-link:hover {
             color: #20597eff;
        }

        canvas {
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            body{
                padding:0px;
            }
            .container{
                border-radius:0px;
            }
            b{
                display:none;
            }
            p{
                display:block;
                text-align:center;
            }

            form {
                flex-direction: column;
            }

            select {
                width: 100%;
            }

            canvas {
                width: 100% !important;
                height: auto !important;
            }
            strong{
                display:none;
            }
            .back-link{
                cursor:none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
                <a class="back-link" href="dashboard.php">< Back to Dashboard</a>

        <h2>Attendance Analytics</h2>
        
        <form method="GET">
            <label for="class_id">Select Class:</label>
            <select name="class_id" required>
                <option value="">-- Select Class --</option>
                <?php
                $stmt = $conn->prepare("SELECT id, class_name FROM classes WHERE admin_id = ?");
                $stmt->bind_param("i", $admin_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($class = $result->fetch_assoc()) {
                    $selected = isset($_GET['class_id']) && $_GET['class_id'] == $class['id'] ? 'selected' : '';
                    echo "<option value='{$class['id']}' $selected>{$class['class_name']}</option>";
                }
                ?>
            </select>
            <button type="submit">View</button>
        </form>

        <?php
        if (isset($_GET['class_id'])) {
            $class_id = $_GET['class_id'];

            // Fetch all students for this class
           // Get roll number range for the class
$stmt = $conn->prepare("SELECT start_roll, end_roll FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

$students = [];
for ($roll = $class['start_roll']; $roll <= $class['end_roll']; $roll++) {
    $students[] = ['roll_no' => $roll, 'name' => 'Roll No. ' . $roll];
}


            $labels = [];
            $percentages = [];

            $total_present = 0;
            $total_absent = 0;

            foreach ($students as $student) {

                $roll = $student['roll_no'];
                $name = $student['name'];

                $stmt2 = $conn->prepare("SELECT COUNT(*) as total, SUM(status = 'Present') as present FROM attendance WHERE class_id = ? AND roll_no = ?");
                $stmt2->bind_param("ii", $class_id, $roll);
                $stmt2->execute();
                $result2 = $stmt2->get_result()->fetch_assoc();

                $total = $result2['total'];
                $present = $result2['present'];
                $absent = $total - $present;

                $percentage = ($total > 0) ? round(($present / $total) * 100, 1) : 0;

                $labels[] = $name;
                $percentages[] = $percentage;

                $total_present += $present;
                $total_absent += $absent;
            }

            // Pass data to JavaScript
            $jsonLabels = json_encode($labels);
            $jsonPercentages = json_encode($percentages);
            $jsonPieData = json_encode([$total_present, $total_absent]);
        ?>

        <h3>Attendance % per Student</h3>
      <strong>Color Legend:</strong>

    <b style="color: #e74c3c;">Red: 0% – 50%</b>
    <b style="color: #f1c40f; ">Yellow: 51% – 75%</b>
    <b style="color: #2ecc71; ">Green: 76% – 100%</b>
     <p style="color: #e74c3c;">Red: 0% – 50%</p>
    <p style="color: #f1c40f; ">Yellow: 51% – 75%</p>
    <p style="color: #2ecc71; ">Green: 76% – 100%</p>

        <canvas id="barChart" width="400" height="200"></canvas>

        <h3>Overall Class Attendance</h3>
        <canvas id="pieChart" width="200" height="50"></canvas>

        <!-- <script>
            const labels = <?= $jsonLabels ?>;
            const percentages = <?= $jsonPercentages ?>;
            const pieData = <?= $jsonPieData ?>;

            new Chart(document.getElementById('barChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Attendance %',
                        data: percentages,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)'
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, max: 100 }
                    }
                }
            });

            new Chart(document.getElementById('pieChart'), {
                type: 'pie',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: pieData,
                        backgroundColor: ['#2ecc71', '#e74c3c']
                    }]
                }
            });
        </script> -->
       <script>
    const labels = <?= $jsonLabels ?>;
    const percentages = <?= $jsonPercentages ?>;
    const pieData = <?= $jsonPieData ?>;

    // Assign color to each bar based on attendance %
    const barColors = percentages.map(p => {
        if (p <= 50) return '#e74c3c';      // Red
        if (p <= 75) return '#f1c40f';      // Yellow
        return '#2ecc71';                  // Green
    });

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Attendance %',
                data: percentages,
                backgroundColor: barColors
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.parsed.y}% attendance`;
                        }
                    }
                }
            }
        }
    });

    // Pie Chart (unchanged)
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: ['Present', 'Absent'],
            datasets: [{
                data: pieData,
                backgroundColor: ['#2ecc71', '#e74c3c']
            }]
        }
    });
</script>


        <?php } ?>
    </div>
</body>
</html>
