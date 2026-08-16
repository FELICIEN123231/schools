<?php
session_start();

// If already logged in, redirect to the appropriate dashboard
if (isset($_SESSION['id'])) {
    if (($_SESSION['role'] ?? 'User') === 'Admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit;
}

include "connection.php";

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $password = md5($_POST['password'] ?? '');

    if ($username === '' || $_POST['password'] === '') {
        $error = "Please enter username and password.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE username = ? AND password = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            // Check if account is approved by admin
            $status = $row['status'] ?? 'Approved';
            if (($status ?? '') === 'Pending') {
                $error = "Your account is pending approval. Please wait for an administrator to approve your account before logging in.";
            } else {
                // Store user information in session
                $_SESSION['id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['role'] = $row['role'] ?? 'User';

                // Redirect based on role
                if ($_SESSION['role'] === 'Admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: user_dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Invalid username or password!";
        }
        if (isset($stmt)) mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: Arial, sans-serif;
    background-color: #f4f6f9;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.login-container {
    width: 400px;
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
.login-container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
}
.login-container label {
    font-weight: bold;
    color: #444;
}
.login-container input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}
.login-container input:focus {
    border-color: #007bff;
    outline: none;
}
.login-container button {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}
.login-container button:hover {
    background-color: #0056b3;
}
.login-container p {
    text-align: center;
    margin-top: 20px;
    color: #555;
}
.login-container a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}
.login-container a:hover {
    text-decoration: underline;
}
.alert {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
    background: #ffe0e0;
    color: #9b1c1c;
}
</style>
</head>
<body>
<div class="login-container">
<h2>Login</h2>

<?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <label>Username</label>
    <input type="text" name="username" required>
    <br><br>

    <label>Password</label>
    <input type="password" name="password" required>
    <br><br>

    <button type="submit" name="login">Login</button>
</form>
<p>
    Don't have an account?
    <a href="createaccount.php">Sign Up</a>
</p>
</div>
</body>
</html>