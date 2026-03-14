<?php 
include('../includes/header.php'); 
include('../config/db.php');

// Security Check
if($_SESSION['auth_role'] != 'lecturer'){
    $_SESSION['message'] = "Unauthorized Access";
    header("Location: ../index.php");
    exit(0);
}

$lecturer_id = $_SESSION['auth_user']['user_id'];
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">Final Year Project (FYP) Requests</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">FYP Requests</li>
    </ol>

    <?php include('../message.php'); ?>

    <!-- Summary Cards for FYP -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-dark shadow-sm h-100 py-2 border-0">
                <div class="card-body py-1">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">Pending Approvals</div>
                            <?php 
                            $pending_q = "SELECT COUNT(*) AS total FROM fyp_projects WHERE supervisor_id='$lecturer_id' AND status='pending'";
                            $pending_res = mysqli_fetch_assoc(mysqli_query($conn, $pending_q))['total'];
                            ?>
                            <div class="h3 mb-0 fw-bold"><?= $pending_res; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm h-100 py-2 border-0">
                <div class="card-body py-1">
                    <div class="row align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">Approved Projects</div>
                            <?php 
                            $approved_q = "SELECT COUNT(*) AS total FROM fyp_projects WHERE supervisor_id='$lecturer_id' AND status='approved'";
                            $approved_res = mysqli_fetch_assoc(mysqli_query($conn, $approved_q))['total'];
                            ?>
                            <div class="h3 mb-0 fw-bold"><?= $approved_res; ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x opacity-50"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FYP Requests Table -->
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-project-diagram me-2"></i> Student Project Proposals</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle border" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Student</th>
                            <th>Project Title</th>
                            <th>Description</th>
                            <th>Proposal File</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get all FYP requests assigned to this lecturer
                        $fyp_query = "SELECT f.*, u.full_name, u.email 
                                      FROM fyp_projects f 
                                      JOIN users u ON f.student_id = u.user_id 
                                      WHERE f.supervisor_id = '$lecturer_id' 
                                      ORDER BY CASE status 
                                         WHEN 'pending' THEN 1 
                                         WHEN 'approved' THEN 2 
                                         WHEN 'rejected' THEN 3 
                                         WHEN 'completed' THEN 4 
                                      END, f.project_id DESC";
                        
                        $fyp_run = mysqli_query($conn, $fyp_query);

                        if(mysqli_num_rows($fyp_run) > 0)
                        {
                            foreach($fyp_run as $row)
                            {
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= $row['full_name']; ?></div>
                                        <small class="text-muted"><?= $row['email']; ?></small>
                                    </td>
                                    <td><strong><?= $row['title']; ?></strong></td>
                                    <td>
                                        <!-- Shorten description if it's too long -->
                                        <span class="d-inline-block text-truncate text-muted" style="max-width: 200px;" title="<?= $row['description']; ?>">
                                            <?= $row['description']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if(!empty($row['submission_file'])): ?>
                                            <a href="../<?= $row['submission_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-file-pdf me-1"></i> View Document
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">No file attached</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if($row['status'] == 'pending'){
                                            echo '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending</span>';
                                        } elseif($row['status'] == 'approved'){
                                            echo '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Approved</span>';
                                        } elseif($row['status'] == 'rejected'){
                                            echo '<span class="badge bg-danger"><i class="fas fa-times me-1"></i> Rejected</span>';
                                        } elseif($row['status'] == 'completed'){
                                            echo '<span class="badge bg-primary"><i class="fas fa-trophy me-1"></i> Completed</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <!-- Action Form (Only show Approve/Reject if it's currently Pending) -->
                                        <?php if($row['status'] == 'pending'): ?>
                                            <form action="code.php" method="POST" class="d-inline">
                                                <input type="hidden" name="project_id" value="<?= $row['project_id']; ?>">
                                                
                                                <button type="submit" name="approve_fyp_btn" class="btn btn-sm btn-success mb-1" title="Approve Project" onclick="return confirm('Approve this project?');">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                
                                                <button type="submit" name="reject_fyp_btn" class="btn btn-sm btn-danger mb-1" title="Reject Project" onclick="return confirm('Reject this project? The student will need to submit a new proposal.');">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- If already approved, lecturer can mark it as Completed later -->
                                            <?php if($row['status'] == 'approved'): ?>
                                                <form action="code.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="project_id" value="<?= $row['project_id']; ?>">
                                                    <button type="submit" name="complete_fyp_btn" class="btn btn-sm btn-primary" onclick="return confirm('Mark this project as fully Completed?');">
                                                        <i class="fas fa-flag-checkered me-1"></i> Mark Done
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">No actions available</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        else
                        {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No FYP Requests assigned to you at the moment.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>