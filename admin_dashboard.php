<?php
require_once "auth.php";
require_once "connection.php";

// Only admins can access the admin dashboard
if (($_SESSION['role'] ?? 'User') !== 'Admin') {
    header("Location: user_dashboard.php");
    exit;
}

// Statistics for the admin dashboard
$student_count    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students"))['total'];
$department_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM department"))['total'];
$user_count       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user"))['total'];
$admin_count      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role='Admin'"))['total'];
$editor_count     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role='Editor'"))['total'];
$regular_count    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user WHERE role != 'Admin' AND role != 'Editor'"))['total'];

// Recent students (latest 5)
$recent_students = mysqli_query($conn,
    "SELECT s.*, d.department_name
     FROM students s
     INNER JOIN department d ON s.department_id = d.id
     ORDER BY s.id DESC LIMIT 5"
);

// Recent users (latest 5)
$recent_users = mysqli_query($conn, "SELECT * FROM user ORDER BY id DESC LIMIT 5");

// Students per department (for admin insight)
$dept_stats = mysqli_query($conn,
    "SELECT d.department_name, COUNT(s.id) AS total
     FROM department d
     LEFT JOIN students s ON s.department_id = d.id
     GROUP BY d.id
     ORDER BY total DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<div class="admin-layout">

    <!-- ===== Sidebar ===== -->
    <aside class="sidebar">
        <div class="brand">
            <h2>Admin Panel</h2>
            <p>Student Registration</p>
        </div>

        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="active">Dashboard</a>
            <a href="student_view.php">Manage Students</a>
            <a href="department_view.php">Manage Departments</a>
            <a href="admin_users.php">Manage Users</a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-mini">
                <div class="admin-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?></div>
                <div>
                    <strong><?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin') ?></strong>
                    <span>Administrator</span>
                </div>
            </div>
            <a class="logout-btn" href="logout.php">&larr; Logout</a>
        </div>
    </aside>

    <!-- ===== Main Content ===== -->
    <main class="admin-main">

        <!-- Top bar -->
        <div class="topbar">
            <h1>Dashboard Overview</h1>
            <div class="topbar-right">
                <span class="role-badge">Admin</span>
                <span class="date"><?= date('l, F j, Y') ?></span>
            </div>
        </div>

        <!-- Stat Cards -->
        <section class="stat-grid">
            <div class="stat-card blue">
                <div class="stat-icon">&#127891;</div>
                <div>
                    <span class="stat-label">Total Students</span>
                    <span class="stat-value"><?= htmlspecialchars($student_count) ?></span>
                </div>
                <a href="student_view.php">View all &rarr;</a>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">&#128194;</div>
                <div>
                    <span class="stat-label">Departments</span>
                    <span class="stat-value"><?= htmlspecialchars($department_count) ?></span>
                </div>
                <a href="department_view.php">Manage &rarr;</a>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">&#128101;</div>
                <div>
                    <span class="stat-label">Total Users</span>
                    <span class="stat-value"><?= htmlspecialchars($user_count) ?></span>
                </div>
                <a href="admin_users.php">Manage &rarr;</a>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">&#128274;</div>
                <div>
                    <span class="stat-label">Admins</span>
                    <span class="stat-value"><?= htmlspecialchars($admin_count) ?></span>
                </div>
                <a href="admin_users.php">View &rarr;</a>
            </div>

            <div class="stat-card teal">
                <div class="stat-icon">&#9997;&#65039;</div>
                <div>
                    <span class="stat-label">Editors</span>
                    <span class="stat-value"><?= htmlspecialchars($editor_count) ?></span>
                </div>
                <a href="admin_users.php">View &rarr;</a>
            </div>

            <div class="stat-card gray">
                <div class="stat-icon">&#128107;</div>
                <div>
                    <span class="stat-label">Regular Users</span>
                    <span class="stat-value"><?= htmlspecialchars($regular_count) ?></span>
                </div>
                <a href="admin_users.php">View &rarr;</a>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="qa-grid">
                <a class="qa-btn primary" href="student_add.php">+ Add Student</a>
                <a class="qa-btn" href="student_view.php">View Students</a>
                <a class="qa-btn" href="department_add.php">+ Add Department</a>
                <a class="qa-btn" href="admin_users.php">+ Add User</a>
            </div>
        </section>

        <!-- Two-column: Recent Students & Recent Users -->
        <section class="dashboard-grid">

            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Registrations</h2>
                    <a href="student_view.php">View all</a>
                </div>
                <div class="table-wrap">
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
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h2>Recent Users</h2>
                    <a href="admin_users.php">View all</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($recent_users && mysqli_num_rows($recent_users) > 0): ?>
                            <?php while ($u = mysqli_fetch_assoc($recent_users)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td>
                                        <span class="role-tag <?= strtolower($u['role'] ?? 'user') ?>">
                                            <?= htmlspecialchars($u['role'] ?? 'User') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="empty">No users found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <!-- Department breakdown -->
        <section class="panel">
            <div class="panel-header">
                <h2>Students per Department</h2>
            </div>
            <div class="dept-list">
                <?php if ($dept_stats && mysqli_num_rows($dept_stats) > 0): ?>
                    <?php $max = 1;
                          while ($row = mysqli_fetch_assoc($dept_stats)) {
                              if ((int)$row['total'] > $max) $max = (int)$row['total'];
                          }
                          mysqli_data_seek($dept_stats, 0); ?>
                    <?php while ($row = mysqli_fetch_assoc($dept_stats)): ?>
                        <?php $pct = $max > 0 ? round(((int)$row['total'] / $max) * 100) : 0; ?>
                        <div class="dept-row">
                            <span class="dept-name"><?= htmlspecialchars($row['department_name']) ?></span>
                            <div class="dept-bar-bg">
                                <div class="dept-bar" style="width: <?= $pct ?>%"></div>
                            </div>
                            <span class="dept-count"><?= (int)$row['total'] ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="empty">No department data available.</p>
                <?php endif; ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>
