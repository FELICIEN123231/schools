<?php
require_once "auth.php";
require_once "connection.php";

$is_admin = ($_SESSION['role'] ?? 'User') === 'Admin';
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

    // Validate fields
    if (
        $student_id === "" ||
        $name === "" ||
        $email === "" ||
        $phone === "" ||
        $gender === "" ||
        $dob === "" ||
        $department_id <= 0 ||
        $role === ""
    ) {
        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // Check whether student ID already exists
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM students WHERE student_id = ? OR email = ?"
        );

        mysqli_stmt_bind_param(
            $check,
            "ss",
            $student_id,
            $email
        );

        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {

            $error = "Student ID or email already exists.";

            mysqli_stmt_close($check);

        } else {

            mysqli_stmt_close($check);

            // Insert student
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO students
                (student_id, name, email, phone, gender, dob, department_id, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                $error = "Database error: " . mysqli_error($conn);
            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssssis",
                    $student_id,
                    $name,
                    $email,
                    $phone,
                    $gender,
                    $dob,
                    $department_id,
                    $role
                );

                if (mysqli_stmt_execute($stmt)) {

                    $message = "Student registered successfully.";

                } else {

                    $error = "Registration failed: " . mysqli_stmt_error($stmt);
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get departments
$departments = mysqli_query(
    $conn,
    "SELECT id, department_name
     FROM department
     ORDER BY department_name"
);

if (!$departments) {
    $error = "Unable to load departments: " . mysqli_error($conn);
}

// Get existing students for the "choose a student" dropdown
$students = mysqli_query(
    $conn,
    "SELECT s.id, s.student_id, s.name, s.email, s.phone, s.gender, s.dob, s.department_id, s.role
     FROM students s
     ORDER BY s.name"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register Student</title>

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

        <h2>Student Registration Form</h2>


        <?php if ($message): ?>

            <div class="alert success">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="">

            <label for="student_id">Student ID</label>

            <input
                type="text"
                id="student_id"
                name="student_id"
                placeholder="e.g. ST001"
                required
            >


            <label for="name">Full Name</label>

            <input
                type="text"
                id="name"
                name="name"
                placeholder="Enter full name"
                required
            >


            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="student@example.com"
                required
            >


            <label for="phone">Phone</label>

            <input
                type="text"
                id="phone"
                name="phone"
                placeholder="Enter phone number"
                required
            >


            <label for="gender">Gender</label>

            <select id="gender" name="gender" required>

                <option value="">-- Select Gender --</option>

                <option value="Male">Male</option>

                <option value="Female">Female</option>

                <option value="Other">Other</option>

            </select>


            <label for="dob">Date of Birth</label>

            <input
                type="date"
                id="dob"
                name="dob"
                required
            >


            <label for="department_id">Department</label>

            <select
                id="department_id"
                name="department_id"
                required
            >

                <option value="">Select Department</option>

                <?php if ($departments && mysqli_num_rows($departments) > 0): ?>

                    <?php while ($department = mysqli_fetch_assoc($departments)): ?>

                        <option value="<?= (int)$department['id'] ?>">
                            <?= htmlspecialchars($department['department_name']) ?>
                        </option>

                    <?php endwhile; ?>

                <?php else: ?>

                    <option value="">
                        No departments available
                    </option>

                <?php endif; ?>

            </select>


            <label for="role">Role</label>

            <select id="role" name="role" required>

                <option value="">Select Role </option>

                <option value="Student">Student</option>

            </select>


            <button class="btn" type="submit">
                Register Student
            </button>

            <a
                class="btn secondary"
                href="student_view.php"
            >
                View Students
            </a>

        </form>

    </div>

</main>

<script>
function prefillStudent(studentId) {
    if (!studentId) {
        // Clear the form
        document.getElementById('student_id').value = '';
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('gender').value = '';
        document.getElementById('dob').value = '';
        document.getElementById('department_id').value = '';
        document.getElementById('role').value = '';
        return;
    }

    var select = document.getElementById('select_student');
    var option = select.options[select.selectedIndex];

    document.getElementById('student_id').value = option.getAttribute('data-student_id');
    document.getElementById('name').value = option.getAttribute('data-name');
    document.getElementById('email').value = option.getAttribute('data-email');
    document.getElementById('phone').value = option.getAttribute('data-phone');
    document.getElementById('gender').value = option.getAttribute('data-gender');
    document.getElementById('dob').value = option.getAttribute('data-dob');
    document.getElementById('department_id').value = option.getAttribute('data-department_id');
    document.getElementById('role').value = option.getAttribute('data-role');
}
</script>

</body>
</html>
