<?php
require_once "auth.php";
require_once "connection.php";

// Only admins can manage users
if (($_SESSION['role'] ?? 'User') !== 'Admin') {
    header("Location: index.php");
    exit;
}

$message = "";
$error = "";

// Handle Add User
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_user'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_role = $_POST['user_role'] ?? 'User';

    if ($fullname === '' || $username === '' || $email === '' || $password === '') {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check duplicate
        $check = mysqli_prepare($conn, "SELECT id FROM user WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($check, "ss", $username, $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Username or email already exists!";
            mysqli_stmt_close($check);
        } else {
            mysqli_stmt_close($check);

            $hashed = md5($password);
            $status = 'Approved'; // Admins directly create approved accounts
            $stmt = mysqli_prepare($conn,
                "INSERT INTO user (fullname, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssssss", $fullname, $username, $email, $hashed, $user_role, $status);

            if (mysqli_stmt_execute($stmt)) {
                $message = "User '$username' registered successfully!";
            } else {
                $error = "Failed to register user: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id > 0) {
        // Prevent deleting yourself
        if ($delete_id === (int)$_SESSION['id']) {
            $error = "You cannot delete your own account.";
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM user WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $delete_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $message = "User deleted successfully.";
        }
    }
}

// Handle Approve User - Sets status to Approved and password to student@123
if (isset($_GET['approve'])) {
    $approve_id = (int)$_GET['approve'];
    if ($approve_id > 0) {
        $default_password = md5('student@123');
        $stmt = mysqli_prepare($conn,
            "UPDATE user SET status = 'Approved', password = ? WHERE id = ? AND status = 'Pending'"
        );
        mysqli_stmt_bind_param($stmt, "si", $default_password, $approve_id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_affected_rows($conn);
        mysqli_stmt_close($stmt);

        if ($affected > 0) {
            $message = "User approved successfully. Default password set to <strong>student@123</strong>.";
        } else {
            $error = "User could not be approved. They may already be approved or not exist.";
        }
    }
}

// Get all users
$users = mysqli_query($conn, "SELECT * FROM user ORDER BY id DESC");

// Count pending users
$pending_count = 0;
$pending_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE status = 'Pending'");
if ($pending_result) {
    $pending_count = (int)mysqli_fetch_assoc($pending_result)['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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

    <div class="page-title">
        <h2>Manage Users
            <?php if ($pending_count > 0): ?>
                <span class="pending-badge"><?= $pending_count ?> pending approval</span>
            <?php endif; ?>
        </h2>
        <a class="btn" href="admin_users.php#add-user">+ Add User</a>
    </div>

    <?php if ($message): ?>
        <div class="alert success"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="form-box" id="add-user">
        <h2>Register a New User</h2>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="fullname" placeholder="Enter full name" required>

            <label>Username</label>
            <input type="text" name="username" placeholder="Choose a username" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="user@example.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Min 6 characters" required>

            <label>Role</label>
            <select name="user_role" required>
                <option value="">-- Select Role --</option>
                <option value="Admin">Admin</option>
                <option value="Editor">Editor</option>
                <option value="User">User</option>
            </select>

            <button class="btn" type="submit" name="add_user">Register User</button>
        </form>
    </div>

    <!-- Users List -->
    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($users && mysqli_num_rows($users) > 0): ?>
                <?php $n = 1; while ($user = mysqli_fetch_assoc($users)): ?>
                    <?php $status = $user['status'] ?? 'Approved'; ?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td><?= htmlspecialchars($user['fullname']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role'] ?? 'User') ?></td>
                        <td>
                            <?php if ($status === 'Pending'): ?>
                                <span class="status-pending">Pending</span>
                            <?php else: ?>
                                <span class="status-approved">Approved</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <?php if ((int)$user['id'] !== (int)$_SESSION['id']): ?>
                                <?php if ($status === 'Pending'): ?>
                                    <a class="approve" href="admin_users.php?approve=<?= $user['id'] ?>"
                                       onclick="return confirm('Approve <?= htmlspecialchars($user['username']) ?>? Their default password will be set to student@123.');">Approve</a>
                                <?php endif; ?>
                                <a class="edit" href="admin_user_edit.php?id=<?= $user['id'] ?>">Edit</a>
                                <a class="delete" href="admin_users.php?delete=<?= $user['id'] ?>"
                                   onclick="return confirm('Delete this user?');">Delete</a>
                            <?php else: ?>
                                <span style="color:#888">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="empty">No users found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>