<?php
require 'connect.php';

session_start();
// $conn = new mysqli("localhost", "root", "", "your_database_name");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    // if ($admin && password_verify($password, $admin["password"])) 
    if ($admin && $password) {
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["username"] = $admin["username"];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
   * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(90deg, #2c3e50, #3498db);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        .form-box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .form-box h2 {
            margin-bottom: 25px;
            color: #333;
            font-size: 24px;
        }

        .form-box input {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-box input:focus {
            border-color: #3498db;
            outline: none;
        }

        .form-box button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .form-box button:hover {
            background: #2980b9;
        }

        .form-box p {
            margin-top: 20px;
            font-size: 14px;
        }

        .form-box a {
            color: #3498db;
            text-decoration: none;
            
        }

        .form-box a:hover {
            text-decoration: underline;
        }

        .error {
            background: #ffdddd;
            color: #d8000c;
            border: 1px solid #d8000c;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 7688px) {
            .form-box {
                padding: 30px 20px;
            }

            .form-box h2 {
                font-size: 20px;
            }

            .form-box input,
            .form-box button {
                font-size: 15px;
                padding: 10px;
            }

            .form-box p {
                font-size: 13px;
            }
             a {
                cursor:none;
        }
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Admin Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
