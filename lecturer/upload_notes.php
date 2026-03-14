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
    <h3 class="mt-4 fw-bold">Upload Lecture Materials</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Upload Notes</li>
    </ol>

    <?php include('../message.php'); ?>

    <div class="row">
        <!-- 1. UPLOAD FORM -->
        <div class="col-xl-4 col-md-5 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cloud-upload-alt me-2"></i> Upload New File</h6>
                </div>
                <div class="card-body">
                    <!-- Important: enctype="multipart/form-data" is required for file uploads -->
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Class</label>
                            <select name="class_id" class="form-select" required>
                                <option value="">-- Choose Class --</option>
                                <?php
                                // Fetch only classes assigned to this lecturer
                                $class_q = "SELECT cl.class_id, co.course_name, cl.academic_year 
                                            FROM classes cl 
                                            JOIN courses co ON cl.course_code = co.course_code
                                            WHERE cl.lecturer_id = '$lecturer_id'";
                                $class_run = mysqli_query($conn, $class_q);
                                
                                if(mysqli_num_rows($class_run) > 0){
                                    foreach($class_run as $cls){
                                        // Auto-select if passed via URL (e.g., from my_classes.php)
                                        $selected = (isset($_GET['class_id']) && $_GET['class_id'] == $cls['class_id']) ? 'selected' : '';
                                        echo "<option value='".$cls['class_id']."' $selected>".$cls['course_name']." (".$cls['academic_year'].")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Material Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 1 - Intro to Programming" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Select File (PDF, PPT, DOCX, ZIP)</label>
                            <input type="file" name="lecture_file" class="form-control" required accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle"></i> Max size: 10MB.</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="upload_material_btn" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fas fa-upload me-2"></i> Upload File
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- 2. UPLOADED FILES LIST -->
        <div class="col-xl-8 col-md-7 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-folder-open me-2"></i> My Uploaded Materials</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Subject / Class</th>
                                    <th>Upload Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // JOIN Query to get materials only for this lecturer's classes
                                $mat_query = "SELECT m.material_id, m.title, m.file_path, m.upload_date, 
                                                     co.course_name, cl.academic_year
                                              FROM materials m
                                              JOIN classes cl ON m.class_id = cl.class_id
                                              JOIN courses co ON cl.course_code = co.course_code
                                              WHERE cl.lecturer_id = '$lecturer_id'
                                              ORDER BY m.material_id DESC";
                                              
                                $mat_run = mysqli_query($conn, $mat_query);

                                if(mysqli_num_rows($mat_run) > 0)
                                {
                                    foreach($mat_run as $row)
                                    {
                                        // Extract file extension to show correct icon
                                        $ext = pathinfo($row['file_path'], PATHINFO_EXTENSION);
                                        $icon = 'fa-file';
                                        if($ext == 'pdf') $icon = 'fa-file-pdf text-danger';
                                        elseif(in_array($ext, ['ppt', 'pptx'])) $icon = 'fa-file-powerpoint text-warning';
                                        elseif(in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word text-primary';
                                        elseif(in_array($ext, ['zip', 'rar'])) $icon = 'fa-file-archive text-secondary';
                                        ?>
                                        <tr>
                                            <td>
                                                <i class="fas <?= $icon; ?> fa-lg me-2"></i> 
                                                <strong><?= $row['title']; ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-dark"><?= $row['course_name']; ?></span>
                                                <small class="d-block text-muted"><?= $row['academic_year']; ?></small>
                                            </td>
                                            <td><span class="text-muted small"><?= date('M d, Y', strtotime($row['upload_date'])); ?></span></td>
                                            <td>
                                                <!-- Download Button -->
                                                <a href="../<?= $row['file_path']; ?>" target="_blank" class="btn btn-sm btn-success text-white" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                
                                                <!-- Delete Button -->
                                                <form action="code.php" method="POST" class="d-inline" onsubmit="return confirm('Delete this file? Students will no longer be able to download it.');">
                                                    <input type="hidden" name="material_id" value="<?= $row['material_id']; ?>">
                                                    <!-- Important: Send file path to delete actual file from server -->
                                                    <input type="hidden" name="file_path" value="<?= $row['file_path']; ?>">
                                                    <button type="submit" name="delete_material_btn" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                else
                                {
                                    echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No materials uploaded yet.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>