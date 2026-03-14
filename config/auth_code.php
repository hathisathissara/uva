<?php
session_start();
include('db.php');

if (isset($_POST['login_btn'])) {
    // Sanitize input to prevent basic SQL injection
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Check if email exists
    $login_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $login_query_run = mysqli_query($conn, $login_query);

    if (mysqli_num_rows($login_query_run) > 0) {
        // Fetch data
        $data = mysqli_fetch_array($login_query_run);

        // Verify Password (Compares input password with the Hash in DB)
        $hashed_password = $data['password_hash'];

        if (password_verify($password, $hashed_password)) {
            // Login Success: Set Session Variables
            $_SESSION['auth'] = true;
            $_SESSION['auth_role'] = $data['role']; // 'admin', 'lecturer', 'student'
            $_SESSION['auth_user'] = [
                'user_id' => $data['user_id'],
                'full_name' => $data['full_name'],
                'email' => $data['email']
            ];

            // Redirect based on Role
            if ($data['role'] == 'admin') {
                $_SESSION['message'] = "Welcome to Admin Dashboard";
                header("Location: ../admin/index.php");
            } elseif ($data['role'] == 'lecturer') {
                $_SESSION['message'] = "Welcome Lecturer";
                header("Location: ../lecturer/index.php");
            } elseif ($data['role'] == 'student') {
                $_SESSION['message'] = "Welcome Student";
                header("Location: ../student/index.php");
            }
            exit(0);
        } else {
            // Password Wrong
            $_SESSION['message'] = "Invalid Password";
            header("Location: ../login.php");
            exit(0);
        }
    } else {
        // Email Wrong
        $_SESSION['message'] = "Invalid Email Address";
        header("Location: ../login.php");
        exit(0);
    }
} else {
    $_SESSION['message'] = "You are not allowed to access this file";
    header("Location: ../login.php");
    exit(0);
}
