 <?php
// require 'connect.php';
// session_start();
// if (!isset($_SESSION["admin_id"])) {
//     header("Location: login.php");
//     exit;
// }
?> 

<!-- <!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; background: #eef; padding: 50px; text-align: center; }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
    <p>This is your admin dashboard.</p>
    <a href="logout.php">Logout</a>
</body>
</html> -->



<?php
require 'connect.php';
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
         body {
            font-family: Arial, sans-serif;
            background: #eef;
            
            margin: 0;
        }
         .header{
           background: linear-gradient(90deg, #2c3e50, #3498db);
             height: 200px;
            display: flex;
            align-content: space-around;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        h1 {
            color: white;
            font-size:50px;
            margin:0px
        }
        p{
            margin-bottom:0px;
            font-size:18px;
        }

        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .card-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 40px;
            gap: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px 20px;
            width: 250px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            text-decoration: none;
            color: #2c3e50;
        }

        .card:hover {
            transform: translateY(-5px);
            background-color: #f0f8ff;
        }

        .card h3 {
            margin: 0;
            font-size: 20px;
        }

        .logout-btn {
            margin-top: 40px;
            padding: 12px 25px;
            font-size: 16px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        @media (max-width: 768px) {
            .card-container {
                flex-direction: column;
                align-items: center;
               
            }

            .card {
                width: 80%;
                cursor:none;
            }
            .header{
                height:150px;
            }
            h1{
                font-size:35px;
            }
        } 
       
            
    </style>
</head>
<body>
    <div class="header">
     <h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
        <p>This is your admin dashboard.</p>
        </div>
    <div class="dashboard-container">
       

        <div class="card-container">
            <a href="create_class.php" class="card">
                <h3>Create Class</h3>
            </a>
            <a href="take_attendance.php" class="card">
                <h3>Take Attendance</h3>
            </a>
            <a href="view_attendance.php" class="card">
                <h3>View Attendance</h3>
            </a>
            <a href="delete_class.php" class="card">
                <h3>Delete Class</h3>
            </a>
                <a href="profile.php" class="card">
                     <h3>Profile Settings</h3>
            </a>
            <a href="analytics.php" class="card">
              <h3>Attendance Analytics</h3>
            </a>


        </div>
        

        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</body>
</html>
