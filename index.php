<?php
session_start();
include("config.php");

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST["username"]));
    $password = $_POST["password"];
    $role = mysqli_real_escape_string($conn, trim($_POST["role"]));

    $sql = "SELECT * FROM users WHERE username='$username' AND role='$role' AND status='Active'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION["user_id"] = $row["user_id"];
            $_SESSION["full_name"] = $row["full_name"];
            $_SESSION["role"] = $row["role"];

            if ($row["role"] == "Admin") {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: user_dashboard.php");
                exit();
            }
        } else {
            $error = "Wrong password.";
        }
    } else {
        $error = "User not found, wrong role, or inactive account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment Rental System - Login</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body class="index-page">
<div class="container">
    <div class="box"> 
    <h2>Equipment Rental System  Login</h2>
    <?php if ($error != ""): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="index.php">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Role</label>
        <select name="role" required>
            <option value="Admin">Admin</option>
            <option value="User">User</option>
        </select>
        <button type="submit">Login</button>
    </form>
    </div>
</div>
</body>
</html>