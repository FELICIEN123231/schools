<?php
require_once "auth.php";
require_once "connection.php";

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {
    // Check if department has students
    $check = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM students WHERE department_id = ?");
    mysqli_stmt_bind_param($check, "i", $id);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($check);

    if ((int)$row['cnt'] > 0) {
        // Department has students - cannot delete
        header("Location: department_view.php?error=has_students");
        exit;
    }

    // Safe to delete
    $stmt = mysqli_prepare($conn, "DELETE FROM department WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header("Location: department_view.php");
exit;
?>