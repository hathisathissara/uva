<?php 
$path = "../"; // Student folder එක ඇතුළේ නිසා
include('../includes/header.php'); 
include('../config/db.php');

// Security Check: Only Students
if($_SESSION['auth_role'] != 'student'){
    $_SESSION['message'] = "Unauthorized Access";
    header("Location: ../index.php");
    exit(0);
}

$student_id = $_SESSION['auth_user']['user_id'];

// ==========================================
// 1. FETCH STUDENT DATA
// ==========================================

// Get Total Enrolled Courses
$enroll_query = "SELECT COUNT(enrollment_id) AS total FROM enrollments WHERE student_id='$student_id'";
$total_courses = mysqli_fetch_assoc(mysqli_query($conn, $enroll_query))['total'];

// Get Overall Attendance (Percentage) - Optional
$att_query = "SELECT 
    (SELECT COUNT(*) FROM attendance a JOIN enrollments e ON a.enrollment_id=e.enrollment_id WHERE e.student_id='$student_id' AND a.status='Present') * 100 / 
    NULLIF((SELECT COUNT(*) FROM attendance a JOIN enrollments e ON a.enrollment_id=e.enrollment_id WHERE e.student_id='$student_id'), 0) AS percentage";
$attendance = mysqli_fetch_assoc(mysqli_query($conn, $att_query))['percentage'] ?? 0;
?>

<div class="container-fluid px-4 text-theme">
    <h3 class="mt-4 fw-bold">Student Dashboard</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Welcome back, <?= $_SESSION['auth_user']['full_name']; ?>!</li>
    </ol>

    <?php include('../message.php'); ?>
