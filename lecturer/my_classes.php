<?php 
include('../includes/header.php'); 
include('../config/db.php');

// Security Check: Only Lecturers allowed
if($_SESSION['auth_role'] != 'lecturer'){
    $_SESSION['message'] = "Unauthorized Access";
    header("Location: ../index.php");
    exit(0);
}

// Get Logged In Lecturer ID
$lecturer_id = $_SESSION['auth_user']['user_id'];
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">My Assigned Classes</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">My Classes</li>
    </ol>

    <?php include('../message.php'); ?>

    <!-- Classes List Card -->
    <div class="card shadow border-0 mb-4">
        <div class="card-header py-3 bg-white d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-chalkboard-teacher me-2"></i> Subjects / Classes You Teach</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-bordered" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Class ID</th>
                            <th>Subject Name</th>
                            <th>Course Code</th>
                            <th>Semester / Year</th>
                            <th>Enrolled Students</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch Lecturer's Classes & Count Students
                        $my_classes = "SELECT cl.class_id, co.course_name, co.course_code, cl.semester, cl.academic_year,
                                       (SELECT COUNT(student_id) FROM enrollments WHERE class_id = cl.class_id) AS student_count
                                       FROM classes cl
                                       JOIN courses co ON cl.course_code = co.course_code
                                       WHERE cl.lecturer_id = '$lecturer_id'
                                       ORDER BY cl.class_id DESC";
                        
                        $my_classes_run = mysqli_query($conn, $my_classes);

                        if(mysqli_num_rows($my_classes_run) > 0) {
                            foreach($my_classes_run as $cls) {
                                ?>
                                <tr>
                                    <td class="fw-bold">CLS-<?= $cls['class_id']; ?></td>
                                    <td><strong><?= $cls['course_name']; ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= $cls['course_code']; ?></span></td>
                                    <td><?= $cls['semester']; ?> (<?= $cls['academic_year']; ?>)</td>
                                    <td>
                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                            <i class="fas fa-users me-1"></i> <?= $cls['student_count']; ?> Students
                                        </span>
                                    </td>
                                    <td>
                                        <!-- View Students Button (Opens Modal) -->
                                        <button type="button" class="btn btn-info btn-sm text-white view_students_btn" value="<?= $cls['class_id']; ?>" data-course="<?= $cls['course_name']; ?>">
                                            <i class="fas fa-eye me-1"></i> View Students
                                        </button>
                                        
                                        <!-- Go to Uploads Button -->
                                        <a href="upload_notes.php?class_id=<?= $cls['class_id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-upload me-1"></i> Upload Notes
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted py-4'>You have not been assigned to any classes yet. Please contact the Administrator.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     View Students Modal 
     ========================================== -->
<div class="modal fade" id="viewStudentsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="modalClassTitle"><i class="fas fa-users me-2"></i> Enrolled Students</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <!-- Search Box -->
        <div class="input-group mb-3">
            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
            <input type="text" id="searchStudent" class="form-control" placeholder="Search student by name or email...">
        </div>

        <!-- Student List Table (Loaded via AJAX) -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="studentTable">
                <thead class="table-light">
                    <tr>
                        <th width="10%">#</th>
                        <th width="40%">Student Name</th>
                        <th width="30%">Email Address</th>
                        <th width="20%">Enrolled Date</th>
                    </tr>
                </thead>
                <tbody id="student_data">
                    <!-- Data will be populated here via AJAX -->
                </tbody>
            </table>
            <div id="loading_spinner" class="text-center my-4 d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <!-- Future Feature: Export to CSV -->
        <button type="button" class="btn btn-success"><i class="fas fa-file-excel me-1"></i> Download List</button>
      </div>
    </div>
  </div>
</div>

<!-- Add jQuery for AJAX to work -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
    $(document).ready(function () {
        
        // 1. When "View Students" button is clicked
        $('.view_students_btn').click(function (e) { 
            e.preventDefault();
            
            var class_id = $(this).val();
            var course_name = $(this).attr('data-course');
            
            // Set Modal Title
            $('#modalClassTitle').html('<i class="fas fa-users me-2"></i> Students in ' + course_name);
            
            // Show Spinner, Hide old data
            $('#student_data').html('');
            $('#loading_spinner').removeClass('d-none');
            
            // Open Modal
            $('#viewStudentsModal').modal('show');

            // Send AJAX Request to get students
            $.ajax({
                type: "POST",
                url: "code.php", // We will create this below
                data: {
                    'fetch_students': true,
                    'class_id': class_id
                },
                success: function (response) {
                    $('#loading_spinner').addClass('d-none'); // Hide spinner
                    $('#student_data').html(response);        // Load Data
                }
            });
        });

        // 2. Simple Client-Side Search functionality for Modal Table
        $("#searchStudent").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#student_data tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

    });
</script>

<?php include('../includes/footer.php'); ?>