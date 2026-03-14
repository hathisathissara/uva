<?php 
$path = "../"; 
include('../includes/header.php'); 
include('../config/db.php');

$student_id = $_SESSION['auth_user']['user_id'];

// Check if student has already submitted a project
$check_fyp = "SELECT f.*, u.full_name AS supervisor_name 
              FROM fyp_projects f 
              LEFT JOIN users u ON f.supervisor_id = u.user_id 
              WHERE f.student_id = '$student_id' LIMIT 1";
$fyp_res = mysqli_query($conn, $check_fyp);
$fyp_data = mysqli_fetch_array($fyp_res);
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Final Year Project (FYP) Portal</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">FYP Submission</li>
    </ol>

    <?php include('../message.php'); ?>

    <div class="row">
        <!-- IF ALREADY SUBMITTED: Show Status Card -->
        <?php if(mysqli_num_rows($fyp_res) > 0 && $fyp_data['status'] != 'rejected'): ?>
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Your Project Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="fw-bold text-dark"><?= $fyp_data['title']; ?></h4>
                                <p class="text-muted"><?= $fyp_data['description']; ?></p>
                                <hr>
                                <p><strong>Supervisor:</strong> <?= $fyp_data['supervisor_name'] ?? 'Not Assigned Yet'; ?></p>
                                <p><strong>Status:</strong> 
                                    <?php 
                                    if($fyp_data['status'] == 'pending') echo '<span class="badge bg-warning text-dark">Pending Approval</span>';
                                    elseif($fyp_data['status'] == 'approved') echo '<span class="badge bg-success">Approved / In Progress</span>';
                                    elseif($fyp_data['status'] == 'completed') echo '<span class="badge bg-primary">Project Completed</span>';
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded border">
                                    <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                                    <br>
                                    <a href="../<?= $fyp_data['submission_file']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Proposal</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- IF NOT SUBMITTED or REJECTED: Show Form -->
        <?php else: ?>
            <div class="col-md-7">
                <?php if(isset($fyp_data['status']) && $fyp_data['status'] == 'rejected'): ?>
                    <div class="alert alert-danger">
                        <strong>Your previous proposal was rejected.</strong> Reason: Please check with your supervisor and submit a new proposal.
                    </div>
                <?php endif; ?>

                <div class="card shadow border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Submit New Project Proposal</h6>
                    </div>
                    <div class="card-body">
                        <form action="code.php" method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-3">
                                <label class="fw-bold">Project Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter your project name" required>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">Select Preferred Supervisor (Lecturer)</label>
                                <select name="supervisor_id" class="form-select" required>
                                    <option value="">-- Choose Supervisor --</option>
                                    <?php
                                    $lecs = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role='lecturer'");
                                    while($lec = mysqli_fetch_array($lecs)){
                                        echo "<option value='".$lec['user_id']."'>".$lec['full_name']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold">Short Description / Abstract</label>
                                <textarea name="description" rows="4" class="form-control" placeholder="Briefly describe your project goals..." required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold">Upload Proposal Document (PDF only)</label>
                                <input type="file" name="proposal_file" class="form-control" accept=".pdf" required>
                                <small class="text-muted small">Max file size: 5MB.</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="submit_fyp_btn" class="btn btn-primary btn-lg shadow">
                                    <i class="fas fa-paper-plane me-2"></i> Submit Proposal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="card bg-light border-0 shadow-sm">
                    <div class="card-body">
                        <h5>Submission Guidelines</h5>
                        <ul class="small text-muted">
                            <li>Title should be clear and concise.</li>
                            <li>Make sure to select the relevant supervisor.</li>
                            <li>Upload only PDF format files.</li>
                            <li>Once submitted, you cannot edit unless the supervisor rejects it.</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>