<!-- Updated Notice Section for Dashboard -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 notice-card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-bullhorn text-danger me-2"></i> Department Announcements
                </h6>
                <span class="badge bg-danger rounded-pill">Latest Updates</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php
                    $latest_notices = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC LIMIT 4");
                    
                    if(mysqli_num_rows($latest_notices) > 0) {
                        while($not = mysqli_fetch_array($latest_notices)) {
                            
                            // තේමාවන් සහ අයිකන් තෝරාගැනීම
                            $cat_class = 'general'; 
                            $icon = 'fa-info-circle';
                            
                            if($not['category'] == 'Exam') {
                                $cat_class = 'exam';
                                $icon = 'fa-file-signature';
                            } elseif($not['category'] == 'Event') {
                                $cat_class = 'event';
                                $icon = 'fa-calendar-alt';
                            } elseif($not['category'] == 'Lab') {
                                $cat_class = 'lab';
                                $icon = 'fa-desktop';
                            }
                            ?>

                            <!-- Notice Item -->
                            <div class="list-group-item list-group-item-action py-3 notice-item border-<?= $cat_class; ?>">
                                <div class="d-flex align-items-center">
                                    <!-- Category Icon Container -->
                                    <div class="notice-icon bg-light me-3">
                                        <i class="fas <?= $icon; ?> text-<?= ($cat_class == 'lab') ? 'warning' : $cat_class; ?>"></i>
                                    </div>
                                    
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold text-dark"><?= $not['title']; ?></h6>
                                            <small class="text-muted"><i class="far fa-clock me-1"></i> <?= date('M d', strtotime($not['created_at'])); ?></small>
                                        </div>
                                        
                                        <!-- Message (Truncated for clean look) -->
                                        <p class="mb-1 small text-secondary text-truncate" style="max-width: 600px;">
                                            <?= $not['message']; ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span class="badge badge-<?= $cat_class; ?> shadow-sm small">
                                                <?= $not['category']; ?>
                                            </span>
                                            <!-- View More Link -->
                                            <a href="javascript:void(0);" class="text-primary small text-decoration-none fw-bold" 
                                               data-bs-toggle="modal" data-bs-target="#noticeModal<?= $not['id']; ?>">
                                                Read More <i class="fas fa-arrow-right ms-1" style="font-size: 0.7rem;"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Individual Modal for each Notice -->
                            <div class="modal fade" id="noticeModal<?= $not['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body pt-0 px-4 pb-4">
                                            <div class="text-center mb-3">
                                                <div class="notice-icon bg-light mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                    <i class="fas <?= $icon; ?> text-<?= ($cat_class == 'lab') ? 'warning' : $cat_class; ?>"></i>
                                                </div>
                                                <h5 class="fw-bold"><?= $not['title']; ?></h5>
                                                <span class="badge badge-<?= $cat_class; ?>"><?= $not['category']; ?></span>
                                            </div>
                                            <hr>
                                            <p class="text-secondary" style="line-height: 1.6;"><?= nl2br($not['message']); ?></p>
                                            <div class="text-end mt-3 text-muted small">
                                                <i class="fas fa-calendar-day me-1"></i> Posted on <?= date('F d, Y', strtotime($not['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php
                        }
                    } else {
                        echo "<div class='p-5 text-center text-muted'>
                                <i class='fas fa-bell-slash fa-2x mb-2 opacity-25'></i>
                                <p class='small mb-0'>No active announcements at the moment.</p>
                              </div>";
                    }
                    ?>
                </div>
            </div>
            <div class="card-footer bg-light border-0 py-2 text-center">
                    <!-- පේජ් එකේ නම notices.php ලෙස ලබා දෙන්න -->
    <a href="notices.php" class="small text-decoration-none fw-bold text-muted">
        View All Announcements <i class="fas fa-chevron-right ms-1"></i>
    </a>

            </div>
        </div>
    </div>
</div>
    <!-- Summary Cards -->
    <div class="row">
        <!-- Enrolled Courses -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">My Enrolled Courses</div>
                            <div class="h5 mb-0 font-weight-bold"><?= $total_courses; ?> Subjects</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-book-reader fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Percentage -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 border-0 border-start border-4 border-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Attendance Rate</div>
                            <div class="h5 mb-0 font-weight-bold"><?= number_format($attendance, 1); ?>%</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-calendar-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FYP Status Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">FYP Project Status</div>
                            <?php 
                            $fyp_q = "SELECT status FROM fyp_projects WHERE student_id='$student_id' LIMIT 1";
                            $fyp_res = mysqli_query($conn, $fyp_q);
                            $fyp_status = (mysqli_num_rows($fyp_res) > 0) ? mysqli_fetch_assoc($fyp_res)['status'] : 'Not Submitted';
                            ?>
                            <div class="h6 mb-0 font-weight-bold text-uppercase"><?= $fyp_status; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-project-diagram fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <!-- Registered Courses Table -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-list me-2"></i> My Current Subjects</h6>
                </div>
                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Subject</th>
                                    <th>Lecturer</th>
                                    <th>Semester</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $my_sub_query = "SELECT cl.class_id, co.course_name, co.course_code, cl.semester, u.full_name AS lecturer_name 
                                                 FROM enrollments e 
                                                 JOIN classes cl ON e.class_id = cl.class_id 
                                                 JOIN courses co ON cl.course_code = co.course_code 
                                                 LEFT JOIN users u ON cl.lecturer_id = u.user_id 
                                                 WHERE e.student_id = '$student_id'";
                                $my_sub_run = mysqli_query($conn, $my_sub_query);

                                if(mysqli_num_rows($my_sub_run) > 0) {
                                    foreach($my_sub_run as $row) {
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-bold"><?= $row['course_name']; ?></span><br>
                                                <small class="text-muted"><?= $row['course_code']; ?></small>
                                            </td>
                                            <td><?= $row['lecturer_name'] ?? 'TBA'; ?></td>
                                            <td><?= $row['semester']; ?></td>
                                            <td class="text-center">
                                                <a href="downloads.php?class_id=<?= $row['class_id']; ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                                    <i class="fas fa-download me-1"></i> Notes
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center py-4'>You are not enrolled in any courses.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Widget: Quick Links -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold">Resources</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="my_results.php" class="list-group-item list-group-item-action border-0 px-0">
                            <i class="fas fa-file-invoice text-success me-2"></i> View Examination Results
                        </a>
                        <a href="downloads.php" class="list-group-item list-group-item-action border-0 px-0">
                            <i class="fas fa-folder-open text-warning me-2"></i> Access Course Materials
                        </a>
                        <a href="submit_fyp.php" class="list-group-item list-group-item-action border-0 px-0">
                            <i class="fas fa-lightbulb text-info me-2"></i> FYP Submission Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>