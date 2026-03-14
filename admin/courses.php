<?php 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Course Management</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Courses</li>
    </ol>

    <?php include('../message.php'); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-book me-1"></i> Course List</h6>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="fas fa-plus-circle"></i> Add Course
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Semester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM courses ORDER BY semester_level ASC";
                        $query_run = mysqli_query($conn, $query);

                        if(mysqli_num_rows($query_run) > 0)
                        {
                            foreach($query_run as $row)
                            {
                                ?>
                                <tr>
                                    <td><strong><?= $row['course_code']; ?></strong></td>
                                    <td><?= $row['course_name']; ?></td>
                                    <td><?= $row['credits']; ?></td>
                                    <td><span class="badge bg-info text-dark">Sem <?= $row['semester_level']; ?></span></td>
                                    <td>
                                        <a href="course_edit.php?code=<?= $row['course_code']; ?>" class="btn btn-success btn-sm"><i class="fas fa-edit"></i></a>
                                        
                                        <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This might delete related classes!');">
                                            <input type="hidden" name="course_code" value="<?= $row['course_code']; ?>">
                                            <button type="submit" name="delete_course_btn" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<tr><td colspan='5' class='text-center'>No Courses Found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add New Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form action="code.php" method="POST">
        <div class="modal-body">
            
            <div class="mb-3">
                <label>Course Code (e.g., ICT101)</label>
                <input type="text" name="course_code" class="form-control" required placeholder="Unique Code">
            </div>

            <div class="mb-3">
                <label>Course Name</label>
                <input type="text" name="course_name" class="form-control" required placeholder="Subject Name">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Credits</label>
                    <input type="number" name="credits" class="form-control" required min="1" max="4">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Semester Level</label>
                    <select name="semester_level" class="form-select" required>
                        <option value="">Select</option>
                        <?php for($i=1; $i<=8; $i++): ?>
                            <option value="<?= $i; ?>">Semester <?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label>Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="add_course_btn" class="btn btn-primary">Save Course</button>
        </div>
      </form>

    </div>
  </div>
</div>

<?php include('../includes/footer.php'); ?>