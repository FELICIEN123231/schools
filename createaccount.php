<?php
session_start();
include 'connection.php';

$error = "";
$message = "";

if (isset($_POST['signup'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validation
    if ($fullname === '' || $username === '' || $email === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if username or email already exists
        $check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username or email already exists!";
        } else {
            mysqli_stmt_close($check);

            // Store a random placeholder password (account is inactive until approved).
            // The admin will set the default password (student@123) upon approval.
            $placeholder_password = bin2hex(random_bytes(16));
            $hashed_password = md5($placeholder_password);

            // Insert user into database with 'User' role and 'Pending' status
            $user_role = 'User';
            $status = 'Pending';
            $stmt = mysqli_prepare($conn,
                "INSERT INTO user (fullname, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssssss", $fullname, $username, $email, $hashed_password, $user_role, $status);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Account created successfully! Your account is pending approval. You will be able to login once an admin approves your account.";
                // Clear form
                $fullname = $username = $email = "";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up</title>
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
.signup-container {
    width: 400px;
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}
.signup-container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
}
.signup-container label {
    font-weight: bold;
    color: #444;
}
.signup-container input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}
.signup-container input:focus {
    border-color: #007bff;
    outline: none;
}
.signup-container button {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}
.signup-container button:hover {
    background-color: #0056b3;
}
.signup-container p {
    text-align: center;
    margin-top: 20px;
    color: #555;
}
.signup-container a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}
.signup-container a:hover {
    text-decoration: underline;
}
.alert {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
}
.alert.success {
    background: #d9f5df;
    color: #176b2c;
}
.alert.error {
    background: #ffe0e0;
    color: #9b1c1c;
}
</style>
</head>
<body>
<div class="signup-container">
<h2>Create Account</h2>
<p style="color:#666; font-size:14px; margin-bottom:15px;">Your account will be pending approval by an administrator. You will receive the default password <strong>student@123</strong> once approved.</p>

<?php if ($message): ?>
    <div class="alert success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <label>Full Name</label>
    <input type="text" name="fullname" value="<?= htmlspecialchars($fullname ?? '') ?>" required>
    <br><br>

    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
    <br><br>

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
    <br><br>

    <button type="submit" name="signup">Sign Up</button>
</form>
<p>
    Already have an account?
    <a href="login.php">Login</a>
</p>
</div>
</body>
</html>