<?php 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Class Management</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Classes</li>
    </ol>

    <?php include('../message.php'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chalkboard me-1"></i> Scheduled Classes</h6>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addClassModal">
                <i class="fas fa-plus-circle"></i> Create New Class
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Class ID</th>
                            <th>Course Name</th>
                            <th>Lecturer In-Charge</th>
                            <th>Semester</th>
                            <th>Academic Year</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // JOIN Query: Subject Name & Lecturer Name එක එකපාර ගන්නවා
                        $query = "SELECT cl.*, co.course_name, u.full_name 
                                  FROM classes cl 
                                  JOIN courses co ON cl.course_code = co.course_code
                                  LEFT JOIN users u ON cl.lecturer_id = u.user_id 
                                  ORDER BY cl.class_id DESC";
                        
                        $query_run = mysqli_query($conn, $query);

                        if(mysqli_num_rows($query_run) > 0)
                        {
                            foreach($query_run as $row)
                            {
                                ?>
                                <tr>
                                    <td><?= $row['class_id']; ?></td>
                                    <td>
                                        <strong><?= $row['course_name']; ?></strong><br>
                                        <small class="text-muted"><?= $row['course_code']; ?></small>
                                    </td>
                                    <td>
                                        <?php if($row['full_name']): ?>
                                            <?= $row['full_name']; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $row['semester']; ?></td>
                                    <td><?= $row['academic_year']; ?></td>
                                    <td>
                                        <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this class? This will also remove student enrollments!');">
                                            <input type="hidden" name="class_id" value="<?= $row['class_id']; ?>">
                                            <button type="submit" name="delete_class_btn" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<tr><td colspan='6' class='text-center'>No Classes Created Yet</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Class</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form action="code.php" method="POST">
        <div class="modal-body">
            
            <!-- 1. Select Course -->
            <div class="mb-3">
                <label>Select Course (Subject)</label>
                <select name="course_code" class="form-select" required>
                    <option value="">-- Choose Subject --</option>
                    <?php
                    $course_q = "SELECT * FROM courses";
                    $course_run = mysqli_query($conn, $course_q);
                    if(mysqli_num_rows($course_run) > 0){
                        foreach($course_run as $c){
                            echo '<option value="'.$c['course_code'].'">'.$c['course_name'].' ('.$c['course_code'].')</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- 2. Select Lecturer -->
            <div class="mb-3">
                <label>Assign Lecturer</label>
                <select name="lecturer_id" class="form-select" required>
                    <option value="">-- Choose Lecturer --</option>
                    <?php
                    // Get only users with role 'lecturer'
                    $lec_q = "SELECT * FROM users WHERE role='lecturer'";
                    $lec_run = mysqli_query($conn, $lec_q);
                    if(mysqli_num_rows($lec_run) > 0){
                        foreach($lec_run as $l){
                            echo '<option value="'.$l['user_id'].'">'.$l['full_name'].'</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="row">
                <!-- 3. Academic Year -->
                <div class="col-md-6 mb-3">
                    <label>Academic Year</label>
                    <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2024-2025" required>
                </div>

                <!-- 4. Semester -->
                <div class="col-md-6 mb-3">
                    <label>Semester</label>
                    <select name="semester" class="form-select" required>
                      <option value="Semester 1">Semester 1 (First Half)</option>
                      <option value="Semester 2">Semester 2 (Second Half)</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_class_btn" class="btn btn-primary">Create Class</button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>