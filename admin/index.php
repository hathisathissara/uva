<?php 
include('../includes/header.php'); 
include('../config/db.php');

// ==========================================
// 1. FETCH DATA FOR DASHBOARD CARDS
// ==========================================

// Get Total Students
$student_query = "SELECT COUNT(user_id) AS total FROM users WHERE role='student'";
$student_run = mysqli_query($conn, $student_query);
$total_students = mysqli_fetch_assoc($student_run)['total'];

// Get Total Lecturers
$lecturer_query = "SELECT COUNT(user_id) AS total FROM users WHERE role='lecturer'";
$lecturer_run = mysqli_query($conn, $lecturer_query);
$total_lecturers = mysqli_fetch_assoc($lecturer_run)['total'];

// Get Total Active Classes
$class_query = "SELECT COUNT(class_id) AS total FROM classes";
$class_run = mysqli_query($conn, $class_query);
$total_classes = mysqli_fetch_assoc($class_run)['total'];

// Get Total Lab Schedules
$lab_query = "SELECT COUNT(schedule_id) AS total FROM lab_schedules";
$lab_run = mysqli_query($conn, $lab_query);
$total_labs = mysqli_fetch_assoc($lab_run)['total'];
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Admin Dashboard</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Welcome to ICT Department UMS</li>
    </ol>

    <?php include('../message.php'); ?>

    <!-- ==========================================
         2. SUMMARY CARDS ROW
         ========================================== -->
    <div class="row">
        
        <!-- Total Students Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 border-0" style="border-left: 4px solid #4e73df !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 0.8rem;">
                                Total Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold fs-3"><?= $total_students; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Lecturers Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 border-0" style="border-left: 4px solid #1cc88a !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.8rem;">
                                Total Lecturers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold fs-3"><?= $total_lecturers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Classes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 border-0" style="border-left: 4px solid #36b9cc !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1" style="font-size: 0.8rem;">
                                Active Classes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold fs-3"><?= $total_classes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book-open fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lab Schedules Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 border-0" style="border-left: 4px solid #f6c23e !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" style="font-size: 0.8rem;">
                                Lab Bookings</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold fs-3"><?= $total_labs; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-laptop-code fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         3. RECENT ACTIVITY / USERS TABLE
         ========================================== -->
    <div class="row mt-3">
        <div class="col-lg-8 mb-4">
            <!-- Recent Users Card -->
            <div class="card shadow border-0 h-100">
                <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-user-clock me-2"></i> Recently Added Users</h6>
                    <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch latest 5 users
                                $recent_users = "SELECT * FROM users ORDER BY user_id DESC LIMIT 5";
                                $recent_run = mysqli_query($conn, $recent_users);

                                if(mysqli_num_rows($recent_run) > 0) {
                                    foreach($recent_run as $user) {
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle text-white d-flex justify-content-center align-items-center me-2 fw-bold" 
                                                         style="width:35px; height:35px; font-size:14px; background-color: <?= $user['role'] == 'admin' ? '#e74a3b' : ($user['role'] == 'lecturer' ? '#f6c23e' : '#1cc88a'); ?>">
                                                        <?= strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <?= $user['full_name']; ?>
                                                </div>
                                            </td>
                                            <td><span class="text-muted small"><?= $user['email']; ?></span></td>
                                            <td>
                                                <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'lecturer' ? 'warning text-dark' : 'success'); ?>">
                                                    <?= ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><span class="text-muted small"><?= date('M d, Y', strtotime($user['created_at'])); ?></span></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No users found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links / System Info Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-bolt me-2"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="users.php" class="btn btn-primary text-start"><i class="fas fa-user-plus me-2"></i> Register New User</a>
                        <a href="classes.php" class="btn btn-info text-white text-start"><i class="fas fa-chalkboard me-2"></i> Schedule Class</a>
                        <a href="labs.php" class="btn btn-warning text-dark text-start"><i class="fas fa-desktop me-2"></i> Book Computer Lab</a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center text-muted small">
                        <p class="mb-1"><strong>System Version:</strong> 1.0.0</p>
                        <p class="mb-0"><strong>Server Time:</strong> <?= date('h:i A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include('../includes/footer.php'); ?>