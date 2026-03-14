<?php 
include('../includes/header.php'); 
include('../config/db.php');

if($_SESSION['auth_role'] != 'lecturer'){
    $_SESSION['message'] = "Unauthorized Access";
    header("Location: ../login.php");
    exit(0);
}

$lecturer_id = $_SESSION['auth_user']['user_id'];

// Check if class & date are selected via GET request
$selected_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : '';
// Default date is today
$selected_date = isset($_GET['attendance_date']) ? $_GET['attendance_date'] : date('Y-m-d');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Student Attendance</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Take Attendance</li>
    </ol>

    <?php include('../message.php'); ?>

    <!-- Step 1: Select Class & Date -->
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter me-2"></i> Step 1: Select Class & Date</h6>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row align-items-end">
                <div class="col-md-5 mb-3">
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
                
                <div class="col-md-4 mb-3">
                    <label class="fw-bold">Attendance Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="<?= $selected_date; ?>" max="<?= date('Y-m-d'); ?>" required>
                </div>

                <div class="col-md-3 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Load Students</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Step 2: Mark Attendance Table (Shows only if a class is selected) -->
    <?php if(!empty($selected_class_id)): ?>
    <div class="card shadow border-0 mb-4 border-top-primary" style="border-top: 4px solid #1cc88a;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-clipboard-check me-2"></i> Mark Attendance for <?= date('d M Y', strtotime($selected_date)); ?>
            </h6>
        </div>
        <div class="card-body">
            <form action="code.php" method="POST">
                
                <!-- Hidden inputs to send data to code.php -->
                <input type="hidden" name="class_id" value="<?= $selected_class_id; ?>">
                <input type="hidden" name="attendance_date" value="<?= $selected_date; ?>">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="10%">Student ID</th>
                                <th width="40%">Student Name</th>
                                <th width="30%">Status</th>
                                <th width="20%">Remarks (Optional)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch Enrolled Students & their current attendance status (if already marked)
                            $students_q = "SELECT e.enrollment_id, u.full_name, u.user_id,
                                           (SELECT status FROM attendance WHERE enrollment_id = e.enrollment_id AND attendance_date = '$selected_date') AS current_status,
                                           (SELECT remarks FROM attendance WHERE enrollment_id = e.enrollment_id AND attendance_date = '$selected_date') AS current_remarks
                                           FROM enrollments e 
                                           JOIN users u ON e.student_id = u.user_id 
                                           WHERE e.class_id = '$selected_class_id'
                                           ORDER BY u.full_name ASC";
                            
                            $students_run = mysqli_query($conn, $students_q);

                            if(mysqli_num_rows($students_run) > 0)
                            {
                                foreach($students_run as $stu)
                                {
                                    $status = $stu['current_status'] ?? 'Present'; // Default is Present
                                    $remarks = $stu['current_remarks'] ?? '';
                                    ?>
                                    <tr>
                                        <td>
                                            <!-- We need enrollment_id to save attendance -->
                                            <input type="hidden" name="enrollment_ids[]" value="<?= $stu['enrollment_id']; ?>">
                                            STU-<?= $stu['user_id']; ?>
                                        </td>
                                        <td class="fw-bold">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2" style="width:30px; height:30px; font-size:12px;">
                                                    <?= strtoupper(substr($stu['full_name'], 0, 1)); ?>
                                                </div>
                                                <?= $stu['full_name']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- Radio buttons for Present/Absent/Late -->
                                            <div class="btn-group" role="group">
                                                <input type="radio" class="btn-check" name="status[<?= $stu['enrollment_id']; ?>]" id="present_<?= $stu['enrollment_id']; ?>" value="Present" <?= $status=='Present'?'checked':''; ?>>
                                                <label class="btn btn-outline-success btn-sm" for="present_<?= $stu['enrollment_id']; ?>">Present</label>

                                                <input type="radio" class="btn-check" name="status[<?= $stu['enrollment_id']; ?>]" id="absent_<?= $stu['enrollment_id']; ?>" value="Absent" <?= $status=='Absent'?'checked':''; ?>>
                                                <label class="btn btn-outline-danger btn-sm" for="absent_<?= $stu['enrollment_id']; ?>">Absent</label>

                                                <input type="radio" class="btn-check" name="status[<?= $stu['enrollment_id']; ?>]" id="late_<?= $stu['enrollment_id']; ?>" value="Late" <?= $status=='Late'?'checked':''; ?>>
                                                <label class="btn btn-outline-warning btn-sm" for="late_<?= $stu['enrollment_id']; ?>">Late</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="remarks[<?= $stu['enrollment_id']; ?>]" class="form-control form-control-sm" placeholder="Reason..." value="<?= $remarks; ?>">
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            else
                            {
                                echo "<tr><td colspan='4' class='text-center text-danger py-4'><i class='fas fa-exclamation-circle me-2'></i> No students enrolled in this class yet!</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php if(mysqli_num_rows($students_run) > 0): ?>
                <div class="text-end mt-3 bg-light p-3 rounded border">
                    <button type="submit" name="save_attendance_btn" class="btn btn-success btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i> Save Attendance
                    </button>
                </div>
                <?php endif; ?>

            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include('../includes/footer.php'); ?>