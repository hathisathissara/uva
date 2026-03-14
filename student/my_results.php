<?php 
$path = "../"; 
include('../includes/header.php'); 
include('../config/db.php');

$student_id = $_SESSION['auth_user']['user_id'];
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">My Examination Results</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Results</li>
    </ol>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Course Name</th>
                            <th>Assessment Type</th>
                            <th class="text-center">Marks Obtained</th>
                            <th class="text-center">Total Marks</th>
                            <th class="text-center">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res_query = "SELECT r.*, co.course_name 
                                      FROM results r 
                                      JOIN enrollments e ON r.enrollment_id = e.enrollment_id 
                                      JOIN classes cl ON e.class_id = cl.class_id 
                                      JOIN courses co ON cl.course_code = co.course_code 
                                      WHERE e.student_id = '$student_id' 
                                      ORDER BY r.result_id DESC";
                        $res_run = mysqli_query($conn, $res_query);

                        if(mysqli_num_rows($res_run) > 0) {
                            foreach($res_run as $row) {
                                $percentage = ($row['marks_obtained'] / $row['total_marks']) * 100;
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= $row['course_name']; ?></td>
                                    <td><?= $row['assessment_type']; ?></td>
                                    <td class="text-center fw-bold text-primary"><?= $row['marks_obtained']; ?></td>
                                    <td class="text-center"><?= $row['total_marks']; ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $percentage >= 50 ? 'bg-success' : 'bg-danger'; ?> px-3">
                                            <?= number_format($percentage, 1); ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-4'>No results published yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>