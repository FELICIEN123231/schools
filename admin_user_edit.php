<?php
require_once "auth.php";
require_once "connection.php";

// Only admins can manage users
if (($_SESSION['role'] ?? 'User') !== 'Admin') {
    header("Location: index.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    die("Invalid user ID.");
}

$stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    die("User not found.");
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $user_role = $_POST["user_role"] ?? "User";
    $new_password = $_POST["new_password"] ?? "";
    $status = $_POST["status"] ?? "Approved";

    if ($fullname === "" || $username === "" || $email === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($new_password !== "" && strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check for duplicate username/email excluding current user
        $check = mysqli_prepare($conn, "SELECT id FROM user WHERE (username = ? OR email = ?) AND id != ?");
        mysqli_stmt_bind_param($check, "ssi", $username, $email, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username or email already exists.";
            mysqli_stmt_close($check);
        } else {
            mysqli_stmt_close($check);

            if ($new_password !== "") {
                // Update with new password
                $hashed = md5($new_password);
                $stmt = mysqli_prepare($conn,
                    "UPDATE user SET fullname=?, username=?, email=?, password=?, role=?, status=? WHERE id=?"
                );
                mysqli_stmt_bind_param($stmt, "ssssssi", $fullname, $username, $email, $hashed, $user_role, $status, $id);
            } else {
                // Update without changing password
                $stmt = mysqli_prepare($conn,
                    "UPDATE user SET fullname=?, username=?, email=?, role=?, status=? WHERE id=?"
                );
                mysqli_stmt_bind_param($stmt, "sssssi", $fullname, $username, $email, $user_role, $status, $id);
            }

            if (mysqli_stmt_execute($stmt)) {
                $message = "User updated successfully.";
                $user["fullname"] = $fullname;
                $user["username"] = $username;
                $user["email"] = $email;
                $user["role"] = $user_role;
                $user["status"] = $status;
            } else {
                $error = "Update failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="container nav">
        <h1>Student Registration System</h1>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="student_view.php">Students</a>
            <a href="department_view.php">Departments</a>
            <a href="admin_users.php">Users</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="form-box">
        <h2>Edit User: <?= htmlspecialchars($user['username']) ?></h2>

        <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>

            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Role</label>
            <select name="user_role" required>
                <option value="">-- Select Role --</option>
                <?php foreach (["Admin", "Editor", "User"] as $r): ?>
                    <option value="<?= $r ?>" <?= ($user['role'] ?? 'User') === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
            </select>

            <label>Status</label>
            <select name="status" required>
                <option value="">-- Select Status --</option>
                <option value="Approved" <?= ($user['status'] ?? 'Approved') === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Pending" <?= ($user['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
            </select>

            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="new_password" placeholder="Enter new password (min 6 chars)">

            <button class="btn" type="submit">Update User</button>
            <a class="btn secondary" href="admin_users.php">Cancel</a>
        </form>
    </div>
</main>

</body>
</html>