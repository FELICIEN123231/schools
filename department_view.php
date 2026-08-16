<?php
require_once "auth.php";
require_once "connection.php";

$is_admin = ($_SESSION['role'] ?? 'User') === 'Admin';
$error = isset($_GET['error']) && $_GET['error'] === 'has_students'
    ? "Cannot delete department: it has students assigned to it."
    : "";

$result = mysqli_query($conn,
    "SELECT d.*, COUNT(s.id) AS student_count
     FROM department d
     LEFT JOIN students s ON d.id = s.department_id
     GROUP BY d.id
     ORDER BY d.department_name"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container nav">
        <h1>Student Registration System</h1>
        <nav>
            <a href="<?= $is_admin ? 'admin_dashboard.php' : 'user_dashboard.php' ?>">Home</a>
            <a href="student_add.php">Register Student</a>
            <a href="student_view.php">Students</a>
            <?php if ($is_admin): ?>
                <a href="admin_users.php">Users</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="page-title">
        <h2>Departments</h2>
        <a class="btn" href="department_add.php">+ Add Department</a>
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Description</th>
                    <th>Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php $n=1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td><?= htmlspecialchars($row['department_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= $row['student_count'] ?></td>
                        <td class="actions">
                            <a class="edit" href="department_edit.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="delete" href="department_delete.php?id=<?= $row['id'] ?>"
                               onclick="return confirm('Delete this department? It can only be deleted if it has no students.');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="empty">No departments available.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>