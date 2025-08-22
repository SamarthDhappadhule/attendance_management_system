<?php
require 'connect.php';

session_start();

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password); //$hashed;

        if ($stmt->execute()) {
            $success = "Admin registered successfully!";
        } else {
            $error = "Username already exists.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

     body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #74ebd5, #acb6e5);
             background: linear-gradient(90deg, #2c3e50, #3498db);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-box {
            background: #fff;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        input {
            width: 92%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #5c9ded;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #5c9ded;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4a8ad4;
        }

        .success {
            color: green;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }

        p {
            text-align: center;
        }

        a {
            color: #5c9ded;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .form-box {
                padding: 25px;
            }

            input, button {
                font-size: 15px;
            }
              a {
                cursor:none;
        }
        }

    </style>
</head>
<body>
    <div class="form-box">
        <h2>Admin Registration</h2>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already registered? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
