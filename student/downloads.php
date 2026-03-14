<?php 
$path = "../"; 
include('../includes/header.php'); 
include('../config/db.php');

$student_id = $_SESSION['auth_user']['user_id'];
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Course Materials</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Downloads</li>
    </ol>

    <div class="row">
        <?php
        // Get all classes the student is enrolled in
        $classes_query = "SELECT cl.class_id, co.course_name FROM enrollments e 
                          JOIN classes cl ON e.class_id = cl.class_id 
                          JOIN courses co ON cl.course_code = co.course_code 
                          WHERE e.student_id = '$student_id'";
        $classes_run = mysqli_query($conn, $classes_query);

        if(mysqli_num_rows($classes_run) > 0) {
            foreach($classes_run as $class) {
                $class_id = $class['class_id'];
                ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fas fa-book me-2"></i> <?= $class['course_name']; ?>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php
                                // Fetch materials for this specific class
                                $mat_query = "SELECT * FROM materials WHERE class_id = '$class_id' ORDER BY material_id DESC";
                                $mat_run = mysqli_query($conn, $mat_query);

                                if(mysqli_num_rows($mat_run) > 0) {
                                    foreach($mat_run as $mat) {
                                        ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div>
                                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                                <?= $mat['title']; ?>
                                                <small class="d-block text-muted"><?= date('M d, Y', strtotime($mat['upload_date'])); ?></small>
                                            </div>
                                            <a href="../<?= $mat['file_path']; ?>" class="btn btn-sm btn-success" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                } else {
                                    echo "<li class='list-group-item text-center py-3 text-muted small'>No materials uploaded yet.</li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='col-12 text-center py-5'><h5>You are not enrolled in any classes.</h5></div>";
        }
        ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>