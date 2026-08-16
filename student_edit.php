<?php
require_once "auth.php";
require_once "connection.php";

$is_admin = ($_SESSION['role'] ?? 'User') === 'Admin';
$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    die("Invalid student ID.");
}

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    die("Student not found.");
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST["student_id"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $gender = $_POST["gender"] ?? "";
    $dob = $_POST["dob"] ?? "";
    $department_id = (int)($_POST["department_id"] ?? 0);
    $role = $_POST["role"] ?? "Student";

    if ($student_id === "" || $name === "" || $email === "" || $phone === "" ||
        $gender === "" || $dob === "" || $department_id <= 0 || $role === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check for duplicate student_id or email (excluding current record)
        $check = mysqli_prepare($conn,
            "SELECT id FROM students WHERE (student_id = ? OR email = ?) AND id != ?"
        );
        mysqli_stmt_bind_param($check, "ssi", $student_id, $email, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $error = "Student ID or email already exists.";
            mysqli_stmt_close($check);
        } else {
            mysqli_stmt_close($check);

            $stmt = mysqli_prepare($conn,
                "UPDATE students
                 SET student_id=?, name=?, email=?, phone=?, gender=?, dob=?, department_id=?, role=?
                 WHERE id=?"
            );
            mysqli_stmt_bind_param($stmt, "ssssssisi",
                $student_id, $name, $email, $phone, $gender, $dob, $department_id, $role, $id
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Student updated successfully.";
                $student = array_merge($student, [
                    "student_id"=>$student_id, "name"=>$name, "email"=>$email,
                    "phone"=>$phone, "gender"=>$gender, "dob"=>$dob,
                    "department_id"=>$department_id, "role"=>$role
                ]);
            } else {
                $error = mysqli_errno($conn) == 1062
                    ? "Student ID or email already exists."
                    : "Update failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$departments = mysqli_query($conn, "SELECT id, department_name FROM department ORDER BY department_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
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
        <h2>Edit Student</h2>

        <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <label>Student ID</label>
            <input type="text" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>" required>

            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>

            <label>Gender</label>
            <select name="gender" required>
                <option value="">-- Select Gender --</option>
                <?php foreach (["Male","Female","Other"] as $g): ?>
                    <option value="<?= $g ?>" <?= $student['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
            </select>

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= htmlspecialchars($student['dob']) ?>" required>

            <label>Role</label>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <?php foreach (["Student","Teacher","Administrator","Staff"] as $r): ?>
                    <option value="<?= $r ?>" <?= ($student['role'] ?? 'Student') === $r ? 'selected' : '' ?>><?= $r ?></option>
                <?php endforeach; ?>
            </select>

            <label>Department</label>
            <select name="department_id" required>
                <option value="">-- Select Department --</option>
                <?php while ($department = mysqli_fetch_assoc($departments)): ?>
                    <option value="<?= $department['id'] ?>"
                        <?= (int)$student['department_id'] === (int)$department['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($department['department_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button class="btn" type="submit">Update Student</button>
            <a class="btn secondary" href="student_view.php">Cancel</a>
        </form>
    </div>
</main>
</body>
</html>