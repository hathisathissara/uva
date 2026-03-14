<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if(!isset($_SESSION['auth'])){
    $_SESSION['message'] = "Login to access dashboard";
    header("Location: ../login.php"); 
    exit(0);
}

// Database එක සම්බන්ධ කරගමු (ප්‍රොෆයිල් පික්චර් එක DB එකෙන් ගන්න)
include_once('../config/db.php');

$role = $_SESSION['auth_role'];
$user_id = $_SESSION['auth_user']['user_id'];

// DB එකෙන් යූසර්ගේ අලුත්ම විස්තර (නම සහ ෆොටෝ) ගමු
$header_query = "SELECT full_name, profile_pic, theme FROM users WHERE user_id='$user_id' LIMIT 1";
$header_query_run = mysqli_query($conn, $header_query);
$user_row = mysqli_fetch_array($header_query_run);

$user_name = $user_row['full_name'];
$profile_pic = $user_row['profile_pic'];
$user_theme = isset($user_row['theme']) ? $user_row['theme'] : 'light';
$initials = strtoupper(substr($user_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<!-- includes/header.php ඇතුළේ -->
<body class="<?= ($user_theme == 'dark') ? 'dark-mode' : ''; ?>">

<div class="d-flex" id="wrapper">

    <!-- ==================== SIDEBAR ==================== -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <i class="fas fa-laptop-code me-2"></i> ICT Department UWU 
        </div>
        
        <div class="list-group list-group-flush mt-3">
            
            <!-- ADMIN MENU -->
            <?php if($role == 'admin') : ?>
                <div class="small text-uppercase fw-bold text-muted px-4 mb-2 mt-2" style="font-size:0.7rem;">Admin Tools</div>
                <a href="../admin/index.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt me-3"></i> Dashboard</a>
                <a href="../admin/users.php" class="list-group-item list-group-item-action"><i class="fas fa-users me-3"></i> Users</a>
                <a href="../admin/courses.php" class="list-group-item list-group-item-action"><i class="fas fa-book me-3"></i> Courses</a>
                <a href="../admin/classes.php" class="list-group-item list-group-item-action"><i class="fas fa-chalkboard me-3"></i> Manage Classes</a>
                <a href="../admin/enrollments.php" class="list-group-item list-group-item-action"><i class="fas fa-user-check me-3"></i> Enrollments</a>
                <a href="../admin/labs.php" class="list-group-item list-group-item-action"><i class="fas fa-desktop me-3"></i> Labs</a>
                <a href="../admin/notices.php" class="list-group-item list-group-item-action"><i class="fas fa-bullhorn me-3"></i> Notices</a>
                <?php endif; ?>

            <!-- LECTURER MENU -->
            <?php if($role == 'lecturer') : ?>
                <div class="small text-uppercase fw-bold text-muted px-4 mb-2 mt-2" style="font-size:0.7rem;">Academic</div>
                <a href="../lecturer/index.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt me-3"></i> Dashboard</a>
                <a href="../lecturer/my_classes.php" class="list-group-item list-group-item-action"><i class="fas fa-chalkboard-teacher me-3"></i> Classes</a>
                <a href="../lecturer/upload_notes.php" class="list-group-item list-group-item-action"><i class="fas fa-cloud-upload-alt me-3"></i> Uploads</a>
                <a href="../lecturer/add_results.php" class="list-group-item list-group-item-action"><i class="fas fa-marker me-3"></i> Results</a>
                <a href="../lecturer/attendance.php" class="list-group-item list-group-item-action"><i class="fas fa-calendar-check me-3"></i> Attendance</a>
                <a href="../lecturer/fyp_requests.php" class="list-group-item list-group-item-action"><i class="fas fa-project-diagram me-3"></i> FYP</a>
            <?php endif; ?>

            <!-- STUDENT MENU -->
            <?php if($role == 'student') : ?>
                <div class="small text-uppercase fw-bold text-muted px-4 mb-2 mt-2" style="font-size:0.7rem;">Student Area</div>
                <a href="../student/index.php" class="list-group-item list-group-item-action"><i class="fas fa-tachometer-alt me-3"></i> Dashboard</a>
                <a href="../student/my_results.php" class="list-group-item list-group-item-action"><i class="fas fa-graduation-cap me-3"></i> Results</a>
                <a href="../student/downloads.php" class="list-group-item list-group-item-action"><i class="fas fa-download me-3"></i> Notes</a>
                <a href="../student/submit_fyp.php" class="list-group-item list-group-item-action"><i class="fas fa-file-upload me-3"></i> FYP</a>
            <?php endif; ?>

            <div class="mt-auto mb-4 border-top pt-2">
                <a href="../logout.php" class="list-group-item list-group-item-action text-danger fw-bold">
                    <i class="fas fa-sign-out-alt me-3"></i> Logout
                </a>
            </div>
            
        </div>
    </div>

    <!-- ==================== PAGE CONTENT WRAPPER ==================== -->
    <div id="page-content-wrapper">

        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg border-bottom bg-white shadow-sm">
            <div class="container-fluid">
                
                <!-- Toggle Button -->
                <button class="btn btn-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Right Side Profile -->
                <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle p-0" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="user-profile-box d-flex align-items-center">
                                <div class="text-end d-none d-sm-block me-2" style="line-height: 1.2;">
                                    <span class="d-block fw-bold text-dark small"><?= $user_name; ?></span>
                                    <span class="d-block text-muted" style="font-size: 0.7rem;"><?= ucfirst($role); ?></span>
                                </div>
                                
                                <!-- Profile Picture Logic -->
                                <?php if($profile_pic != 'default.png' && $profile_pic != ''): ?>
                                    <img src="../uploads/profile/<?= $profile_pic; ?>" class="rounded-circle border" style="width:38px; height:38px; object-fit:cover;">
                                <?php else: ?>
                                    <div class="user-avatar bg-primary text-white rounded-circle d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width:38px; height:38px;">
                                        <?= $initials; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdown">
                            <!-- Link to Profile.php -->
                            <a class="dropdown-item py-2" href="profile.php">
                                <i class="fas fa-user-circle fa-sm fa-fw me-2 text-gray-400"></i> My Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger py-2" href="../logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
 <!-- ==================== SCROLLING NOTICE MARQUEE ==================== -->
        <?php
        // අලුත්ම නෝටිස් 5ක් ගන්නවා
        $marquee_query = "SELECT title FROM notices ORDER BY id DESC LIMIT 5";
        $marquee_run = mysqli_query($conn, $marquee_query);

        if(mysqli_num_rows($marquee_run) > 0) {
            echo '<div class="notice-marquee shadow-sm">';
            echo '<marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">';
            echo '<i class="fas fa-bullhorn me-2 text-danger"></i> LATEST NOTICES: ';
            
            while($m_row = mysqli_fetch_array($marquee_run)) {
                echo '<span class="mx-4"> <i class="fas fa-circle fa-xs text-warning me-2"></i> ' . $m_row['title'] . '</span>';
            }
            
            echo '</marquee>';
            echo '</div>';
        }
        ?>
        
        <!-- Main Content Container Starts Here -->
        <div class="container-fluid px-4 py-4">