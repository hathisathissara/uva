<?php 
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/db.php'); // db.php එකේ BASE_URL තියෙනවා කියලා උපකල්පනය කරනවා

// Security Check: If not logged in, redirect to login page
if(!isset($_SESSION['auth'])){
    $_SESSION['message'] = "Login to access your profile";
    header("Location: " . BASE_URL . "index.php");
    exit(0);
}

include('../includes/header.php'); 

// Fetch current user details from DB using session ID
$user_id = $_SESSION['auth_user']['user_id'];
$query = "SELECT * FROM users WHERE user_id='$user_id' LIMIT 1";
$query_run = mysqli_query($conn, $query);

if(mysqli_num_rows($query_run) > 0){
    $user = mysqli_fetch_array($query_run);
} else {
    $_SESSION['message'] = "User Not Found";
    header("Location: " . BASE_URL . "index.php");
    exit(0);
}

// Determine Role Color for styling
$role_color = '#4e73df'; // Default Blue
if($user['role'] == 'admin') $role_color = '#e74a3b'; // Red
elseif($user['role'] == 'lecturer') $role_color = '#f6c23e'; // Yellow
elseif($user['role'] == 'student') $role_color = '#1cc88a'; // Green
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 fw-bold">My Profile</h3>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">My Profile</li>
    </ol>

    <!-- Message Alert Box -->
    <?php include('../message.php'); ?>

    <!-- 
      IMPORTANT: The entire row is wrapped in ONE form.
      enctype="multipart/form-data" is REQUIRED for image uploads!
    -->
    <form action="code.php" method="POST" enctype="multipart/form-data">
        
        <!-- Hidden input for User ID -->
        <input type="hidden" name="user_id" value="<?= $user['user_id']; ?>">
        <!-- Hidden input to keep track of the old image name -->
        <input type="hidden" name="old_image" value="<?= $user['profile_pic']; ?>">

        <div class="row">
            
            <!-- ==========================================
                 LEFT COLUMN: PROFILE PICTURE CARD 
                 ========================================== -->
            <div class="col-xl-4 col-md-5 mb-4">
                <div class="card shadow border-0 text-center h-100 py-4 border-bottom-primary" style="border-bottom: 4px solid <?= $role_color; ?>;">
                    <div class="card-body">
                        
                        <!-- Display Profile Picture -->
                        <div class="mb-4 position-relative d-inline-block">
                            <?php if($user['profile_pic'] != 'default.png' && $user['profile_pic'] != ''): ?>
                                <!-- Show Uploaded Image -->
                                <img src="../uploads/profile/<?= $user['profile_pic']; ?>" 
                                     class="rounded-circle shadow border border-3 border-white" 
                                     style="width:160px; height:160px; object-fit:cover;">
                            <?php else: ?>
                                <!-- Show Letter Avatar if no image -->
                                <div class="rounded-circle text-white d-flex justify-content-center align-items-center mx-auto shadow border border-3 border-white" 
                                     style="width:160px; height:160px; font-size:60px; background-color: <?= $role_color; ?>;">
                                    <?= strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Camera Icon Badge -->
                            <span class="position-absolute bottom-0 end-0 bg-white rounded-circle p-2 shadow" style="transform: translate(-10px, -10px);">
                                <i class="fas fa-camera text-muted"></i>
                            </span>
                        </div>
                        
                        <!-- File Input for New Picture -->
                        <div class="mb-4 px-4 text-start">
                            <label class="form-label small fw-bold text-muted text-uppercase">Change Photo</label>
                            <input type="file" name="profile_image" class="form-control form-control-sm" accept=".jpg, .jpeg, .png">
                            <small class="text-muted" style="font-size: 0.7rem;">Allowed: JPG, PNG. Max: 2MB</small>
                        </div>

                        <h5 class="fw-bold mb-1 text-dark"><?= $user['full_name']; ?></h5>
                        <p class="text-muted mb-3"><?= $user['email']; ?></p>
                        
                        <!-- Role Badge -->
                        <span class="badge rounded-pill px-3 py-2 text-uppercase fw-bold shadow-sm" 
                              style="background-color: <?= $role_color; ?>; color: <?= $user['role']=='lecturer'?'#333':'#fff'; ?>;">
                            <i class="fas fa-user-shield me-1"></i> <?= $user['role']; ?> Account
                        </span>

                        <hr class="my-4 mx-3">
                        
                        <!-- Account Meta Data -->
                        <div class="text-start px-4 text-muted small">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fas fa-calendar-alt me-2"></i> Joined:</span>
                                <span class="fw-bold text-dark"><?= date('d M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-id-badge me-2"></i> User ID:</span>
                                <span class="fw-bold text-dark">ICT-<?= sprintf('%04d', $user['user_id']); ?></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ==========================================
                 RIGHT COLUMN: ACCOUNT DETAILS FORM 
                 ========================================== -->
            <div class="col-xl-8 col-md-7 mb-4">
                <div class="card shadow border-0 h-100 border-top-primary" style="border-top: 4px solid <?= $role_color; ?>;">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold" style="color: <?= $role_color; ?>;">
                            <i class="fas fa-user-edit me-2"></i> Personal Information
                        </h6>
                    </div>
                    <div class="card-body px-4 py-4">
                        
                        <div class="row">
                            <!-- Full Name -->
                            <div class="col-md-6 mb-4">
                                <label class="fw-bold text-muted small text-uppercase mb-2">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="full_name" class="form-control border-start-0 ps-0" value="<?= $user['full_name']; ?>" required>
                                </div>
                            </div>
                            
                            <!-- Email (Readonly) -->
                            <div class="col-md-6 mb-4">
                                <label class="fw-bold text-muted small text-uppercase mb-2">Email Address</label>
                                <div class="input-group" data-bs-toggle="tooltip" title="Email cannot be changed here. Contact Admin.">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 bg-light text-muted ps-0" value="<?= $user['email']; ?>" readonly>
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6 mb-4">
                                <label class="fw-bold text-muted small text-uppercase mb-2">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="phone" class="form-control border-start-0 ps-0" value="<?= $user['phone'] ?? ''; ?>" placeholder="e.g. 0771234567">
                                </div>
                            </div>
                            
                            <!-- Theme Preference -->
                            <div class="col-md-6 mb-4">
                                <label class="fw-bold text-muted small text-uppercase mb-2">Theme Preference</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-palette text-muted"></i></span>
                                    <select name="theme_preference" id="theme_preference" class="form-select border-start-0 ps-0">
                                        <option value="light" <?= (empty($user['theme']) || $user['theme'] == 'light') ? 'selected' : ''; ?>>Light Mode</option>
                                        <option value="dark" <?= ($user['theme'] == 'dark') ? 'selected' : ''; ?>>Dark Mode</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <!-- Change Password Section -->
                        <h6 class="fw-bold text-danger mb-3"><i class="fas fa-lock me-2"></i> Security (Change Password)</h6>
                        
                        <div class="row bg-light p-3 rounded mx-0 mb-4 border border-1 border-light shadow-sm">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small text-uppercase mb-2">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-key text-muted"></i></span>
                                    <input type="password" name="new_password" id="new_pwd" class="form-control border-start-0 ps-0" placeholder="Leave blank to keep current">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted small text-uppercase mb-2">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-check-circle text-muted" id="pwd_icon"></i></span>
                                    <input type="password" name="confirm_password" id="confirm_pwd" class="form-control border-start-0 ps-0" placeholder="Type new password again">
                                </div>
                                <small id="pwd_match_msg" class="d-block mt-2 fw-bold"></small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end mt-4">
                            <button type="submit" name="update_profile_btn" class="btn btn-primary px-5 py-2 shadow-sm rounded-pill fw-bold" style="background-color: <?= $role_color; ?>; border-color: <?= $role_color; ?>;">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- jQuery for Real-time Password Matching -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
    $(document).ready(function () {
        
        // Initialize Bootstrap Tooltips (for the email readonly message)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Theme Update AJAX
        $('#theme_preference').change(function() {
            var selectedTheme = $(this).val();
            var userId = $('input[name="user_id"]').val();
            
            $.ajax({
                type: "POST",
                url: "code.php",
                data: {
                    update_theme: true,
                    user_id: userId,
                    theme: selectedTheme
                },
                success: function(response) {
                    if(response.trim() === "success") {
                        if(selectedTheme === 'dark') {
                            $('body').addClass('dark-mode');
                        } else {
                            $('body').removeClass('dark-mode');
                        }
                    } else {
                        alert("Failed to update theme.");
                    }
                }
            });
        });

        // Password Match Checker
        $('#confirm_pwd, #new_pwd').keyup(function () {
            var pwd = $('#new_pwd').val();
            var cpwd = $('#confirm_pwd').val();

            if(pwd != '' && cpwd != ''){
                if(pwd === cpwd){
                    $('#pwd_match_msg').html('<span class="text-success">Passwords match perfectly!</span>');
                    $('#pwd_icon').removeClass('text-muted text-danger').addClass('text-success');
                    $('button[name="update_profile_btn"]').prop('disabled', false);
                } else {
                    $('#pwd_match_msg').html('<span class="text-danger">Passwords do not match!</span>');
                    $('#pwd_icon').removeClass('text-muted text-success').addClass('text-danger');
                    $('button[name="update_profile_btn"]').prop('disabled', true);
                }
            } else {
                $('#pwd_match_msg').html('');
                $('#pwd_icon').removeClass('text-success text-danger').addClass('text-muted');
                $('button[name="update_profile_btn"]').prop('disabled', false);
            }
        });
    });
</script>

<?php include('../includes/footer.php'); ?>