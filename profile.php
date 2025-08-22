<?php
session_start();
require 'connect.php';

$admin_id = $_SESSION["admin_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST["username"];
    // $new_password = password_hash($_POST["password"], PASSWORD_DEFAULT);
     $new_password = $_POST["password"];


    $stmt = $conn->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_username, $new_password, $admin_id);

    if ($stmt->execute()) {
        $_SESSION["username"] = $new_username;
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }
}
?>

<html>
<head>
    <title>Admin Profile</title>
      <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(90deg, #2c3e50, #3498db);
            padding: 155px 50px;
            margin: 0;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            color: #2ecc71;
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
            transform: scale(1.05);
        }
         @media (max-width: 768px) {
            body{
                padding:260px 10px;
            }
            .back-link{
                cursor:none;
            }
            
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Update Profile</h1>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>New Username:</label>
        <input type="text" name="username" required value="<?php echo htmlspecialchars($_SESSION["username"]); ?>">

        <label>New Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Update</button>
    </form>

    <a class="back-link" href="dashboard.php">< Back to Dashboard</a>
</div>
</body>
</html>