<?php
include 'admin_dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = $_POST['exam_name'];
    $exam_date = $_POST['exam_date'];

    $stmt = $conn->prepare("DELETE FROM results WHERE exam_name = ? AND result_date = ?");
    $stmt->bind_param("ss", $exam_name, $exam_date);

    if ($stmt->execute()) {
        echo "<script>alert('Result deleted successfully!'); window.location.href='admin control.php';</script>";
    } else {
        echo "<script>alert('Failed to delete result. Try again.'); window.location.href='admin control.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>