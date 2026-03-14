<?php 
include('../includes/header.php'); 
include('../config/db.php');

// Security Check: Make sure only lecturers access this folder
if($_SESSION['auth_role'] != 'lecturer'){
    $_SESSION['message'] = "You are not authorized as a Lecturer";
    header("Location: ../login.php");
    exit(0);
}

// Get Logged In Lecturer ID
$lecturer_id = $_SESSION['auth_user']['user_id'];

// ==========================================
// 1. FETCH DATA FOR LECTURER DASHBOARD
// ==========================================

// Get Total Classes assigned to this lecturer
$class_query = "SELECT COUNT(class_id) AS total_classes FROM classes WHERE lecturer_id='$lecturer_id'";
$class_run = mysqli_query($conn, $class_query);
$total_classes = mysqli_fetch_assoc($class_run)['total_classes'];

// Get Total Students enrolled in this lecturer's classes
$student_query = "SELECT COUNT(DISTINCT e.student_id) AS total_students 
                  FROM enrollments e 
                  JOIN classes c ON e.class_id = c.class_id 
                  WHERE c.lecturer_id='$lecturer_id'";
$student_run = mysqli_query($conn, $student_query);
$total_students = mysqli_fetch_assoc($student_run)['total_students'];

// Get Total Materials Uploaded by this lecturer
$material_query = "SELECT COUNT(m.material_id) AS total_materials 
                   FROM materials m 
                   JOIN classes c ON m.class_id = c.class_id 
                   WHERE c.lecturer_id='$lecturer_id'";
$material_run = mysqli_query($conn, $material_query);
$total_materials = mysqli_fetch_assoc($material_run)['total_materials'];
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Lecturer Dashboard</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Welcome back, Prof. <?= $_SESSION['auth_user']['full_name']; ?>!</li>
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
    <!-- ==========================================
         2. SUMMARY CARDS ROW
         ========================================== -->
    <div class="row">
        
        <!-- Total Classes Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 border-0" style="border-left: 4px solid #4e73df !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 0.8rem;">
                                My Classes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold fs-3"><?= $total_classes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Students Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 border-0" style="border-left: 4px solid #1cc88a !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.8rem;">
                                My Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold fs-3"><?= $total_students; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Uploads Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 border-0" style="border-left: 4px solid #36b9cc !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1" style="font-size: 0.8rem;">
                                Uploaded Materials</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800 fw-bold fs-3"><?= $total_materials; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-pdf fa-2x text-gray-300" style="color: #dddfeb;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         3. MY SCHEDULED CLASSES TABLE
         ========================================== -->
    <div class="row mt-3">
        <div class="col-lg-8 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-book me-2"></i> My Upcoming Classes</h6>
                    <a href="my_classes.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Course Code</th>
                                    <th>Semester</th>
                                    <th>Enrolled Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch Lecturer's Classes with student count
                                $my_classes = "SELECT cl.class_id, co.course_name, co.course_code, cl.semester, cl.academic_year,
                                              (SELECT COUNT(student_id) FROM enrollments WHERE class_id = cl.class_id) AS student_count
                                              FROM classes cl
                                              JOIN courses co ON cl.course_code = co.course_code
                                              WHERE cl.lecturer_id = '$lecturer_id'
                                              ORDER BY cl.class_id DESC LIMIT 5";
                                
                                $my_classes_run = mysqli_query($conn, $my_classes);

                                if(mysqli_num_rows($my_classes_run) > 0) {
                                    foreach($my_classes_run as $cls) {
                                        ?>
                                        <tr>
                                            <td><strong><?= $cls['course_name']; ?></strong></td>
                                            <td><span class="badge bg-secondary"><?= $cls['course_code']; ?></span></td>
                                            <td><?= $cls['semester']; ?> (<?= $cls['academic_year']; ?>)</td>
                                            <td>
                                                <span class="badge bg-success rounded-pill px-3 py-2">
                                                    <i class="fas fa-user-graduate me-1"></i> <?= $cls['student_count']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No classes assigned yet. Contact Admin.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-bolt me-2"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="my_classes.php" class="btn btn-primary text-start"><i class="fas fa-users me-2"></i> View Students List</a>
                        <a href="upload_notes.php" class="btn btn-info text-white text-start"><i class="fas fa-cloud-upload-alt me-2"></i> Upload Lecture Notes</a>
                        <a href="add_results.php" class="btn btn-success text-white text-start"><i class="fas fa-edit me-2"></i> Add Student Results</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include('../includes/footer.php'); ?>