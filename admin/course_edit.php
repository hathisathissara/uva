<?php 
include('../includes/header.php'); 
include('../config/db.php');
?>

<div class="container-fluid px-4">
    <h3 class="mt-4">Edit Course</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>

    <div class="card shadow">
        <div class="card-header">
            <h6>Update Course Information</h6>
        </div>
        <div class="card-body">
            
            <?php
            if(isset($_GET['code']))
            {
                $course_code = mysqli_real_escape_string($conn, $_GET['code']);
                $query = "SELECT * FROM courses WHERE course_code='$course_code' LIMIT 1";
                $query_run = mysqli_query($conn, $query);

                if(mysqli_num_rows($query_run) > 0)
                {
                    $course = mysqli_fetch_array($query_run);
                    ?>
                    
                    <form action="code.php" method="POST">
                        
                        <div class="mb-3">
                            <label>Course Code (Cannot be changed)</label>
                            <!-- Readonly because it's the Primary Key -->
                            <input type="text" name="course_code" value="<?= $course['course_code']; ?>" class="form-control bg-light" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Course Name</label>
                            <input type="text" name="course_name" value="<?= $course['course_name']; ?>" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Credits</label>
                                <input type="number" name="credits" value="<?= $course['credits']; ?>" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Semester Level</label>
                                <select name="semester_level" class="form-select" required>
                                    <option value="">Select</option>
                                    <?php for($i=1; $i<=8; $i++): ?>
                                        <option value="<?= $i; ?>" <?= $course['semester_level'] == $i ? 'selected':'' ?> >Semester <?= $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= $course['description']; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <button type="submit" name="update_course_btn" class="btn btn-primary">Update Course</button>
                            <a href="courses.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <?php
                }
                else
                {
                    echo "<h4>No Course Found</h4>";
                }
            }
            ?>

        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>