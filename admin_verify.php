<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_code = $_POST['admin_code'];

    // Hardcoded admin code verification
    if ($admin_code === '@82109') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_page.php");
        exit();
    } else {
        echo "<script>alert('Incorrect admin code.'); window.location.href='admin_login.php';</script>";
    }
}
?>
