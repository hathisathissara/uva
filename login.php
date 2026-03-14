<?php 
session_start(); 
if(isset($_SESSION['auth'])){
    // Already logged in? Redirect accordingly
    if($_SESSION['auth_role'] == 'admin') header("Location: admin/index.php");
    elseif($_SESSION['auth_role'] == 'lecturer') header("Location: lecturer/index.php");
    elseif($_SESSION['auth_role'] == 'student') header("Location: student/index.php");
    exit(0);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ICT Department UWU</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="glass-card text-center">

        <!-- Logo or Icon -->
        <div class="mb-4">
            <i class="fas fa-laptop-code fa-3x text-white"></i>
        </div>

        <div class="login-header mb-4">
            <h3>ICT DEPARTMENT</h3>
            <p>UWU Management System</p>
        </div>

        <!-- Error Message Display -->
        <?php include('message.php'); ?>

        <form action="config/auth_code.php" method="POST">

            <div class="input-group mb-4">
                <input type="email" name="email" class="form-control" placeholder="University Email" required>
            </div>

            <div class="input-group mb-3">
                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Password" required>
                <button class="btn btn-glass" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
            </div>

            <button type="submit" name="login_btn" class="btn btn-login shadow">LOGIN SYSTEM</button>

        </form>

        <div class="mt-4">
            <a href="#" class="text-white text-decoration-none small" style="opacity: 0.7;">Forgot Password?</a>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="text-white text-decoration-none small">Back to Home</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#passwordInput');
        const icon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function (e) {
            // 1. Toggle the type attribute (password <-> text)
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // 2. Toggle the eye icon (fa-eye <-> fa-eye-slash)
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>

</body>

</html>