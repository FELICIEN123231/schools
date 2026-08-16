<?php
require_once "auth.php";
require_once "connection.php";

$is_admin = ($_SESSION['role'] ?? 'User') === 'Admin';
$result = mysqli_query($conn,
    "SELECT s.*, d.department_name
     FROM students s
     INNER JOIN department d ON s.department_id = d.id
     ORDER BY s.id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Students</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container nav">
        <h1>Student Registration System</h1>
        <nav>
            <a href="<?= $is_admin ? 'admin_dashboard.php' : 'user_dashboard.php' ?>">Home</a>
            <a href="student_add.php">Register Student</a>
            <a href="department_view.php">Departments</a>
            <?php if ($is_admin): ?>
                <a href="admin_users.php">Users</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="page-title">
        <h2>Registered Students</h2>
        <a class="btn" href="student_add.php">+ Add Student</a>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>DOB</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php $n = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['gender']) ?></td>
                        <td><?= htmlspecialchars($row['dob']) ?></td>
                        <td><?= htmlspecialchars($row['department_name']) ?></td>
                        <td><?= htmlspecialchars($row['role'] ?? 'Student') ?></td>
                        <td class="actions">
                            <a class="edit" href="student_edit.php?id=<?= $row['id'] ?>">Edit</a>
                            <a class="delete" href="student_delete.php?id=<?= $row['id'] ?>"
                               onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="empty">No students registered yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>