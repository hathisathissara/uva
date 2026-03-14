<?php 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Student Enrollments</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Enrollments</li>
    </ol>

    <?php include('../message.php'); ?>

    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-graduate me-1"></i> Enrolled Students List</h6>
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#enrollModal">
                <i class="fas fa-plus-circle"></i> Enroll Students
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Course / Subject</th>
                            <th>Academic Year & Sem</th>
                            <th>Enrolled Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // JOIN Query to get Student Name, Course Name, and Class details
                        $query = "SELECT e.enrollment_id, e.enrolled_date, 
                                         u.full_name AS student_name, u.email,
                                         c.course_name, cl.academic_year, cl.semester
                                  FROM enrollments e
                                  JOIN users u ON e.student_id = u.user_id
                                  JOIN classes cl ON e.class_id = cl.class_id
                                  JOIN courses c ON cl.course_code = c.course_code
                                  ORDER BY e.enrollment_id DESC";
                        
                        $query_run = mysqli_query($conn, $query);

                        if(mysqli_num_rows($query_run) > 0)
                        {
                            foreach($query_run as $row)
                            {
                                ?>
                                <tr>
                                    <td><?= $row['enrollment_id']; ?></td>
                                    <td>
                                        <strong><?= $row['student_name']; ?></strong><br>
                                        <small class="text-muted"><?= $row['email']; ?></small>
                                    </td>
                                    <td><span class="badge bg-info text-dark"><?= $row['course_name']; ?></span></td>
                                    <td><?= $row['academic_year']; ?> (<?= $row['semester']; ?>)</td>
                                    <td><?= date('Y-m-d', strtotime($row['enrolled_date'])); ?></td>
                                    <td>
                                        <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Remove student from this class? They will lose access to materials and results!');">
                                            <input type="hidden" name="enrollment_id" value="<?= $row['enrollment_id']; ?>">
                                            <button type="submit" name="unenroll_btn" class="btn btn-danger btn-sm"><i class="fas fa-user-minus"></i> Unenroll</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<tr><td colspan='6' class='text-center'>No Enrollments Found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Enroll Student Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Enroll Students to Class</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form action="code.php" method="POST">
        <div class="modal-body">
            
            <!-- 1. Select Class -->
            <div class="mb-4">
                <label class="fw-bold text-primary mb-2">1. Select Target Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Choose a Class --</option>
                    <?php
                    $class_q = "SELECT cl.class_id, c.course_name, cl.academic_year, cl.semester 
                                FROM classes cl 
                                JOIN courses c ON cl.course_code = c.course_code
                                ORDER BY cl.class_id DESC";
                    $class_run = mysqli_query($conn, $class_q);
                    
                    if(mysqli_num_rows($class_run) > 0){
                        foreach($class_run as $cls){
                            echo '<option value="'.$cls['class_id'].'">'.$cls['course_name'].' - '.$cls['academic_year'].' ('.$cls['semester'].')</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- 2. Select Students (Multiple Selection) -->
            <div class="mb-3">
                <label class="fw-bold text-primary mb-2">2. Select Students (Hold CTRL or CMD to select multiple)</label>
                <select name="student_ids[]" class="form-select" multiple size="8" required>
                    <?php
                    // Get only users with role 'student'
                    $stu_q = "SELECT user_id, full_name, email FROM users WHERE role='student' ORDER BY full_name ASC";
                    $stu_run = mysqli_query($conn, $stu_q);
                    
                    if(mysqli_num_rows($stu_run) > 0){
                        foreach($stu_run as $stu){
                            echo '<option value="'.$stu['user_id'].'">'.$stu['full_name'].' ('.$stu['email'].')</option>';
                        }
                    } else {
                        echo '<option disabled>No Students Registered Yet</option>';
                    }
                    ?>
                </select>
                <div class="form-text text-muted mt-2">
                    <i class="fas fa-info-circle"></i> Tip: You can select multiple students at once to enroll them together.
                </div>
            </div>

        </div>
        <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="enroll_students_btn" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Enrollments</button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>