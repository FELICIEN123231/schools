<?php
require_once "auth.php";
require_once "connection.php";

$is_admin = ($_SESSION['role'] ?? 'User') === 'Admin';
$error = "";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $department_name = trim($_POST["department_name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($department_name === "") {
        $error = "Department name is required.";
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO department (department_name, description) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ss", $department_name, $description);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Department added successfully.";
        } else {
            $error = mysqli_errno($conn) == 1062
                ? "That department already exists."
                : "Could not add department: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Department</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container nav">
        <h1>Student Registration System</h1>
        <nav>
            <a href="<?= $is_admin ? 'admin_dashboard.php' : 'user_dashboard.php' ?>">Home</a>
            <a href="student_view.php">Students</a>
            <a href="department_view.php">Departments</a>
            <?php if ($is_admin): ?>
                <a href="admin_users.php">Users</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="form-box">
        <h2>Add Department</h2>

        <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <label>Department Name</label>
            <input type="text" name="department_name" placeholder="e.g. Computer Science" required>

            <label>Description</label>
            <textarea name="description" placeholder="Enter department description"></textarea>

            <button class="btn" type="submit">Add Department</button>
            <a class="btn secondary" href="department_view.php">View Departments</a>
        </form>
    </div>
</main>
</body>
</html>