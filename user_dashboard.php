<?php
require_once "auth.php";
require_once "connection.php";

// Redirect admins to their dedicated dashboard
if (($_SESSION['role'] ?? 'User') === 'Admin') {
    header("Location: admin_dashboard.php");
    exit;
}

$is_editor = ($_SESSION['role'] ?? 'User') === 'Editor';
$student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students"))['total'];
$department_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM department"))['total'];

// Recent students
$recent_students = mysqli_query($conn,
    "SELECT s.*, d.department_name
     FROM students s
     INNER JOIN department d ON s.department_id = d.id
     ORDER BY s.id DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container nav">
        <h1>Student Registration System</h1>
        <nav>
            <a href="user_dashboard.php">Home</a>
            <a href="student_view.php">Students</a>
            <a href="department_view.php">Departments</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="hero">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']) ?>!</h2>
        <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['role'] ?? 'User') ?></strong>.
           Browse students and departments below.</p>
        <a class="btn" href="student_view.php">View Students</a>
    </section>

    <section class="cards">
        <div class="card">
            <h3>Registered Students</h3>
            <p class="number"><?= htmlspecialchars($student_count) ?></p>
            <a href="student_view.php">View Students</a>
        </div>
        <div class="card">
            <h3>Departments</h3>
            <p class="number"><?= htmlspecialchars($department_count) ?></p>
            <a href="department_view.php">View Departments</a>
        </div>
    </section>

    <section class="panel" style="background:#fff;padding:20px;border-radius:10px;box-shadow:0 3px 15px rgba(0,0,0,0.08);margin-bottom:40px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h2 style="color:#123b63;">Recent Students</h2>
            <a href="student_view.php" style="color:#1769aa;text-decoration:none;">View all</a>
        </div>
        <div class="table-box" style="margin-bottom:0;">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Department</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recent_students && mysqli_num_rows($recent_students) > 0): ?>
                    <?php while ($s = mysqli_fetch_assoc($recent_students)): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['student_id']) ?></td>
                            <td><?= htmlspecialchars($s['department_name']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="empty">No students registered yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer>
    <p>&copy; Group 6 Design thinking and didactic materal development.</p>
</footer>
</body>
</html>
