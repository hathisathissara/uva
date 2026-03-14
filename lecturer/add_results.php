<?php 
include('../includes/header.php'); 
include('../config/db.php');

if($_SESSION['auth_role'] != 'lecturer'){
    $_SESSION['message'] = "Unauthorized Access";
    header("Location: ../index.php");
    exit(0);
}

$lecturer_id = $_SESSION['auth_user']['user_id'];

// Check if a class is selected via GET request
$selected_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Upload Student Marks</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Add Results</li>
    </ol>

    <?php include('../message.php'); ?>

    <!-- Step 1: Select Class & Assessment Type -->
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i> Step 1: Select Class</h6>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row align-items-end">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">My Assigned Classes</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php
                        $class_q = "SELECT cl.class_id, co.course_name, cl.academic_year 
                                    FROM classes cl 
                                    JOIN courses co ON cl.course_code = co.course_code
                                    WHERE cl.lecturer_id = '$lecturer_id'";
                        $class_run = mysqli_query($conn, $class_q);
                        
                        if(mysqli_num_rows($class_run) > 0){
                            foreach($class_run as $cls){
                                $selected = ($selected_class_id == $cls['class_id']) ? 'selected' : '';
                                echo "<option value='".$cls['class_id']."' $selected>".$cls['course_name']." (".$cls['academic_year'].")</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Load Students</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Step 2: Enter Marks Table (Shows only if a class is selected) -->
    <?php if(!empty($selected_class_id)): ?>
    <div class="card shadow border-0 mb-4 border-top-primary" style="border-top: 4px solid #4e73df;">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-edit me-2"></i> Step 2: Enter Marks</h6>
        </div>
        <div class="card-body">
            <form action="code.php" method="POST">
                
                <!-- Hidden input to send the class_id to code.php -->
                <input type="hidden" name="class_id" value="<?= $selected_class_id; ?>">

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="fw-bold">Assessment Type</label>
                        <select name="assessment_type" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            <option value="Midterm Exam">Midterm Exam</option>
                            <option value="Final Exam">Final Exam</option>
                            <option value="Assignment 1">Assignment 1</option>
                            <option value="Assignment 2">Assignment 2</option>
                            <option value="Practical Test">Practical Test</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Total Marks (Out of)</label>
                        <input type="number" name="total_marks" class="form-control" value="100" required>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Marks Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch Enrolled Students for this Class
                            $students_q = "SELECT e.enrollment_id, u.full_name, u.user_id
                                           FROM enrollments e 
                                           JOIN users u ON e.student_id = u.user_id 
                                           WHERE e.class_id = '$selected_class_id'
                                           ORDER BY u.full_name ASC";
                            $students_run = mysqli_query($conn, $students_q);

                            if(mysqli_num_rows($students_run) > 0)
                            {
                                foreach($students_run as $stu)
                                {
                                    ?>
                                    <tr>
                                        <td>
                                            <!-- We need the enrollment_id to save the result correctly -->
                                            <input type="hidden" name="enrollment_ids[]" value="<?= $stu['enrollment_id']; ?>">
                                            STU-<?= $stu['user_id']; ?>
                                        </td>
                                        <td class="fw-bold"><?= $stu['full_name']; ?></td>
                                        <td>
                                            <!-- Input array for marks -->
                                            <input type="number" name="marks[]" class="form-control w-50" placeholder="Enter marks" step="0.01" min="0">
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            else
                            {
                                echo "<tr><td colspan='3' class='text-center text-danger'>No students enrolled in this class yet!</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php if(mysqli_num_rows($students_run) > 0): ?>
                <div class="text-end mt-3">
                    <button type="submit" name="save_marks_btn" class="btn btn-success btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i> Publish Results
                    </button>
                </div>
                <?php endif; ?>

            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include('../includes/footer.php'); ?>